<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#3d4733">
<title>Ficha de Cadastro · Uso interno · Guia Campo Belo &amp; Região</title>
<link rel="icon" type="image/png" href="/assets/img/logo.png">

<!--
  ============================================================================
  GUIA CAMPO BELO & REGIÃO — Ficha de cadastro de campo · USO INTERNO · v2
  Espelha a aba "Base de Cadastro" (planilha 1TDHVfX7KCBqoDnds0TpEc8w_G0XRKB3O)
  ----------------------------------------------------------------------------
  Envia para /cadastro/salvar.php, que grava na tabela `prospects` e salva a
  foto da fachada em /assets/img/prospects/. Os cadastros aparecem no admin
  em /admin/prospects/.

  Link por vendedor: /cadastro/?vendedor=Nome%20do%20Vendedor
  CEP consulta ViaCEP e preenche o bairro. Falha em silêncio se estiver offline
  ou se o navegador bloquear por CSP (nesse caso, adicione viacep.com.br ao
  connect-src do nginx-seguranca.conf).
  ============================================================================
-->

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
:root{
  --verde:#3d4733; --dourado:#c9aa6b; --areia:#d2b48c;
  --cinza:#8b8589; --grafite:#1d1d1b; --papel:#fbfaf7;
  --linha:rgba(61,71,51,.16); --erro:#8c3b2e;
  --toque:44px;
}
*,*::before,*::after{box-sizing:border-box}
body{
  margin:0;font-family:'Montserrat',system-ui,-apple-system,sans-serif;
  background:var(--papel);color:var(--grafite);line-height:1.55;font-size:15px;
  -webkit-font-smoothing:antialiased;
  padding-bottom:calc(7rem + env(safe-area-inset-bottom));
}
h1,h2,p,ul{margin:0}ul{list-style:none;padding:0}
.wrap{width:min(880px,calc(100% - 2rem));margin-inline:auto}

