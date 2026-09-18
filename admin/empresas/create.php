<?php
/**
 * admin/empresas/create.php
 * Cadastro manual de empresa (cria usuário + empresa) pelo admin
 */
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

Auth::require();

$page_title = 'Nova Empresa';

$planos_validos  = ['essencial', 'profissional', 'premium'];
$status_validos  = ['rascunho', 'pendente', 'aprovada'];
$erros = [];

$nome        = '';
$email       = '';
$tipo_pessoa = 'pj';
$cpf_cnpj    = '';
$plan_intent = 'essencial';
$status      = 'aprovada';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Sanitize::csrfValid($_POST['_token'] ?? '')) {
        $erros[] = 'Token de segurança inválido. Recarregue a página.';
    } else {
        $nome        = Sanitize::post('nome');
        $email       = Sanitize::post('email', 'email');
        $senha       = $_POST['senha'] ?? '';
        $tipo_pessoa = in_array($_POST['tipo_pessoa'] ?? '', ['pf', 'pj']) ? $_POST['tipo_pessoa'] : 'pj';
        $cpf_cnpj    = Sanitize::post('cpf_cnpj');
        $plan_intent = in_array($_POST['plan_intent'] ?? '', $planos_validos) ? $_POST['plan_intent'] : 'essencial';
        $status      = in_array($_POST['status'] ?? '', $status_validos) ? $_POST['status'] : 'aprovada';

        if (mb_strlen($nome) < 2)   $erros[] = 'Informe o nome do responsável.';
        if (!$email)                $erros[] = 'Informe um e-mail válido.';
        if (mb_strlen($senha) < 8)  $erros[] = 'A senha deve ter pelo menos 8 caracteres.';
        if ($email && DB::row('SELECT id FROM usuarios WHERE email = ?', [$email])) {
            $erros[] = 'Este e-mail já está cadastrado.';
        }

        if (empty($erros)) {
            $admin_id = (int)($_SESSION['admin_id'] ?? 0);

            DB::beginTransaction();
            try {
                DB::exec(
                    'INSERT INTO usuarios (nome, email, senha_hash, plan_intent, criado_em)
                     VALUES (?, ?, ?, ?, NOW())',
                    [$nome, $email, password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]), $plan_intent]
                );
                $usuario_id = (int) DB::lastId();

                $plano_ativo  = $status === 'aprovada' ? $plan_intent : null;
                $submetido_em = in_array($status, ['pendente', 'aprovada']) ? date('Y-m-d H:i:s') : null;
                $aprovado_por = $status === 'aprovada' ? $admin_id : null;
                $aprovado_em  = $status === 'aprovada' ? date('Y-m-d H:i:s') : null;

                DB::exec(
                    'INSERT INTO empresas
                        (usuario_id, plan_intent, plano_ativo, status, cpf_cnpj, tipo_pessoa,
                         aprovado_por, aprovado_em, submetido_em, criado_em)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
                    [$usuario_id, $plan_intent, $plano_ativo, $status, $cpf_cnpj ?: null, $tipo_pessoa,
                     $aprovado_por, $aprovado_em, $submetido_em]
                );
                $empresa_id = (int) DB::lastId();

                DB::exec(
                    'INSERT INTO empresa_logs (empresa_id, admin_id, acao, detalhe, criado_em)
                     VALUES (?, ?, "criada_manual", ?, NOW())',
                    [$empresa_id, $admin_id, "Empresa criada manualmente pelo admin com status: $status"]
                );

                DB::commit();

                $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Empresa cadastrada com sucesso. Agora vincule um lugar a ela.'];
                header('Location: /admin/empresas/index.php');
                exit;

            } catch (Exception $e) {
                DB::rollback();
                error_log('[ADMIN] Erro ao criar empresa: ' . $e->getMessage());
                $erros[] = 'Erro interno ao salvar. Tente novamente.';
            }
        }
    }
}

include __DIR__ . '/../_layout.php';
?>

