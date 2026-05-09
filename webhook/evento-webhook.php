<?php
/**
 * actions/evento-webhook.php
 * Recebe notificações do Asaas, gera QR Code e envia e-mail ao participante
 */

define('SKIP_AUTH', true);

require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../core/Mailer.php';
require_once __DIR__ . '/../vendor/autoload.php';

 // Valida o token do Asaas
$token_asaas    = 'whsec_cs_Uj3TlFsiTHPtuG5Hb7GX_lZFfeL2t53F0Q7fKztE';
$token_recebido = $_SERVER['HTTP_ASAAS_ACCESS_TOKEN'] ?? '';

if ($token_recebido !== $token_asaas) {
    echo json_encode(['ok' => false, 'msg' => 'token inválido']);
    exit;
}

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

header('Content-Type: application/json');

$raw     = file_get_contents('php://input');
$payload = json_decode($raw, true);

// Só processa pagamentos confirmados
$evento = $payload['payment'] ?? null;
if (!$evento) { echo json_encode(['ok' => false]); exit; }

$status = $payload['event'] ?? '';
if (!in_array($status, ['PAYMENT_CONFIRMED', 'PAYMENT_RECEIVED'])) {
    echo json_encode(['ok' => true, 'msg' => 'evento ignorado']); exit;
}

$payment_id = $evento['id']       ?? '';
$customer   = $evento['customer'] ?? '';

// Evita processar o mesmo pagamento duas vezes
$jaProcessado = DB::row(
    'SELECT id FROM evento_inscricoes WHERE asaas_payment_id = ?',
    [$payment_id]
);
if ($jaProcessado) { echo json_encode(['ok' => true, 'msg' => 'já processado']); exit; }

// Busca dados do cliente no Asaas
$ASAAS_KEY  = $_ENV['ASAAS_API_KEY'] ?? '$aact_hmlg_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OjBjMDE3Mzc4LTI4YTAtNDExZS1hZGVlLTJhOTM3NTBhNWJjODo6JGFhY2hfNDgxNWMyMzEtYmE2YS00ZjE4LTkwNjYtZTI2MDA1MGVmOTc3';
$ASAAS_BASE = 'https://sandbox.asaas.com/api/v3'; // troque em produção

function asaas_get(string $endpoint, string $key, string $base): ?array {
    $ch = curl_init($base . $endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => ['access_token: ' . $key],
    ]);
    $resp = curl_exec($ch); curl_close($ch);
    return $resp ? json_decode($resp, true) : null;
}

$cliente_data  = asaas_get("/customers/{$customer}", $ASAAS_KEY, $ASAAS_BASE);
$nome_cliente  = $cliente_data['name']  ?? 'Participante';
$email_cliente = $cliente_data['email'] ?? '';

if (!$email_cliente) {
    error_log('[webhook] e-mail não encontrado para customer: ' . $customer);
    echo json_encode(['ok' => false, 'msg' => 'e-mail não encontrado']); exit;
}

// Gera token único do ingresso
$token = strtoupper(substr(md5($payment_id . $email_cliente . time()), 0, 12));

// Persiste a inscrição confirmada
try {
    DB::exec(
        'INSERT INTO evento_inscricoes
            (evento_id, nome, email, asaas_payment_id, asaas_customer_id, token, status, criado_em)
         VALUES (?, ?, ?, ?, ?, ?, "confirmado", NOW())',
        ['guia-connect-soft-opening-mai25', $nome_cliente, $email_cliente,
         $payment_id, $customer, $token]
    );
} catch (Exception $e) {
    error_log('[webhook] DB: ' . $e->getMessage());
    echo json_encode(['ok' => false]); exit;
}

// Gera o QR Code e salva como PNG em disco
$qrConteudo  = "GUIACONNECT|{$token}|{$email_cliente}";
$qrFilename  = 'qr_' . $token . '.png';
$qrPath      = __DIR__ . '/../uploads/qrcodes/' . $qrFilename;
$qrUrl       = 'https://gcbr.maiolidesign.com.br/uploads/qrcodes/' . $qrFilename;

$qrCode = new QrCode(
    data                : $qrConteudo,
    encoding            : new Encoding('UTF-8'),
    errorCorrectionLevel: ErrorCorrectionLevel::High,
    size                : 300,
    margin              : 20,
    foregroundColor     : new Color(42, 48, 34),
    backgroundColor     : new Color(255, 255, 255)
);

$writer = new PngWriter();
$result = $writer->write($qrCode);
file_put_contents($qrPath, $result->getString());

// Atualiza o token no banco
DB::exec(
    'UPDATE evento_inscricoes SET qrcode_token = ? WHERE asaas_payment_id = ?',
    [$token, $payment_id]
);

// Envia o e-mail com o QR Code
$html_email = _buildEmailQr($nome_cliente, $token, $qrUrl);

