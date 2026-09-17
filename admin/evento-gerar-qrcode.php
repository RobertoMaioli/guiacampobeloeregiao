<?php
/**
 * admin/evento-gerar-qrcode.php
 * Gera/reenvia QR Codes manualmente para um participante
 */
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../core/Mailer.php';
require_once __DIR__ . '/../core/Sanitize.php';
require_once __DIR__ . '/../config/asaas.php';
require_once __DIR__ . '/../vendor/autoload.php';

Auth::require();

$page_title  = 'Ingressos — Guia Connect';
$active_menu = '/admin/evento-gerar-qrcode.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

$mensagem  = null;
$inscricao = null;

// ── Busca participante ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['buscar'])) {
    $busca     = trim($_GET['buscar']);
    $inscricao = DB::row(
        'SELECT * FROM evento_inscricoes
         WHERE email = ? OR asaas_payment_id = ?
         ORDER BY criado_em DESC LIMIT 1',
        [$busca, $busca]
    );
    if (!$inscricao) {
        $mensagem = ['tipo' => 'erro', 'texto' => 'Participante não encontrado.'];
    }
}

// ── Gera e reenvia QR Code ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inscricao_id  = (int) ($_POST['inscricao_id'] ?? 0);
    $nome_cliente  = trim($_POST['nome']       ?? '');
    $email_cliente = trim($_POST['email']      ?? '');
    $payment_id    = trim($_POST['payment_id'] ?? '');
    
    // ── Excluir inscrição manual ──────────────────────────────────────────
    if (!empty($_POST['excluir_id'])) {
        $excluir_id = (int) $_POST['excluir_id'];
        // Só exclui se for manual (segurança extra)
        $reg = DB::row(
            'SELECT id, token, asaas_payment_id FROM evento_inscricoes WHERE id = ?',
            [$excluir_id]
        );
        if ($reg && str_starts_with($reg['asaas_payment_id'], 'manual_')) {
            // Remove o arquivo PNG se existir
            $qrPath = __DIR__ . '/../uploads/qrcodes/qr_' . $reg['token'] . '.png';
            if (file_exists($qrPath)) unlink($qrPath);
            // Remove do banco
            DB::exec('DELETE FROM evento_inscricoes WHERE id = ?', [$excluir_id]);
            $mensagem = ['tipo' => 'ok', 'texto' => 'Inscrição excluída com sucesso.'];
        } else {
            $mensagem = ['tipo' => 'erro', 'texto' => 'Não é possível excluir inscrições geradas pelo Asaas.'];
        }
    }

    if (!$inscricao_id && $email_cliente && $nome_cliente) {
        // Cria nova inscrição manual
        $token = strtoupper(substr(md5('manual_' . $email_cliente . time()), 0, 12));
        try {
            DB::exec(
                'INSERT INTO evento_inscricoes
                    (evento_id, nome, email, asaas_payment_id, asaas_customer_id, token, status, criado_em)
                 VALUES (?, ?, ?, ?, "", ?, "confirmado", NOW())',
                ['guia-connect-soft-opening-mai25', $nome_cliente, $email_cliente,
                 $payment_id ?: 'manual_' . time(), $token]
            );
            $inscricao_id = (int) DB::lastId();
        } catch (Exception $e) {
            $mensagem = ['tipo' => 'erro', 'texto' => 'Erro ao criar inscrição: ' . $e->getMessage()];
        }
    } else {
        $inscricao_row = DB::row('SELECT * FROM evento_inscricoes WHERE id = ?', [$inscricao_id]);
        $token         = $inscricao_row['token']  ?? null;
        $nome_cliente  = $inscricao_row['nome']   ?? '';
        $email_cliente = $inscricao_row['email']  ?? '';
    }

    if (!isset($mensagem)) {
        if (!isset($token)) {
            $row   = DB::row('SELECT token FROM evento_inscricoes WHERE id = ?', [$inscricao_id]);
            $token = $row['token'] ?? strtoupper(substr(md5($inscricao_id . time()), 0, 12));
            DB::exec('UPDATE evento_inscricoes SET token = ? WHERE id = ?', [$token, $inscricao_id]);
        }

        $qrConteudo = "GUIACONNECT|{$token}|{$email_cliente}";
        $qrFilename = 'qr_' . $token . '.png';
        $qrPath     = __DIR__ . '/../uploads/qrcodes/' . $qrFilename;
        $qrUrl      = SITE_URL . '/uploads/qrcodes/' . $qrFilename;

        try {
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

            DB::exec(
                'UPDATE evento_inscricoes SET qrcode_token = ? WHERE id = ?',
                [$token, $inscricao_id]
            );

            ob_start();
            include __DIR__ . '/../emails/evento-ingresso.php';
            $html      = ob_get_clean();
            $resultado = Mailer::send(
                $email_cliente,
                $nome_cliente,
                '🎉 Seu ingresso — Guia Connect Soft Opening',
                $html
            );

            if ($resultado['ok']) {
                $mensagem = ['tipo' => 'ok', 'texto' => "QR Code gerado e e-mail enviado para {$email_cliente} com sucesso!"];
            } else {
                $mensagem = ['tipo' => 'erro', 'texto' => 'QR Code gerado mas e-mail falhou: ' . ($resultado['erro'] ?? '')];
            }

            $inscricao = DB::row('SELECT * FROM evento_inscricoes WHERE id = ?', [$inscricao_id]);

        } catch (Exception $e) {
            $mensagem = ['tipo' => 'erro', 'texto' => 'Erro ao gerar QR Code: ' . $e->getMessage()];
        }
    }
}

