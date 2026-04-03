<?php
/**
 * includes/head.php
 * Head centralizado — Bootstrap 5.3 + identidade Guia Campo Belo
 * Uso: definir $page_title, $meta_desc, $canonical antes de incluir
 */
$page_title = $page_title ?? 'Guia Campo Belo & Região';
$meta_desc  = $meta_desc  ?? 'A curadoria definitiva do Campo Belo e região. Restaurantes, serviços e experiências para quem valoriza tempo e qualidade.';
$canonical  = $canonical  ?? 'https://guiacampobeloeregiao.com.br/';
?>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="description" content="<?= htmlspecialchars($meta_desc) ?>"/>
<title><?= htmlspecialchars($page_title) ?></title>
<link rel="canonical" href="<?= htmlspecialchars($canonical) ?>"/>
<meta property="og:title"       content="<?= htmlspecialchars($page_title) ?>"/>
<meta property="og:description" content="<?= htmlspecialchars($meta_desc) ?>"/>
<meta property="og:type"        content="website"/>
<meta property="og:url"         content="<?= htmlspecialchars($canonical) ?>"/>
<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet"/>
<!-- Bootstrap 5.3 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css"/>
<!-- Guia Campo Belo CSS -->
<link rel="stylesheet" href="/assets/css/gcb.css?v1.3.3"/>
<link rel="stylesheet" href="/assets/css/header-user.css">
<link rel="icon" type="image/png" href="/assets/img/logo.png">