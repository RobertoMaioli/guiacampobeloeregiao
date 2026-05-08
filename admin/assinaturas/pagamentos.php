<?php
/**
 * admin/assinaturas/pagamentos.php
 * Retorna HTML com histórico de pagamentos de uma empresa (carregado via AJAX)
 */
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

Auth::require();

$empresa_id = (int)Sanitize::get('empresa_id', 'int', 0);

if (!$empresa_id) {
    echo '<p style="color:#dc2626">Empresa inválida.</p>'; exit;
}

$pagamentos = DB::query(
    'SELECT asaas_payment_id, valor, status, vencimento, pago_em, criado_em
     FROM asaas_pagamentos
     WHERE empresa_id = ?
     ORDER BY criado_em DESC',
    [$empresa_id]
);

if (empty($pagamentos)):
?>
  <p style="color:#8b8589;text-align:center;padding:20px 0">
    Nenhum pagamento registrado ainda.
  </p>
<?php else: ?>

  <table style="width:100%;border-collapse:collapse">
    <thead>
      <tr style="font-size:10px;font-weight:700;text-transform:uppercase;
                 letter-spacing:.06em;color:#8b8589">
        <th style="padding:8px 0;border-bottom:1px solid rgba(61,71,51,.08)">ID Asaas</th>
        <th style="padding:8px 0;border-bottom:1px solid rgba(61,71,51,.08)">Valor</th>
        <th style="padding:8px 0;border-bottom:1px solid rgba(61,71,51,.08)">Status</th>
        <th style="padding:8px 0;border-bottom:1px solid rgba(61,71,51,.08)">Vencimento</th>
        <th style="padding:8px 0;border-bottom:1px solid rgba(61,71,51,.08)">Pago em</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($pagamentos as $p):
      $status_cfg = match($p['status']) {
        'CONFIRMED', 'RECEIVED' => ['bg'=>'#ecfdf5','color'=>'#065f46','label'=>'Pago'],
        'PENDING'               => ['bg'=>'#fef3c7','color'=>'#92400e','label'=>'Pendente'],
        'OVERDUE'               => ['bg'=>'#fef2f2','color'=>'#dc2626','label'=>'Vencido'],
        'REFUNDED'              => ['bg'=>'#eff6ff','color'=>'#1d4ed8','label'=>'Reembolsado'],
        'CANCELLED'             => ['bg'=>'#f3f4f6','color'=>'#6b7280','label'=>'Cancelado'],
        default                 => ['bg'=>'#f3f4f6','color'=>'#6b7280','label'=>$p['status']],
      };
    ?>
    <tr style="font-size:12px">
      <td style="padding:10px 0;border-bottom:1px solid rgba(61,71,51,.05);
                 color:#8b8589;font-family:monospace">
        <?= Sanitize::html($p['asaas_payment_id']) ?>
      </td>
      <td style="padding:10px 0;border-bottom:1px solid rgba(61,71,51,.05);font-weight:600">
        R$ <?= number_format($p['valor'], 2, ',', '.') ?>
      </td>
      <td style="padding:10px 0;border-bottom:1px solid rgba(61,71,51,.05)">
        <span style="background:<?= $status_cfg['bg'] ?>;color:<?= $status_cfg['color'] ?>;
                     font-size:10px;font-weight:700;padding:3px 8px;border-radius:999px">
          <?= $status_cfg['label'] ?>
        </span>
      </td>
      <td style="padding:10px 0;border-bottom:1px solid rgba(61,71,51,.05);color:#8b8589">
        <?= $p['vencimento'] ? date('d/m/Y', strtotime($p['vencimento'])) : '—' ?>
      </td>
      <td style="padding:10px 0;border-bottom:1px solid rgba(61,71,51,.05);color:#8b8589">
        <?= $p['pago_em'] ? date('d/m/Y', strtotime($p['pago_em'])) : '—' ?>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

<?php endif; ?>