$participantes = DB::query(
    'SELECT * FROM evento_inscricoes
     WHERE evento_id = "guia-connect-soft-opening-mai25"
     ORDER BY criado_em DESC'
);

include __DIR__ . '/_layout.php';
?>

<style>
  .card { background: #fff; border-radius: 16px; padding: 24px 28px;
          box-shadow: 0 2px 16px rgba(29,29,27,.06); margin-bottom: 24px; }
  .card h2 { font-size: 15px; font-weight: 800; color: var(--green-dk); margin-bottom: 16px; }
  .form-label-adm { font-size: 10px; font-weight: 700; letter-spacing: .08em;
                text-transform: uppercase; color: var(--green-dk);
                margin-bottom: 5px; display: block; }
  .form-control-adm { width: 100%; border: 1.5px solid rgba(61,71,51,.14);
                  border-radius: 9px; font-family: 'Montserrat', sans-serif;
                  font-size: 13px; padding: 9px 12px; background: var(--cream);
                  outline: none; }
  .form-control-adm:focus { border-color: var(--green); }
  .btn-adm { padding: 10px 20px; border-radius: 9px; border: none; cursor: pointer;
         font-family: 'Montserrat', sans-serif; font-size: 12px; font-weight: 800;
         letter-spacing: .06em; text-transform: uppercase; }
  .btn-adm-green { background: var(--green-dk); color: #fff; }
  .btn-adm-green:hover { background: var(--green); }
  .btn-adm-gold  { background: var(--gold); color: var(--green-dk); }
  .btn-adm-gold:hover { background: var(--gold-lt); }
  .alert-adm { border-radius: 10px; padding: 12px 16px; font-size: 13px;
           font-weight: 600; margin-bottom: 20px; }
  .alert-ok   { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
  .alert-erro { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
  .tbl { width: 100%; border-collapse: collapse; font-size: 12.5px; }
  .tbl th { background: var(--offwhite); font-size: 10px; font-weight: 800;
            letter-spacing: .08em; text-transform: uppercase; color: var(--warmgray);
            padding: 10px 12px; text-align: left; }
  .tbl td { padding: 10px 12px; border-bottom: 1px solid rgba(61,71,51,.07); }
  .tbl tr:last-child td { border-bottom: none; }
  .bdg { display: inline-block; padding: 3px 10px; border-radius: 999px;
         font-size: 10px; font-weight: 700; }
  .bdg-ok      { background: #d1fae5; color: #065f46; }
  .bdg-pending { background: #fef3c7; color: #92400e; }
  .frow { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
</style>

<?php if ($mensagem): ?>
<div class="alert-adm alert-<?= $mensagem['tipo'] ?>">
  <?= htmlspecialchars($mensagem['texto']) ?>
</div>
<?php endif; ?>

<!-- Buscar participante -->
<div class="card">
  <h2>Buscar participante existente</h2>
  <form method="GET" style="display:flex;gap:12px;align-items:flex-end">
    <div style="flex:1">
      <label class="form-label-adm">E-mail ou Payment ID</label>
      <input type="text" name="buscar" class="form-control-adm"
             placeholder="email@exemplo.com ou pay_xxxxxxxxx"
             value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>"/>
    </div>
    <button type="submit" class="btn-adm btn-adm-green">Buscar</button>
  </form>

  <?php if ($inscricao && $_SERVER['REQUEST_METHOD'] === 'GET'): ?>
  <div style="margin-top:20px;padding:16px;background:var(--offwhite);border-radius:10px">
    <p style="font-size:13px;margin-bottom:12px">
      <strong><?= htmlspecialchars($inscricao['nome']) ?></strong> —
      <?= htmlspecialchars($inscricao['email']) ?>
      <span class="bdg bdg-ok" style="margin-left:8px"><?= $inscricao['status'] ?></span>
    </p>
    <form method="POST">
      <input type="hidden" name="inscricao_id" value="<?= $inscricao['id'] ?>"/>
      <input type="hidden" name="nome"  value="<?= htmlspecialchars($inscricao['nome']) ?>"/>
      <input type="hidden" name="email" value="<?= htmlspecialchars($inscricao['email']) ?>"/>
      <button type="submit" class="btn-adm btn-adm-gold">
        Gerar QR Code e reenviar e-mail
      </button>
    </form>
  </div>
  <?php endif; ?>
</div>

<!-- Criar ingresso manual -->
<div class="card">
  <h2>Criar ingresso manual</h2>
  <p style="font-size:12px;color:var(--warmgray);margin-bottom:16px">
    Use para participantes que pagaram fora do checkout (transferência, cortesia, etc.)
  </p>
  <form method="POST">
    <input type="hidden" name="inscricao_id" value="0"/>
    <div class="frow" style="margin-bottom:14px">
      <div>
        <label class="form-label-adm">Nome completo *</label>
        <input type="text" name="nome" class="form-control-adm"
               placeholder="Nome do participante" required/>
      </div>
      <div>
        <label class="form-label-adm">E-mail *</label>
        <input type="email" name="email" class="form-control-adm"
               placeholder="email@exemplo.com" required/>
      </div>
    </div>
    <div style="margin-bottom:16px">
      <label class="form-label-adm">Payment ID Asaas (opcional)</label>
      <input type="text" name="payment_id" class="form-control-adm"
             placeholder="pay_xxxxxxxxx"/>
    </div>
    <button type="submit" class="btn-adm btn-adm-green">
      Criar ingresso e enviar QR Code
    </button>
  </form>
</div>

<!-- Lista de participantes -->
<div class="card">
  <h2>Participantes inscritos (<?= count($participantes) ?>)</h2>
  <?php if ($participantes): ?>
  <table class="tbl">
    <thead>
      <tr>
        <th>Nome</th>
        <th>E-mail</th>
        <th>Token</th>
        <th>Status</th>
        <th>Data</th>
        <th>Ação</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($participantes as $p): ?>
      <tr>
        <td><?= htmlspecialchars($p['nome']) ?></td>
        <td><?= htmlspecialchars($p['email']) ?></td>
        <td style="font-family:monospace;font-size:11px;letter-spacing:.1em">
          <?= htmlspecialchars($p['token']) ?>
        </td>
        <td>
          <span class="bdg <?= $p['status'] === 'confirmado' ? 'bdg-ok' : 'bdg-pending' ?>">
            <?= $p['status'] ?>
          </span>
        </td>
        <td><?= date('d/m H:i', strtotime($p['criado_em'])) ?></td>
        <td style="display:flex;gap:6px;align-items:center">
          <form method="POST" style="display:inline">
            <input type="hidden" name="inscricao_id" value="<?= $p['id'] ?>"/>
            <input type="hidden" name="nome"  value="<?= htmlspecialchars($p['nome']) ?>"/>
            <input type="hidden" name="email" value="<?= htmlspecialchars($p['email']) ?>"/>
            <button type="submit" class="btn-adm btn-adm-gold"
                    style="padding:6px 12px;font-size:10px">
              Reenviar
            </button>
          </form>
        
          <?php if (str_starts_with($p['asaas_payment_id'], 'manual_')): ?>
          <form method="POST" style="display:inline"
                onsubmit="return confirm('Excluir ingresso de <?= htmlspecialchars($p['nome']) ?>?')">
            <input type="hidden" name="excluir_id" value="<?= $p['id'] ?>"/>
            <button type="submit" class="btn-adm"
                    style="padding:6px 12px;font-size:10px;background:#fee2e2;color:#991b1b">
              Excluir
            </button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <p style="font-size:13px;color:var(--warmgray)">Nenhum participante inscrito ainda.</p>
  <?php endif; ?>
</div>

</div><!-- /page-content -->
    </main>
</div><!-- /admin-wrapper -->
<?php include __DIR__ . '/_layout_end.php'; ?>