/* topo */
.topo{background:var(--verde);color:#f4f2ec;padding:1.75rem 0 1.5rem}
.eyebrow{font-size:.66rem;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:var(--dourado)}
.topo h1{font-size:clamp(1.5rem,4.5vw,2.05rem);font-weight:300;line-height:1.1;letter-spacing:-.02em;margin:.7rem 0 .5rem}
.topo h1 strong{font-weight:600}
.topo p{color:rgba(244,242,236,.78);font-size:.9rem;max-width:58ch}
.topo .linha{
  margin-top:1.2rem;padding-top:.9rem;border-top:1px solid rgba(201,170,107,.3);
  display:flex;flex-wrap:wrap;gap:.35rem 1.5rem;font-size:.72rem;letter-spacing:.08em;
  text-transform:uppercase;color:rgba(244,242,236,.6);
}
.topo .linha b{color:var(--dourado);font-weight:600}
.topo .legenda{margin-top:.6rem;font-size:.72rem;color:rgba(244,242,236,.6);text-transform:none;letter-spacing:0}
.topo .legenda i{color:var(--dourado);font-style:normal;font-weight:700}

/* blocos */
.bloco{padding:2rem 0 2.25rem;border-top:1px solid var(--linha)}
.bloco__tag{display:inline-block;font-size:.64rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:#fff;padding:.3rem .6rem;border-radius:2px;margin-bottom:.75rem}
.b-verde .bloco__tag{background:var(--verde)}
.b-grafite .bloco__tag{background:var(--grafite)}
.b-cinza .bloco__tag{background:var(--cinza)}
.b-dourado .bloco__tag{background:var(--dourado);color:var(--grafite)}
.bloco h2{font-size:1.35rem;font-weight:300;color:var(--verde);letter-spacing:-.01em;line-height:1.2}
.bloco h2 em{font-style:normal;font-weight:600}
.bloco__sub{font-size:.86rem;color:#6b6b64;margin-top:.35rem;max-width:60ch}

/* grade */
.grade{display:grid;gap:1.15rem;margin-top:1.4rem}
@media(min-width:720px){.grade{grid-template-columns:1fr 1fr}.col2{grid-column:1/-1}}
.campo{scroll-margin:6rem 0 8rem}
.campo>label,.campo .rotulo{display:block;font-size:.84rem;font-weight:600;margin-bottom:.28rem}
.req::before{content:"•";color:var(--dourado);font-weight:700;margin-right:.35rem}
.dica{font-size:.77rem;color:#7c7c75;margin:-.1rem 0 .45rem}
.campo input[type=text],.campo input[type=tel],.campo input[type=email],
.campo input[type=url],.campo input[type=time],.campo textarea{
  width:100%;font:inherit;font-size:1rem;background:#fff;color:var(--grafite);
  border:1px solid var(--linha);border-radius:2px;padding:.7rem .75rem;min-height:var(--toque);
}
.campo textarea{min-height:5.5rem;resize:vertical}
.campo input:focus,.campo textarea:focus{outline:none;border-color:var(--verde);box-shadow:0 0 0 3px rgba(61,71,51,.09)}
.campo input::placeholder,.campo textarea::placeholder{color:#b8b8b1}
.dupla{display:grid;grid-template-columns:1fr 1fr;gap:.6rem}

/* chips */
.chips{display:flex;flex-wrap:wrap;gap:.4rem}
.chips input{position:absolute;opacity:0;pointer-events:none}
.chips label{
  display:flex;align-items:center;justify-content:center;
  min-height:var(--toque);padding:.5rem .8rem;font-size:.85rem;line-height:1.2;
  background:#fff;border:1px solid var(--linha);border-radius:2px;cursor:pointer;user-select:none;
  -webkit-tap-highlight-color:transparent;
}
.chips label:hover{border-color:var(--areia)}
.chips input:checked+label{background:var(--verde);border-color:var(--verde);color:#f4f2ec;font-weight:500}
.chips input:focus-visible+label{outline:2px solid var(--dourado);outline-offset:2px}
.chips--sn label{min-width:4.5rem}
.chips--preco label{min-width:3.6rem;font-weight:600}
.chips--dias{display:grid;grid-template-columns:repeat(7,1fr);gap:.35rem}
.chips--dias label{padding:.5rem .2rem;font-size:.8rem;font-weight:600;letter-spacing:.02em}
@media(max-width:400px){.chips--dias label{font-size:.72rem}}

/* horário */
.horario__pares{margin-top:.9rem}
.horario__rot{font-size:.78rem;font-weight:600;color:#5a5a54;margin-bottom:.3rem}
.horario-dia-linha{display:grid;grid-template-columns:4.4rem 1fr 1fr;gap:.5rem;align-items:center;margin-bottom:.5rem}
.horario-dia-linha span{font-size:.82rem;font-weight:600;color:var(--grafite)}
.horario-dia-linha input[type=time]{width:100%;font:inherit;font-size:.92rem;background:#fff;color:var(--grafite);border:1px solid var(--linha);border-radius:2px;padding:.55rem .5rem;min-height:2.5rem}
.alternar{
  display:flex;align-items:center;gap:.6rem;margin-top:.85rem;
  font-size:.82rem;color:#5a5a54;cursor:pointer;min-height:var(--toque);
}
.alternar input{
  appearance:none;width:1.2rem;height:1.2rem;flex:0 0 auto;background:#fff;
  border:1px solid var(--verde);border-radius:2px;cursor:pointer;position:relative;
}
.alternar input:checked{background:var(--verde)}
.alternar input:checked::after{content:"";position:absolute;left:.38rem;top:.12rem;width:.28rem;height:.58rem;border:solid #f4f2ec;border-width:0 2px 2px 0;transform:rotate(45deg)}
.fds{display:none;margin-top:.7rem;padding-left:1.8rem;border-left:2px solid var(--areia)}
.fds.on{display:block}
.resumo{
  margin-top:.9rem;padding:.7rem .85rem;background:rgba(201,170,107,.12);
  border-left:3px solid var(--dourado);font-size:.86rem;color:#4f4f4a;
}
.resumo b{font-weight:600;color:var(--grafite)}
.resumo em{font-style:normal;color:var(--cinza)}
.ajustar{background:none;border:0;padding:.2rem 0;margin-top:.35rem;font:inherit;font-size:.78rem;color:var(--verde);text-decoration:underline;text-underline-offset:3px;cursor:pointer}
#fechaManual{display:none;margin-top:.6rem}
#fechaManual.on{display:block}

/* foto */
.foto input[type=file]{position:absolute;opacity:0;width:0;height:0}
.foto__btn{display:flex;align-items:center;gap:.6rem;width:100%;padding:.9rem;min-height:var(--toque);background:#fff;border:1px dashed var(--areia);border-radius:2px;font:inherit;font-size:.9rem;color:var(--verde);cursor:pointer;text-align:left}
.foto__btn:hover{border-color:var(--verde)}
.foto__preview{margin-top:.55rem;display:none}
.foto__preview img{width:100%;max-height:200px;object-fit:cover;border:1px solid var(--linha);display:block}
.foto__preview button{margin-top:.4rem;background:none;border:0;padding:.3rem 0;font:inherit;font-size:.8rem;color:var(--erro);text-decoration:underline;cursor:pointer}

/* consentimento */
.consent{background:rgba(61,71,51,.05);border-left:3px solid var(--verde);padding:1.1rem;display:flex;gap:.75rem;align-items:flex-start}
.consent input[type=checkbox]{appearance:none;flex:0 0 auto;width:1.4rem;height:1.4rem;margin-top:.1rem;background:#fff;border:1px solid var(--verde);border-radius:2px;cursor:pointer;position:relative}
.consent input[type=checkbox]:checked{background:var(--verde)}
.consent input[type=checkbox]:checked::after{content:"";position:absolute;left:.46rem;top:.16rem;width:.3rem;height:.66rem;border:solid #f4f2ec;border-width:0 2px 2px 0;transform:rotate(45deg)}
.consent label{font-size:.83rem;color:#4f4f4a;cursor:pointer}

/* erro */
.erro{display:none;font-size:.78rem;color:var(--erro);margin-top:.32rem;font-weight:500}
.invalido input,.invalido textarea{border-color:var(--erro)}
.invalido .erro{display:block}

/* barra */
.barra{position:fixed;left:0;right:0;bottom:0;z-index:20;background:rgba(251,250,247,.97);backdrop-filter:blur(8px);border-top:1px solid var(--linha);padding:.8rem 0 calc(.8rem + env(safe-area-inset-bottom))}
.barra .wrap{display:flex;align-items:center;gap:1rem}
.barra__status{flex:1;min-width:0}
.barra__status b{display:block;font-size:.79rem;font-weight:600;color:var(--verde)}
.barra__status span{font-size:.72rem;color:var(--cinza)}
.barra__trilho{height:3px;background:var(--linha);margin-top:.35rem;overflow:hidden}
.barra__trilho i{display:block;height:100%;width:0;background:var(--dourado);transition:width .25s}
.btn{flex:0 0 auto;font:inherit;font-size:.9rem;font-weight:600;background:var(--verde);color:#f4f2ec;border:0;border-radius:2px;padding:.9rem 1.4rem;min-height:var(--toque);cursor:pointer}
.btn:hover{background:#333c2b}
.btn:disabled{background:var(--cinza);cursor:not-allowed}
.btn:focus-visible{outline:2px solid var(--dourado);outline-offset:3px}

/* sucesso */
.sucesso{display:none;position:fixed;inset:0;z-index:40;background:var(--verde);color:#f4f2ec;place-items:center;text-align:center;padding:2rem}
.sucesso.on{display:grid}
.sucesso h2{font-size:1.8rem;font-weight:300;line-height:1.2;margin-bottom:.8rem}
.sucesso h2 strong{display:block;font-weight:600}
.sucesso p{color:rgba(244,242,236,.82);max-width:36ch;margin:0 auto 1.5rem;font-size:.95rem}
.sucesso .assinatura{font-size:.72rem;letter-spacing:.16em;text-transform:uppercase;color:var(--dourado);margin-bottom:1.8rem}
.sucesso button{font:inherit;font-size:.9rem;font-weight:600;background:transparent;color:#f4f2ec;border:1px solid rgba(244,242,236,.5);border-radius:2px;padding:.85rem 1.6rem;cursor:pointer}
.sucesso button:hover{border-color:var(--dourado);color:var(--dourado)}
@media(prefers-reduced-motion:reduce){*{transition:none!important}}
</style>
</head>
<body>

<header class="topo">
  <div class="wrap">
    <p class="eyebrow">Uso interno · não enviar a terceiros</p>
    <h1>Ficha de <strong>Cadastro</strong></h1>
    <p>Preencha com o que der para levantar na visita. Campo em branco não trava o envio — mas campo em branco também não é zero: é cadastro pela metade.</p>
    <p class="linha">
      <span>Vendedor · <b id="nomeVendedor">—</b></span>
      <span>Destino · <b>Entrada de Campo</b></span>
      <span>Triagem gera o ID</span>
    </p>
    <p class="legenda"><i>•</i> marca os sete campos obrigatórios. Toque de novo numa opção marcada para desmarcar.</p>
  </div>
</header>

<main id="form">

  <section class="bloco b-verde" id="blocoVendedor" hidden>
    <div class="wrap">
      <span class="bloco__tag">Identificação</span>
      <h2>Quem está <em>cadastrando</em></h2>
      <div class="grade">
        <div class="campo" data-req="vendedor">
          <label class="req" for="vendedor">Seu nome</label>
          <p class="dica">Normalmente vem pelo link. Se não veio, escreva.</p>
          <input type="text" id="vendedor" placeholder="Nome e sobrenome">
          <p class="erro">Obrigatório.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- EMPRESA E LOCALIZAÇÃO -->
  <section class="bloco b-verde">
    <div class="wrap">
      <span class="bloco__tag">Empresa e localização</span>
      <h2>O <em>estabelecimento</em></h2>
      <p class="bloco__sub">Razão social e CNPJ costumam estar no alvará atrás do balcão ou na nota fiscal. Se não vierem agora, a triagem busca depois.</p>
      <div class="grade">

        <div class="campo" data-req="nome_fantasia">
          <label class="req" for="nome_fantasia">Nome fantasia</label>
          <input type="text" id="nome_fantasia" placeholder="Como está na fachada">
          <p class="erro">Obrigatório.</p>
        </div>

        <div class="campo">
          <label for="razao_social">Razão social</label>
          <input type="text" id="razao_social" placeholder="Nome jurídico completo">
        </div>

        <div class="campo">
          <label for="cnpj">CNPJ</label>
          <input type="text" id="cnpj" inputmode="numeric" maxlength="18" placeholder="00.000.000/0001-00">
        </div>

        <div class="campo">
          <label for="ano_fundacao">Ano de fundação</label>
          <input type="text" id="ano_fundacao" inputmode="numeric" maxlength="4" placeholder="2019">
        </div>

        <div class="campo col2" data-req="categoria">
          <span class="rotulo req">Categoria</span>
          <div class="chips" id="categoria" role="radiogroup" aria-label="Categoria"></div>
          <p class="erro">Escolha uma.</p>
        </div>

        <div class="campo col2">
          <label for="subcategoria">Subcategoria</label>
          <p class="dica">O nível fino, do jeito que a casa se descreve: cafeteria especial, omakase, pilates clínico.</p>
          <input type="text" id="subcategoria" placeholder="Como eles mesmos se chamam">
        </div>

        <div class="campo col2" data-req="endereco">
          <label class="req" for="endereco">Endereço · rua e número</label>
          <input type="text" id="endereco" placeholder="Rua Vieira de Morais, 1141">
          <p class="erro">Obrigatório.</p>
        </div>

        <div class="campo">
          <label for="cep">CEP</label>
          <p class="dica">Preenche o bairro sozinho.</p>
          <input type="text" id="cep" inputmode="numeric" maxlength="9" placeholder="04600-000">
        </div>

        <div class="campo">
          <label for="bairro">Bairro</label>
          <input type="text" id="bairro" placeholder="Campo Belo">
        </div>

        <div class="campo">
          <label for="complemento">Complemento</label>
          <input type="text" id="complemento" placeholder="Loja 2, conjunto 41">
        </div>

        <div class="campo">
          <label for="maps">Link do Google Maps</label>
          <p class="dica">Compartilhar → copiar link. Garante o pin certo no mapa.</p>
          <input type="url" id="maps" inputmode="url" placeholder="https://maps.app.goo.gl/…">
        </div>

        <div class="campo col2" data-req="regiao">
          <span class="rotulo req">Região do Guia</span>
          <div class="chips" id="regiao" role="radiogroup" aria-label="Região do Guia"></div>
          <p class="erro">Escolha uma.</p>
        </div>

      </div>
    </div>
  </section>

  <!-- CONTATO E DECISOR -->
  <section class="bloco b-grafite">
    <div class="wrap">
      <span class="bloco__tag">Contato e decisor</span>
      <h2>Com quem se <em>fala de verdade</em></h2>
      <p class="bloco__sub">Cadastro feito com quem não decide não é contato comercial — e isso precisa aparecer na triagem.</p>
      <div class="grade">

        <div class="campo">
          <label for="decisor">Nome do decisor</label>
          <input type="text" id="decisor" placeholder="Nome e sobrenome">
        </div>

        <div class="campo">
          <span class="rotulo">Cargo</span>
          <div class="chips" id="cargo" role="radiogroup" aria-label="Cargo"></div>
        </div>

        <div class="campo" data-req="whatsapp">
          <label class="req" for="whatsapp">WhatsApp comercial</label>
          <p class="dica">Único campo que, errado, invalida o cadastro inteiro.</p>
          <input type="tel" id="whatsapp" inputmode="numeric" maxlength="16" placeholder="(11) 91234-5678">
          <p class="erro">Informe um número com DDD.</p>
        </div>

        <div class="campo">
          <label for="fixo">Telefone fixo</label>
          <input type="tel" id="fixo" inputmode="numeric" maxlength="15" placeholder="(11) 3000-0000">
        </div>

        <div class="campo col2">
          <label for="email">E-mail comercial</label>
          <input type="email" id="email" inputmode="email" autocapitalize="off" autocorrect="off" placeholder="contato@estabelecimento.com.br">
        </div>

      </div>
    </div>
  </section>

  <!-- PRESENÇA DIGITAL -->
  <section class="bloco b-grafite">
    <div class="wrap">
      <span class="bloco__tag">Presença digital</span>
      <h2>Onde ele já <em>aparece</em></h2>
      <p class="bloco__sub">Seguidores e nota do Google se conferem no celular, ali mesmo. Dado estimado no olho não entra — deixe em branco.</p>
      <div class="grade">

        <div class="campo">
          <label for="instagram">Instagram</label>
          <p class="dica">Só o @. Se colar o link, o campo converte sozinho.</p>
          <input type="text" id="instagram" inputmode="text" autocapitalize="off" autocorrect="off" placeholder="@nomedolugar">
        </div>

        <div class="campo">
          <label for="seguidores">Seguidores no Instagram</label>
          <p class="dica">Número exato do perfil. Não arredonde.</p>
          <input type="text" id="seguidores" inputmode="numeric" placeholder="4820">
        </div>

        <div class="campo">
          <label for="site">Site próprio</label>
          <input type="text" id="site" inputmode="url" autocapitalize="off" placeholder="www.exemplo.com.br">
        </div>

        <div class="campo">
          <label for="delivery">Delivery · iFood</label>
          <input type="text" id="delivery" placeholder="iFood e retirada · só retirada · não faz">
        </div>

        <div class="campo col2">
          <span class="rotulo">Nota do Google</span>
          <p class="dica">Verificada no app, com o número de avaliações.</p>
          <div class="dupla">
            <input type="text" id="nota_valor" inputmode="decimal" maxlength="3" placeholder="4,7" aria-label="Nota">
            <input type="text" id="nota_qtd" inputmode="numeric" placeholder="312 avaliações" aria-label="Número de avaliações">
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- OPERAÇÃO -->
  <section class="bloco b-cinza">
    <div class="wrap">
      <span class="bloco__tag">Operação</span>
      <h2>Como a casa <em>funciona</em></h2>
      <p class="bloco__sub">Este bloco é o que vira ficha no site depois. Quanto mais concreto aqui, menos volta de checagem lá na frente.</p>
      <div class="grade">

        <!-- HORÁRIO -->
        <div class="campo col2">
          <span class="rotulo">Dias em que abre</span>
          <p class="dica">Toque nos dias. O que sobrar entra sozinho como dia de fechamento.</p>
          <div class="chips chips--dias" id="dias" role="group" aria-label="Dias em que abre"></div>

          <label class="alternar">
            <input type="checkbox" id="horarioUniforme" checked>
            <span>Mesmo horário em todos os dias abertos</span>
          </label>

          <div class="horario__pares" id="horarioUniformeBox">
            <p class="horario__rot">Horário</p>
            <div class="dupla">
              <input type="time" id="abre" aria-label="Abre às">
              <input type="time" id="fecha" aria-label="Fecha às">
            </div>
          </div>

          <div class="horario__pares" id="horarioPorDiaBox" style="display:none">
            <p class="horario__rot">Horário de cada dia</p>
            <div id="horarioPorDiaLista"></div>
          </div>

          <div class="resumo" id="resumoHorario" aria-live="polite">
            <b>Horário:</b> <span id="resumoTexto"><em>selecione os dias e as horas</em></span><br>
            <b>Fecha:</b> <span id="resumoFecha"><em>—</em></span>
            <button type="button" class="ajustar" id="btnAjustar">Ajustar dia de fechamento</button>
            <div id="fechaManual">
              <div class="chips chips--dias" id="diasFecha" role="group" aria-label="Dias de fechamento"></div>
            </div>
          </div>

          <div class="horario__pares">
            <label class="alternar">
              <input type="checkbox" id="feriadoAbre">
              <span>Abre em feriados</span>
            </label>

            <div class="fds" id="blocoFeriado">
              <label class="alternar" style="margin-top:0">
                <input type="checkbox" id="feriadoDiferente">
                <span>Horário diferente do dia da semana correspondente</span>
              </label>
              <div class="dupla" id="feriadoHorarioPar" style="display:none;margin-top:.6rem">
                <input type="time" id="abreFeriado" aria-label="Abre às, feriado">
                <input type="time" id="fechaFeriado" aria-label="Fecha às, feriado">
              </div>
            </div>
          </div>

          <div style="margin-top:.9rem">
            <label for="horario_obs" style="display:block;font-size:.84rem;font-weight:600;margin-bottom:.28rem">Exceção de horário</label>
            <p class="dica">Só se houver. Ex.: cozinha fecha das 15h às 18h.</p>
            <input type="text" id="horario_obs" placeholder="Cozinha fecha 15h–18h">
          </div>
        </div>

        <div class="campo">
          <span class="rotulo">Faixa de preço</span>
          <p class="dica">Âncoras em reais por categoria: [CONFIRMAR]. Sem elas, cada vendedor usa uma régua diferente.</p>
          <div class="chips chips--preco" id="faixa" role="radiogroup" aria-label="Faixa de preço"></div>
        </div>

        <div class="campo">
          <label for="ticket">Ticket médio em R$</label>
          <p class="dica">O que a casa informa, não o que você estima.</p>
          <input type="text" id="ticket" inputmode="numeric" placeholder="68">
        </div>

        <div class="campo">
          <label for="capacidade">Capacidade em lugares</label>
          <input type="text" id="capacidade" inputmode="numeric" placeholder="32">
        </div>

        <div class="campo">
          <label for="pagamento_formas">Formas de pagamento</label>
          <input type="text" id="pagamento_formas" placeholder="Pix, crédito, débito, VR">
        </div>

        <div class="campo">
          <span class="rotulo">Reservas</span>
          <div class="chips" id="reservas" role="radiogroup" aria-label="Reservas"></div>
        </div>

        <div class="campo">
          <span class="rotulo">Estacionamento · manobrista</span>
          <div class="chips chips--sn" id="estacionamento" role="radiogroup" aria-label="Estacionamento"></div>
        </div>

        <div class="campo">
          <span class="rotulo">Acessibilidade</span>
          <p class="dica">Entrada sem degrau e banheiro adaptado. Parcial vale quando só um dos dois existe.</p>
          <div class="chips chips--sn" id="acessibilidade" role="radiogroup" aria-label="Acessibilidade"></div>
        </div>

        <div class="campo">
          <span class="rotulo">Pet friendly</span>
          <div class="chips chips--sn" id="pet" role="radiogroup" aria-label="Pet friendly"></div>
        </div>

      </div>
    </div>
  </section>

  <!-- COMERCIAL E CONNECT -->
  <section class="bloco b-dourado">
    <div class="wrap">
      <span class="bloco__tag">Comercial e GUIA CONNECT</span>
      <h2>Leitura de <em>quem esteve lá</em></h2>
      <p class="bloco__sub">As duas últimas são avaliação sua, não informação da casa. Responda pensando na sala do evento, não no faturamento dele.</p>
      <div class="grade">
        <div class="campo col2">
          <span class="rotulo">Origem do lead</span>
          <div class="chips" id="origem_lead" role="radiogroup" aria-label="Origem do lead"></div>
        </div>
        <div class="campo">
          <span class="rotulo">Convidar para o CONNECT?</span>
          <div class="chips chips--sn" id="convidar_connect" role="radiogroup" aria-label="Convidar para o CONNECT"></div>
        </div>
        <div class="campo">
          <span class="rotulo">Potencial de patrocínio</span>
          <div class="chips" id="potencial" role="radiogroup" aria-label="Potencial de patrocínio"></div>
        </div>
      </div>
    </div>
  </section>

  <!-- REGISTRO DA VISITA -->
  <section class="bloco b-verde">
    <div class="wrap">
      <span class="bloco__tag">Registro da visita</span>
      <h2>O que só quem <em>esteve lá</em> sabe</h2>
      <div class="grade">
        <div class="campo col2" data-req="foto">
          <span class="rotulo req">Foto da fachada</span>
          <p class="dica">Fachada inteira, de frente. É o que confirma na triagem que o lugar existe e é o que parece.</p>
          <div class="foto">
            <input type="file" id="foto" accept="image/*" capture="environment">
            <button type="button" class="foto__btn" id="fotoBtn">
              <span aria-hidden="true">📍</span><span>Tirar foto ou escolher da galeria</span>
            </button>
            <div class="foto__preview" id="fotoPreview">
              <img id="fotoImg" alt="Pré-visualização da fachada">
              <button type="button" id="fotoTrocar">Trocar foto</button>
            </div>
          </div>
          <p class="erro">Obrigatória.</p>
        </div>
        <div class="campo col2">
          <label for="observacoes">Observações comerciais</label>
          <p class="dica">Fila, horário morto, o prato que todo mundo pede, obra na fachada, sócio que pediu retorno depois da reforma.</p>
          <textarea id="observacoes" placeholder="Escreva do jeito que você contaria para o time."></textarea>
        </div>
      </div>
    </div>
  </section>

  <!-- CONSENTIMENTO -->
  <section class="bloco b-verde">
    <div class="wrap">
      <span class="bloco__tag">Conformidade</span>
      <h2>Consentimento <em>do decisor</em></h2>
      <p class="bloco__sub">Marque na frente dele, depois de ler em voz alta. É o registro da base legal para o dado pessoal coletado.</p>
      <div class="grade">
        <div class="campo col2" data-req="consentimento">
          <div class="consent">
            <input type="checkbox" id="consentimento">
            <label for="consentimento">
              O responsável autorizou o Guia Campo Belo &amp; Região a usar os dados acima para contato comercial e avaliação de inclusão no guia. Tratamento conforme a Lei 13.709/2018, sem compartilhamento com terceiros. Correção ou exclusão pelo e-mail <b>[CONFIRMAR]</b>. Controlador: <b>[CONFIRMAR: razão social e CNPJ]</b>.
            </label>
          </div>
          <p class="erro">Sem o aceite, o cadastro não pode ser enviado.</p>
        </div>
      </div>
    </div>
  </section>

</main>

<div class="barra">
  <div class="wrap">
    <div class="barra__status">
      <b id="statusTexto">Faltam 6 campos obrigatórios</b>
      <span id="statusExtra">0 de 30 campos opcionais preenchidos</span>
      <div class="barra__trilho"><i id="statusBarra"></i></div>
    </div>
    <button class="btn" id="enviar" type="button">Enviar cadastro</button>
  </div>
</div>

<div class="sucesso" id="sucesso" role="status">
  <div>
    <h2>Cadastro<strong>registrado.</strong></h2>
    <p class="assinatura">Dica boa não fica solta… fica Salva!</p>
    <button type="button" id="novo">Cadastrar o próximo</button>
  </div>
</div>

<script>
const ENDPOINT = "/cadastro/salvar.php";

/* ---------- Listas (espelham a aba "Listas") ---------- */
const LISTAS = {
  categoria:["Gastronomia","Café e Padaria","Bar e Vida Noturna","Beleza e Estética","Saúde e Bem-estar","Moda e Varejo","Casa e Decoração","Serviços Profissionais","Educação","Pet","Imobiliário","Fitness e Esporte","Cultura e Lazer","Automotivo","Outros"],
  regiao:["Campo Belo","Brooklin","Moema","Alto da Boa Vista","Itaim Bibi","Vila Nova Conceição","Adjacências"],
  cargo:["Proprietário","Sócio","Gerente","Funcionário","Não falei com ninguém"],
  faixa:["$","$$","$$$","$$$$"],
  reservas:["Aceita","Não aceita","Somente grupos","Fila de espera"],
  estacionamento:["Sim","Não","Parcial"],
  acessibilidade:["Sim","Não","Parcial"],
  pet:["Sim","Não","Parcial"],
  origem_lead:["Instagram","Indicação","Prospecção ativa","GUIA CONNECT","Site","Visita presencial","Outros"],
  convidar_connect:["Sim","Não","Pendente"],
  potencial:["Alto","Médio","Baixo","Não avaliado"]
};
for(const [nome,itens] of Object.entries(LISTAS)){
  const el = document.getElementById(nome); if(!el) continue;
  el.innerHTML = itens.map((v,i)=>{
    const id = nome+"_"+i;
    return `<input type="radio" name="${nome}" id="${id}" value="${v}"><label for="${id}">${v}</label>`;
  }).join("");
}
/* Toque de novo desmarca o rádio */
document.addEventListener("click", e=>{
  const lab = e.target.closest(".chips label"); if(!lab) return;
  const inp = document.getElementById(lab.htmlFor);
  if(inp && inp.type === "radio" && inp.checked){
    e.preventDefault(); inp.checked = false; atualizar();
  }
});

/* ---------- Dias da semana ---------- */
const DIAS = [
  {s:"Seg",l:"Segunda"},{s:"Ter",l:"Terça"},{s:"Qua",l:"Quarta"},
  {s:"Qui",l:"Quinta"},{s:"Sex",l:"Sexta"},{s:"Sáb",l:"Sábado"},{s:"Dom",l:"Domingo"}
];
document.getElementById("dias").innerHTML = DIAS.map((d,i)=>
  `<input type="checkbox" name="dias" id="dia_${i}" value="${i}"><label for="dia_${i}" title="${d.l}">${d.s}</label>`).join("");
document.getElementById("diasFecha").innerHTML = DIAS.map((d,i)=>
  `<input type="checkbox" name="diasFecha" id="df_${i}" value="${i}"><label for="df_${i}" title="${d.l}">${d.s}</label>`).join("");

let fechaManualAtivo = false;
document.getElementById("btnAjustar").addEventListener("click", ()=>{
  fechaManualAtivo = !fechaManualAtivo;
  const box = document.getElementById("fechaManual");
  box.classList.toggle("on", fechaManualAtivo);
  document.getElementById("btnAjustar").textContent =
    fechaManualAtivo ? "Voltar ao preenchimento automático" : "Ajustar dia de fechamento";
  if(!fechaManualAtivo) document.querySelectorAll('input[name="diasFecha"]').forEach(i=>i.checked=false);
  montarHorario();
});

const marcados = n => [...document.querySelectorAll(`input[name="${n}"]:checked`)].map(i=>+i.value);

/* "Seg a sáb" quando os dias são contíguos; senão "Seg, qua e sex" */
function nomearDias(idx){
  if(!idx.length) return "";
  const ord = [...idx].sort((a,b)=>a-b);
  const contiguo = ord.every((v,i)=> i===0 || v === ord[i-1]+1);
  if(ord.length === 7) return "Todos os dias";
  if(contiguo && ord.length > 2) return DIAS[ord[0]].s+" a "+DIAS[ord[ord.length-1]].s.toLowerCase();
  const nomes = ord.map(i=>DIAS[i].s);
  return nomes.length === 1 ? nomes[0] : nomes.slice(0,-1).join(", ")+" e "+nomes[nomes.length-1].toLowerCase();
}
const hhmm = v => !v ? "" : (()=>{ const [h,m] = v.split(":"); return m === "00" ? (+h)+"h" : (+h)+"h"+m; })();

/* ---------- Horário por dia individual ---------- */
const horarioPorDiaValores = {}; // { idxDia: {abre, fecha} }

function renderHorarioPorDia(){
  const abertos = marcados("dias");
  const lista = document.getElementById("horarioPorDiaLista");
  lista.innerHTML = abertos.map(i=>{
    const v = horarioPorDiaValores[i] || {};
    return `<div class="horario-dia-linha">
      <span>${DIAS[i].s}</span>
      <input type="time" data-dia="${i}" data-tipo="abre" value="${v.abre||""}" aria-label="Abre às, ${DIAS[i].l}">
      <input type="time" data-dia="${i}" data-tipo="fecha" value="${v.fecha||""}" aria-label="Fecha às, ${DIAS[i].l}">
    </div>`;
  }).join("") || `<p class="dica">Marque ao menos um dia acima.</p>`;

  lista.querySelectorAll("input[data-dia]").forEach(inp=>{
    inp.addEventListener("input", ()=>{
      const d = +inp.dataset.dia, t = inp.dataset.tipo;
      horarioPorDiaValores[d] = horarioPorDiaValores[d] || {};
      horarioPorDiaValores[d][t] = inp.value;
      atualizar();
    });
  });
}

document.getElementById("horarioUniforme").addEventListener("change", e=>{
  const uniforme = e.target.checked;
  document.getElementById("horarioUniformeBox").style.display = uniforme ? "" : "none";
  document.getElementById("horarioPorDiaBox").style.display = uniforme ? "none" : "";
  if(!uniforme) renderHorarioPorDia();
  atualizar();
});

document.getElementById("feriadoAbre").addEventListener("change", e=>{
  document.getElementById("blocoFeriado").classList.toggle("on", e.target.checked);
  atualizar();
});
document.getElementById("feriadoDiferente").addEventListener("change", e=>{
  document.getElementById("feriadoHorarioPar").style.display = e.target.checked ? "" : "none";
  atualizar();
});

function montarHorario(){
  const abertos = marcados("dias");
  const uniforme = document.getElementById("horarioUniforme").checked;

  if(!uniforme) renderHorarioPorDiaSeNecessario(abertos);

  let txt = "";
  if(abertos.length && uniforme){
    const a = hhmm(document.getElementById("abre").value);
    const f = hhmm(document.getElementById("fecha").value);
    txt = nomearDias(abertos) + (a&&f ? ", "+a+"–"+f : "");
  } else if(abertos.length && !uniforme){
    // agrupa dias consecutivos que têm o mesmo par abre/fecha
    const grupos = [];
    abertos.slice().sort((a,b)=>a-b).forEach(i=>{
      const v = horarioPorDiaValores[i] || {};
      const chave = (v.abre||"")+"|"+(v.fecha||"");
      const ultimo = grupos[grupos.length-1];
      if(ultimo && ultimo.chave === chave && i === ultimo.dias[ultimo.dias.length-1]+1){
        ultimo.dias.push(i);
      }else{
        grupos.push({chave, dias:[i]});
      }
    });
    txt = grupos.map(g=>{
      const v = horarioPorDiaValores[g.dias[0]] || {};
      const a = hhmm(v.abre), f = hhmm(v.fecha);
      return nomearDias(g.dias) + (a&&f ? ", "+a+"–"+f : "");
    }).join(" · ");
  }

  const feriadoAbre = document.getElementById("feriadoAbre").checked;
  if(feriadoAbre){
    const dif = document.getElementById("feriadoDiferente").checked;
    if(dif){
      const af = hhmm(document.getElementById("abreFeriado").value);
      const ff = hhmm(document.getElementById("fechaFeriado").value);
      txt = [txt, "Feriados"+(af&&ff ? ", "+af+"–"+ff : "")].filter(Boolean).join(" · ");
    }else{
      txt = [txt, "Feriados, mesmo horário"].filter(Boolean).join(" · ");
    }
  }

  document.getElementById("resumoTexto").innerHTML =
    txt || "<em>selecione os dias e as horas</em>";

  let fechados;
  if(fechaManualAtivo){
    fechados = marcados("diasFecha");
  }else{
    fechados = DIAS.map((_,i)=>i).filter(i=>!abertos.includes(i));
    document.querySelectorAll('input[name="diasFecha"]').forEach((inp,i)=>inp.checked = fechados.includes(i));
  }
  document.getElementById("resumoFecha").innerHTML =
    !abertos.length && !fechaManualAtivo ? "<em>—</em>" :
    (fechados.length ? nomearDias(fechados) : "Não fecha");
}

/* Só re-renderiza as linhas por dia quando o conjunto de dias marcados muda de fato,
   para não perder o foco/valor de quem está digitando */
let ultimosDiasRenderizados = "";
function renderHorarioPorDiaSeNecessario(abertos){
  const chave = abertos.join(",");
  if(chave !== ultimosDiasRenderizados){
    ultimosDiasRenderizados = chave;
    renderHorarioPorDia();
  }
}

/* ---------- Vendedor ---------- */
const paramVendedor = new URLSearchParams(location.search).get("vendedor");
if(paramVendedor){
  document.getElementById("nomeVendedor").textContent = paramVendedor;
}else{
  document.getElementById("blocoVendedor").hidden = false;
  document.getElementById("nomeVendedor").textContent = "a preencher";
}

/* ---------- Máscaras ---------- */
function mascara(id, fn){
  const el = document.getElementById(id);
  el.addEventListener("input", e=>{ e.target.value = fn(e.target.value.replace(/\D/g,"")); atualizar(); });
}
mascara("whatsapp", d=>{
  d = d.slice(0,11);
  if(d.length<=2) return d.length?"("+d:"";
  if(d.length<=6) return "("+d.slice(0,2)+") "+d.slice(2);
  const c = d.length>10?7:6;
  return "("+d.slice(0,2)+") "+d.slice(2,c)+"-"+d.slice(c);
});
mascara("fixo", d=>{
  d = d.slice(0,10);
  if(d.length<=2) return d.length?"("+d:"";
  if(d.length<=6) return "("+d.slice(0,2)+") "+d.slice(2);
  return "("+d.slice(0,2)+") "+d.slice(2,6)+"-"+d.slice(6);
});
mascara("cnpj", d=>{
  d = d.slice(0,14);
  return d.replace(/^(\d{2})(\d)/,"$1.$2").replace(/^(\d{2})\.(\d{3})(\d)/,"$1.$2.$3")
          .replace(/\.(\d{3})(\d)/,".$1/$2").replace(/(\d{4})(\d)/,"$1-$2");
});
mascara("cep", d=>{ d = d.slice(0,8); return d.length>5 ? d.slice(0,5)+"-"+d.slice(5) : d; });

/* CEP → bairro */
document.getElementById("cep").addEventListener("blur", async e=>{
  const d = e.target.value.replace(/\D/g,"");
  if(d.length !== 8) return;
  const campoBairro = document.getElementById("bairro");
  if(campoBairro.value.trim()) return;
  try{
    const ctrl = new AbortController();
    setTimeout(()=>ctrl.abort(), 3000);
    const r = await fetch("https://viacep.com.br/ws/"+d+"/json/",{signal:ctrl.signal});
    const j = await r.json();
    if(j && j.bairro){ campoBairro.value = j.bairro; atualizar(); }
  }catch(_){}
});

/* Instagram */
document.getElementById("instagram").addEventListener("blur", e=>{
  let v = e.target.value.trim(); if(!v) return;
  v = v.replace(/^https?:\/\/(www\.)?instagram\.com\//i,"").replace(/[\/?].*$/,"").replace(/^@+/,"");
  e.target.value = v ? "@"+v : "";
});

/* ---------- Foto ---------- */
let fotoBase64 = "";
const inputFoto = document.getElementById("foto");
document.getElementById("fotoBtn").addEventListener("click",()=>inputFoto.click());
document.getElementById("fotoTrocar").addEventListener("click",()=>inputFoto.click());
inputFoto.addEventListener("change", e=>{
  const arq = e.target.files[0]; if(!arq) return;
  const leitor = new FileReader();
  leitor.onload = ev=>{
    const img = new Image();
    img.onload = ()=>{
      const max = 1600; let {width:w,height:h} = img;
      if(w>max||h>max){ const r = max/Math.max(w,h); w = Math.round(w*r); h = Math.round(h*r); }
      const c = document.createElement("canvas"); c.width=w; c.height=h;
      c.getContext("2d").drawImage(img,0,0,w,h);
      fotoBase64 = c.toDataURL("image/jpeg",0.8);
      document.getElementById("fotoImg").src = fotoBase64;
      document.getElementById("fotoPreview").style.display = "block";
      document.getElementById("fotoBtn").querySelector("span:last-child").textContent = "Foto anexada";
      atualizar();
    };
    img.src = ev.target.result;
  };
  leitor.readAsDataURL(arq);
});

/* ---------- Estado ---------- */
const val = id => (document.getElementById(id)?.value || "").trim();
const radio = n => document.querySelector(`input[name="${n}"]:checked`)?.value || "";
const precisaVendedor = !paramVendedor;

const OPC_TEXTO = ["razao_social","cnpj","ano_fundacao","subcategoria","complemento","bairro","cep","maps","decisor","fixo","email","instagram","seguidores","site","delivery","nota_valor","nota_qtd","abre","fecha","horario_obs","ticket","capacidade","pagamento_formas","observacoes"];
const OPC_LISTA = ["cargo","faixa","reservas","estacionamento","acessibilidade","pet","origem_lead","convidar_connect","potencial"];
const TOTAL_OPC = OPC_TEXTO.length + OPC_LISTA.length + 1; // +1 = dias da semana

function pendencias(){
  const p = [];
  if(precisaVendedor && !val("vendedor")) p.push("vendedor");
  if(!val("nome_fantasia")) p.push("nome_fantasia");
  if(!radio("categoria")) p.push("categoria");
  if(!val("endereco")) p.push("endereco");
  if(!radio("regiao")) p.push("regiao");
  if(val("whatsapp").replace(/\D/g,"").length < 10) p.push("whatsapp");
  if(!fotoBase64) p.push("foto");
  if(!document.getElementById("consentimento").checked) p.push("consentimento");
  return p;
}
const TOTAL_OBRIG = precisaVendedor ? 8 : 7;

function atualizar(){
  montarHorario();
  const faltam = pendencias().length;
  document.getElementById("statusBarra").style.width = Math.round((TOTAL_OBRIG-faltam)/TOTAL_OBRIG*100)+"%";
  document.getElementById("statusTexto").textContent =
    faltam === 0 ? "Tudo pronto para enviar" :
    faltam === 1 ? "Falta 1 campo obrigatório" : "Faltam "+faltam+" campos obrigatórios";
  const opc = OPC_TEXTO.filter(id=>val(id)).length + OPC_LISTA.filter(n=>radio(n)).length + (marcados("dias").length?1:0);
  document.getElementById("statusExtra").textContent = opc+" de "+TOTAL_OPC+" campos opcionais preenchidos";
}
document.getElementById("form").addEventListener("input", atualizar);
document.getElementById("form").addEventListener("change", atualizar);
atualizar();

/* ---------- Envio ---------- */
document.getElementById("enviar").addEventListener("click", async ()=>{
  document.querySelectorAll(".campo").forEach(c=>{ c.classList.remove("invalido"); c.removeAttribute("aria-invalid"); });
  const p = pendencias();
  if(p.length){
    p.forEach(k=>{ const el = document.querySelector(`.campo[data-req="${k}"]`); if(el){ el.classList.add("invalido"); el.setAttribute("aria-invalid","true"); }});
    document.querySelector(`.campo[data-req="${p[0]}"]`)?.scrollIntoView({behavior:"smooth",block:"center"});
    return;
  }

  const agora = new Date().toISOString();
  const abertos = marcados("dias");
  const fechados = marcados("diasFecha");
  const horarioTxt = document.getElementById("resumoTexto").textContent.trim();
  const obs = val("horario_obs");
  const nota = val("nota_valor") ? val("nota_valor") + (val("nota_qtd") ? " ("+val("nota_qtd")+")" : "") : "";

  const COLUNAS = {
    data_cadastro: agora,
    vendedor: paramVendedor || val("vendedor"),
    nome_fantasia: val("nome_fantasia"),
    razao_social: val("razao_social"),
    cnpj: val("cnpj"),
    categoria: radio("categoria"),
    subcategoria: val("subcategoria"),
    ano_fundacao: val("ano_fundacao"),
    endereco: val("endereco"),
    complemento: val("complemento"),
    bairro: val("bairro"),
    cep: val("cep"),
    regiao: radio("regiao"),
    link_maps: val("maps"),
    decisor: val("decisor"),
    cargo: radio("cargo"),
    whatsapp: val("whatsapp"),
    telefone_fixo: val("fixo"),
    email: val("email"),
    instagram: val("instagram"),
    seguidores: val("seguidores"),
    site: val("site"),
    delivery: val("delivery"),
    nota_google: nota,
    horario: abertos.length ? horarioTxt + (obs ? " · "+obs : "") : "",
    dia_fechamento: abertos.length || fechados.length ? (fechados.length ? nomearDias(fechados) : "Não fecha") : "",
    faixa_preco: radio("faixa"),
    ticket_medio: val("ticket"),
    formas_pagamento: val("pagamento_formas"),
    reservas: radio("reservas"),
    estacionamento: radio("estacionamento"),
    acessibilidade: radio("acessibilidade"),
    pet_friendly: radio("pet"),
    capacidade: val("capacidade"),
    origem_lead: radio("origem_lead"),
    pagamento_em_dia: "Sem contrato ativo",
    convidar_connect: radio("convidar_connect"),
    potencial_patrocinio: radio("potencial"),
    status_site: "Prospect",
    foto_fachada: fotoBase64,
    observacoes: val("observacoes"),
    consentimento: "Sim · " + agora,
    /* cru, para tratamento posterior */
    dias_abertos: abertos.map(i=>DIAS[i].l).join(", "),
    hora_abre: document.getElementById("horarioUniforme").checked ? val("abre") : "",
    hora_fecha: document.getElementById("horarioUniforme").checked ? val("fecha") : "",
    horario_por_dia: document.getElementById("horarioUniforme").checked ? "" : JSON.stringify(horarioPorDiaValores),
    feriado_atende: document.getElementById("feriadoAbre").checked ? "Sim" : "Não",
    feriado_abre: document.getElementById("feriadoDiferente").checked ? val("abreFeriado") : "",
    feriado_fecha: document.getElementById("feriadoDiferente").checked ? val("fechaFeriado") : ""
  };

  const btn = document.getElementById("enviar");
  btn.disabled = true; btn.textContent = "Enviando…";
  try{
    const resp = await fetch(ENDPOINT, {
      method: "POST",
      headers: {"Content-Type": "application/json"},
      body: JSON.stringify(COLUNAS)
    });
    const resultado = await resp.json().catch(()=>({ok:false}));
    if(!resp.ok || !resultado.ok){
      throw new Error(resultado.erro || "Falha ao salvar.");
    }
    document.getElementById("sucesso").classList.add("on");
    try{ localStorage.removeItem(CHAVE); }catch(_){}
  }catch(err){
    console.error(err);
    btn.textContent = "Não enviou · tocar de novo";
    btn.disabled = false;
  }
});

document.getElementById("novo").addEventListener("click", ()=>location.replace(location.href));

/* ----------------------------------------------------------------------------
   RASCUNHO AUTOMÁTICO
   Guarda o preenchimento a cada mudança e devolve se a aba fechar.
   ---------------------------------------------------------------------------- */
const CHAVE = "gcbr_ficha_rascunho";
function salvarRascunho(){
  const d = {};
  document.querySelectorAll("#form input, #form textarea").forEach(el=>{
    if(el.type === "file") return;
    d[el.id || el.name+"_"+el.value] = (el.type==="checkbox"||el.type==="radio") ? el.checked : el.value;
  });
  try{ localStorage.setItem(CHAVE, JSON.stringify(d)); }catch(_){}
}
function lerRascunho(){
  try{
    const d = JSON.parse(localStorage.getItem(CHAVE) || "null"); if(!d) return;
    document.querySelectorAll("#form input, #form textarea").forEach(el=>{
      if(el.type === "file") return;
      const k = el.id || el.name+"_"+el.value;
      if(!(k in d)) return;
      if(el.type==="checkbox"||el.type==="radio") el.checked = d[k]; else el.value = d[k];
    });
    atualizar();
  }catch(_){}
}
document.getElementById("form").addEventListener("input", salvarRascunho);
document.getElementById("form").addEventListener("change", salvarRascunho);
lerRascunho();
</script>
</body>
</html>