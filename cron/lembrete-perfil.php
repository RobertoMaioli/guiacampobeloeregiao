<?php
/**
 * cron/lembrete-perfil.php
 * E-mail #8 — Lembrete de perfil incompleto
 *
 * Configurar no AAPanel → Cron Jobs:
 *   Comando:   php /www/wwwroot/guiacampobeloeregiao.com.br/cron/lembrete-perfil.php
 *   Frequência: 1x por dia (ex: 10h da manhã)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../core/Mailer.php';

$empresas = DB::query(
    "SELECT e.id AS empresa_id, u.nome, u.email,
            l.nome AS nome_empresa, l.id AS lugar_id,
            l.telefone, l.foto_principal,
            (SELECT COUNT(*) FROM fotos f WHERE f.lugar_id = l.id) AS total_fotos,
            (SELECT COUNT(*) FROM horarios h WHERE h.lugar_id = l.id) AS total_horarios
     FROM empresas e
     JOIN usuarios u ON u.id = e.usuario_id
     JOIN lugares l  ON l.id = e.lugar_id
     WHERE e.status = 'aprovada'
       AND DATE(e.aprovado_em) = CURDATE() - INTERVAL ? DAY
       AND NOT EXISTS (
           SELECT 1 FROM empresa_logs el
           WHERE el.empresa_id = e.id AND el.acao = 'lembrete_perfil_enviado'
       )
       AND (
           l.telefone IS NULL OR l.telefone = ''
           OR (l.foto_principal IS NULL AND (SELECT COUNT(*) FROM fotos f2 WHERE f2.lugar_id = l.id) = 0)
           OR (SELECT COUNT(*) FROM horarios h2 WHERE h2.lugar_id = l.id) = 0
       )",
    [LEMBRETE_DIAS]
);

$enviados = 0;
$erros    = 0;

foreach ($empresas as $emp) {
    $checks = [
        ['ok' => !empty($emp['telefone']),                                   'label' => 'Telefone de contato'],
        ['ok' => $emp['total_fotos'] > 0 || !empty($emp['foto_principal']),  'label' => 'Foto de capa'],
        ['ok' => $emp['total_horarios'] > 0,                                 'label' => 'Horários de funcionamento'],
    ];

    $ok_count       = count(array_filter($checks, fn($c) => $c['ok']));
    $pct            = (int)round($ok_count / count($checks) * 100);
    $itens_faltando = array_column(array_filter($checks, fn($c) => !$c['ok']), 'label');

    if (empty($itens_faltando)) continue;

    $nome         = $emp['nome'];
    $nome_empresa = $emp['nome_empresa'];

    ob_start();
    include __DIR__ . '/../emails/lembrete-perfil.php';
    $html = ob_get_clean();

    $res = Mailer::send(
        $emp['email'], $emp['nome'],
        'Complete o perfil de ' . $nome_empresa . ' no Guia Campo Belo',
        $html
    );

    if ($res['ok']) {
        DB::exec(
            'INSERT INTO empresa_logs (empresa_id, acao, detalhe, criado_em)
             VALUES (?, "lembrete_perfil_enviado", ?, NOW())',
            [$emp['empresa_id'], "Lembrete enviado. Perfil: {$pct}%"]
        );
        $enviados++;
        echo "[OK] {$emp['email']} — {$nome_empresa} ({$pct}%)\n";
    } else {
        $erros++;
        echo "[ERRO] {$emp['email']} — {$res['erro']}\n";
    }
}

echo "\nConcluído: {$enviados} enviados, {$erros} erros.\n";