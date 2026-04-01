<?php
/**
 * core/Mailer.php
 * Wrapper simples para a API do Resend via cURL
 * Sem dependências externas
 */
// Garante que as configs de mail estão carregadas independente do contexto
if (!defined('RESEND_API_KEY')) {
    require_once __DIR__ . '/../config/mail.php';
}

class Mailer
{
    /**
     * Envia um e-mail via Resend API
     *
     * @param string $to_email   E-mail do destinatário
     * @param string $to_name    Nome do destinatário
     * @param string $subject    Assunto
     * @param string $html       Corpo HTML
     * @param string $text       Corpo texto puro (opcional)
     * @return array ['ok' => bool, 'id' => string|null, 'erro' => string|null]
     */
    public static function send(
        string $to_email,
        string $to_name,
        string $subject,
        string $html,
        string $text = ''
    ): array {
        $key = defined('RESEND_API_KEY') ? RESEND_API_KEY : '';
        if (empty($key) || str_starts_with($key, 're_SUBSTITUA')) {
            // Tenta carregar novamente como fallback
            $cfg = __DIR__ . '/../config/mail.php';
            if (file_exists($cfg)) {
                include $cfg; // include (não require_once) para forçar carregamento
                $key = defined('RESEND_API_KEY') ? RESEND_API_KEY : '';
            }
        }
        if (empty($key) || str_starts_with($key, 're_SUBSTITUA')) {
            error_log('[Mailer] API key não configurada. RESEND_API_KEY=' . ($key ?: 'vazio'));
            return ['ok' => false, 'erro' => 'API key não configurada.'];
        }

        $payload = [
            'from'    => MAIL_FROM_NAME . ' <' . MAIL_FROM_EMAIL . '>',
            'to'      => [$to_name . ' <' . $to_email . '>'],
            'subject' => $subject,
            'html'    => $html,
        ];
        if ($text) $payload['text'] = $text;

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $key,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 10,
        ]);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err) {
            error_log("[Mailer] cURL error: $err");
            return ['ok' => false, 'erro' => $err];
        }

        $data = json_decode($response, true);

        if ($status === 200 || $status === 201) {
            return ['ok' => true, 'id' => $data['id'] ?? null];
        }

        $msg = $data['message'] ?? $data['name'] ?? "HTTP $status";
        error_log("[Mailer] Resend error: $msg | payload: " . json_encode($payload));
        return ['ok' => false, 'erro' => $msg];
    }

    /**
     * Atalho para enviar ao admin
     */
    public static function sendAdmin(string $subject, string $html): array
    {
        return self::send(ADMIN_EMAIL, ADMIN_NAME, $subject, $html);
    }
}