$resultado = Mailer::send(
    $email_cliente,
    $nome_cliente,
    '🎉 Seu ingresso — Guia Connect Soft Opening',
    $html_email
);

if (!$resultado['ok']) {
    error_log('[webhook] e-mail falhou: ' . ($resultado['erro'] ?? ''));
}

echo json_encode(['ok' => true, 'token' => $token]);


// ── Template do e-mail ──────────────────────────────────────────────────────
function _buildEmailQr(string $nome, string $token, string $qrUrl): string {
    return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
</head>
<body style="margin:0;padding:0;background:#f2f0eb;font-family:'Helvetica Neue',Arial,sans-serif">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f2f0eb;padding:40px 16px">
    <tr><td align="center">
      <table width="100%" cellpadding="0" cellspacing="0"
             style="max-width:520px;background:#fff;border-radius:20px;overflow:hidden">

        <!-- Cabeçalho -->
        <tr>
          <td style="background:#2a3022;padding:32px 40px;text-align:center">
            <p style="font-size:11px;font-weight:700;letter-spacing:.2em;text-transform:uppercase;
                      color:rgba(255,255,255,.45);margin:0 0 8px">Guia Campo Belo &amp; Região</p>
            <h1 style="font-size:26px;font-weight:800;color:#fff;margin:0;line-height:1.2">
              Guia <em style="font-style:italic;font-weight:300;color:#ddc48a">Connect</em>
            </h1>
            <p style="font-size:12px;color:rgba(255,255,255,.5);margin:8px 0 0;font-weight:300">
              Soft Opening · 19 de maio de 2025
            </p>
          </td>
        </tr>

        <!-- Corpo -->
        <tr>
          <td style="padding:36px 40px;text-align:center">
            <p style="font-size:15px;font-weight:400;color:#1d1d1b;margin:0 0 6px">
              Olá, <strong style="color:#2a3022">{$nome}</strong>!
            </p>
            <p style="font-size:13px;font-weight:300;color:#8b8589;line-height:1.7;margin:0 0 28px">
              Seu pagamento foi confirmado. Abaixo está o seu
              <strong style="color:#2a3022">QR Code de acesso</strong>.<br>
              Apresente-o na entrada do evento.
            </p>

            <!-- QR Code -->
            <div style="background:#faf8f3;border-radius:16px;padding:24px;display:inline-block;margin-bottom:20px">
              <img src="{$qrUrl}" alt="QR Code de acesso"
                   width="200" height="200"
                   style="display:block;border-radius:8px"/>
            </div>

            <!-- Token legível -->
            <p style="font-size:11px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;
                      color:#8b8589;margin:0 0 4px">Código do ingresso</p>
            <p style="font-size:20px;font-weight:800;color:#2a3022;
                      letter-spacing:.2em;margin:0 0 28px">{$token}</p>

            <!-- Dados do evento -->
            <table width="100%" cellpadding="0" cellspacing="0"
                   style="background:#f5edda;border-radius:12px;margin-bottom:28px">
              <tr>
                <td style="padding:18px 20px">
                  <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="padding:6px 0;font-size:12px;color:#8b8589;font-weight:600;
                                 text-transform:uppercase;letter-spacing:.07em;width:90px">Data</td>
                      <td style="padding:6px 0;font-size:13px;color:#2a3022;font-weight:600">
                        Segunda, 19 de maio de 2025</td>
                    </tr>
                    <tr>
                      <td style="padding:6px 0;font-size:12px;color:#8b8589;font-weight:600;
                                 text-transform:uppercase;letter-spacing:.07em">Horário</td>
                      <td style="padding:6px 0;font-size:13px;color:#2a3022;font-weight:600">
                        A partir das 19h</td>
                    </tr>
                    <tr>
                      <td style="padding:6px 0;font-size:12px;color:#8b8589;font-weight:600;
                                 text-transform:uppercase;letter-spacing:.07em">Local</td>
                      <td style="padding:6px 0;font-size:13px;color:#2a3022;font-weight:600">
                        Cris Parilla<br>
                        <span style="font-weight:300;color:#8b8589">
                          Rua República do Iraque, 1326 — Campo Belo
                        </span>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>

            <!-- Aviso -->
            <p style="font-size:12px;font-weight:300;color:#8b8589;line-height:1.7;margin:0">
              Guarde este e-mail — o QR Code é seu ingresso.<br>
              Dúvidas? Fale pelo
              <a href="https://wa.me/5511999999999"
                 style="color:#3d4733;font-weight:700">WhatsApp</a>.
            </p>
          </td>
        </tr>

        <!-- Rodapé -->
        <tr>
          <td style="background:#f2f0eb;padding:20px 40px;text-align:center;
                     font-size:11px;color:#8b8589;font-weight:300">
            Guia Campo Belo &amp; Região ·
            <a href="https://guiacampobeloeregiao.com.br"
               style="color:#c9aa6b;text-decoration:none">guiacampobeloeregiao.com.br</a>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
}