<style>
    .form-label-admin {
        display: block;
        font-size: .625rem; font-weight: 900;
        letter-spacing: .18em; text-transform: uppercase;
        color: var(--warmgray); margin-bottom: .375rem;
    }
    .form-field {
        width: 100%;
        padding: .625rem .875rem;
        background: var(--offwhite);
        border: 1.5px solid rgba(61,71,51,.1);
        border-radius: .75rem;
        font-family: 'Montserrat', sans-serif;
        font-size: .84375rem;
        color: var(--graphite);
        outline: none;
        transition: border-color .2s;
    }
    .form-field:focus { border-color: rgba(201,170,107,.55); }

    .form-card {
        background: #fff;
        border: 1px solid rgba(61,71,51,.07);
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
        margin-bottom: 1.25rem;
    }
    .form-card-title {
        font-size: 1rem; font-weight: 700;
        color: var(--green-dk); margin: 0 0 1.25rem;
    }

    .error-box {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 1rem;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
    }
    .error-box-title { font-size: .8125rem; font-weight: 700; color: #b91c1c; margin-bottom: .375rem; }
    .error-box ul { margin: 0; padding-left: 1.25rem; }
    .error-box li { font-size: .8125rem; color: #dc2626; }

    .radio-opt {
        display: flex; align-items: center; gap: .5rem;
        padding: .625rem .875rem;
        background: var(--offwhite);
        border: 1.5px solid rgba(61,71,51,.1);
        border-radius: .75rem;
        cursor: pointer; font-size: .8125rem; color: var(--graphite);
        flex: 1;
    }
    .radio-opt input { accent-color: var(--green); }

    .btn-save {
        padding: .75rem 1.5rem;
        background: var(--green-dk); color: #fff;
        font-size: .75rem; font-weight: 900;
        letter-spacing: .1em; text-transform: uppercase;
        border: none; border-radius: 50px;
        cursor: pointer; transition: background .2s;
        font-family: 'Montserrat', sans-serif;
    }
    .btn-save:hover { background: var(--green); }

    .btn-cancel {
        padding: .75rem 1.5rem;
        background: var(--offwhite); color: var(--graphite);
        font-size: .75rem; font-weight: 700;
        border: none; border-radius: 50px;
        text-decoration: none; transition: background .2s;
    }
    .btn-cancel:hover { background: var(--gold-pale); color: var(--graphite); }
</style>

<?php if (!empty($erros)): ?>
<div class="error-box">
    <p class="error-box-title">Corrija os erros:</p>
    <ul>
        <?php foreach ($erros as $e): ?>
        <li><?= Sanitize::html($e) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST" novalidate style="max-width:640px">
<input type="hidden" name="_token" value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>

<div class="form-card">
    <h3 class="form-card-title">Responsável / Login</h3>

    <div class="row g-3">
        <div class="col-12">
            <label class="form-label-admin">Nome completo *</label>
            <input type="text" name="nome" value="<?= Sanitize::html($nome) ?>" required class="form-field"/>
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label-admin">E-mail *</label>
            <input type="email" name="email" value="<?= Sanitize::html($email) ?>" required class="form-field"/>
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label-admin">Senha *</label>
            <input type="text" name="senha" placeholder="Mínimo 8 caracteres" required class="form-field"/>
        </div>
    </div>
</div>

<div class="form-card">
    <h3 class="form-card-title">Dados da empresa</h3>

    <div class="row g-3">
        <div class="col-12">
            <label class="form-label-admin">Tipo de pessoa</label>
            <div class="d-flex gap-2">
                <label class="radio-opt">
                    <input type="radio" name="tipo_pessoa" value="pj" <?= $tipo_pessoa==='pj'?'checked':'' ?>> Pessoa jurídica
                </label>
                <label class="radio-opt">
                    <input type="radio" name="tipo_pessoa" value="pf" <?= $tipo_pessoa==='pf'?'checked':'' ?>> Pessoa física
                </label>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label-admin">CPF / CNPJ</label>
            <input type="text" name="cpf_cnpj" value="<?= Sanitize::html($cpf_cnpj) ?>" class="form-field"/>
        </div>
    </div>
</div>

<div class="form-card">
    <h3 class="form-card-title">Plano &amp; Status</h3>

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label class="form-label-admin">Plano</label>
            <select name="plan_intent" class="form-field">
                <?php foreach (['essencial'=>'Essencial','profissional'=>'Profissional','premium'=>'Premium'] as $v=>$l): ?>
                <option value="<?= $v ?>" <?= $plan_intent===$v?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label-admin">Status inicial</label>
            <select name="status" class="form-field">
                <?php foreach (['aprovada'=>'Aprovada (pronta para vincular lugar)','pendente'=>'Pendente (aguardando análise)','rascunho'=>'Rascunho'] as $v=>$l): ?>
                <option value="<?= $v ?>" <?= $status===$v?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
            <p style="font-size:.6875rem; color:var(--warmgray); margin:.375rem 0 0">
                "Aprovada" já ativa o plano escolhido. Depois é só ir em Lugares → Novo/Editar e vincular este usuário no bloco "Empresa &amp; Plano".
            </p>
        </div>
    </div>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn-save">Cadastrar empresa</button>
    <a href="/admin/empresas/index.php" class="btn-cancel">Cancelar</a>
</div>

</form>

<?php include __DIR__ . '/../_layout_end.php'; ?>
