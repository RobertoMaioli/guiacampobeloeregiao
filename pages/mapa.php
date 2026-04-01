<?php
require_once __DIR__ . '/../core/UserAuth.php';
require_once __DIR__ . '/../includes/icons.php';
require_once __DIR__ . '/../core/DB.php';
UserAuth::start();

$page_title = 'Mapa Interativo — Guia Campo Belo & Região';
$meta_desc  = 'Explore o mapa interativo do Guia Campo Belo. Encontre restaurantes, cafés e serviços perto de você.';
$canonical  = 'https://guiacampobeloeregiao.com.br/pages/mapa.php';

/* Categorias do banco */
$cats_db = DB::query('SELECT slug,label,icon FROM categorias WHERE ativo=1 ORDER BY ordem,label');
$categorias = array_merge([['slug'=>'todos','label'=>'Todos','icon'=>'trending-up']], $cats_db);

/* Lugares com coordenadas */
$lugares_db = DB::query(
    "SELECT l.id,l.slug,l.nome,l.cat_label,l.preco_nivel,l.preco_simbolo,
            l.endereco,l.telefone,l.rating,l.total_reviews,l.lat,l.lng,
            c.slug AS cat_slug, c.label AS cat_nome,
            COALESCE(f.url,l.foto_principal,'/assets/img/sem-imagem.png') AS img,
            CASE WHEN EXISTS(SELECT 1 FROM horarios h WHERE h.lugar_id=l.id
              AND h.dia_semana=DAYOFWEEK(NOW())-1 AND h.fechado=0
              AND(h.dia_todo=1 OR(h.hora_abre<=TIME(NOW()) AND (IF(h.hora_fecha='00:00:00','24:00:00',h.hora_fecha)>=TIME(NOW()))))
            ) THEN 1 ELSE 0 END AS aberto
     FROM lugares l JOIN categorias c ON c.id=l.categoria_id
     LEFT JOIN fotos f ON f.lugar_id=l.id AND f.principal=1
     WHERE l.ativo=1 AND l.lat IS NOT NULL AND l.lng IS NOT NULL
     ORDER BY l.destaque DESC,l.rating DESC");

/* Fallback mock se não houver dados */
if (empty($lugares_db)) {
    $lugares_db = [
        ['id'=>1,'slug'=>'osteria-moderna','nome'=>'Osteria Moderna','cat_label'=>'Italiana','cat_slug'=>'restaurantes','cat_nome'=>'Restaurantes','preco_nivel'=>'alto','preco_simbolo'=>'R$$$','endereco'=>'R. Lagoa Santa, 230','telefone'=>'(11) 3045-7892','rating'=>4.8,'total_reviews'=>247,'lat'=>-23.6185,'lng'=>-46.6675,'img'=>'/assets/img/sem-imagem.png','aberto'=>1],
        ['id'=>2,'slug'=>'nishiki-omakase','nome'=>'Nishiki Omakase','cat_label'=>'Japonesa','cat_slug'=>'japonesa','cat_nome'=>'Japonesa','preco_nivel'=>'luxo','preco_simbolo'=>'R$$$$','endereco'=>'Al. Arapanés, 450','telefone'=>'','rating'=>5.0,'total_reviews'=>183,'lat'=>-23.6155,'lng'=>-46.6698,'img'=>'https://images.unsplash.com/photo-1611270629569-8b357cb88da9?w=400&q=75','aberto'=>1],
        ['id'=>3,'slug'=>'bossa-cafe','nome'=>'Bossa Café & Bistrô','cat_label'=>'Café','cat_slug'=>'cafes','cat_nome'=>'Cafés','preco_nivel'=>'barato','preco_simbolo'=>'R$$','endereco'=>'R. Cap. A. Rosa, 09','telefone'=>'','rating'=>4.4,'total_reviews'=>94,'lat'=>-23.6200,'lng'=>-46.6640,'img'=>'https://images.unsplash.com/photo-1506354666786-959d6d497f1a?w=400&q=75','aberto'=>0],
    ];
}

$faixas = ['todos'=>'Todos os preços','barato'=>'Até R$ 60','medio'=>'R$ 60–R$ 120','alto'=>'R$ 120–R$ 200','luxo'=>'Acima de R$ 200'];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?php include __DIR__ . '/../includes/head.php'; ?>
    <!-- Leaflet -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <link rel="stylesheet" href="/assets/css/mapa.css?v1.2.4" />

</head>

<body>

    <?php include __DIR__ . '/../includes/search-modal.php'; ?>

    <div id="app">
        <?php include __DIR__ . '/../includes/header.php'; ?>

        <div id="main-area">

            <!-- Panel -->
            <div id="panel">
                <!-- Panel filtros -->
                <div class="panel-filters">

                    <!-- Search -->
                    <div class="panel-search">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--gcb-gold)" stroke-width="1.75" stroke-linecap="round">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text" id="map-search" placeholder="Buscar estabelecimento…" oninput="applyMapFilters()" autocomplete="off"/>
                        <button id="map-search-clear" onclick="clearMapSearch()" class="border-0 bg-transparent p-0 d-none" style="color:var(--gcb-warmgray);line-height:1;flex-shrink:0">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Selects lado a lado -->
                    <div class="filter-row">

                        <!-- Categoria -->
                        <div class="filter-select-wrap">
                            <label class="filter-label">Categoria</label>
                            <div class="select-box">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--gcb-gold)" stroke-width="1.75" stroke-linecap="round">
                                    <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                                    <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                                </svg>
                                <select id="map-cat-select" onchange="setMapCategorySelect(this.value)">
                                    <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat['slug']) ?>">
                                        <?= htmlspecialchars($cat['label']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <svg class="select-caret" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Preço -->
                        <div class="filter-select-wrap">
                            <label class="filter-label">Preço</label>
                            <div class="select-box">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--gcb-gold)" stroke-width="1.75" stroke-linecap="round">
                                    <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                </svg>
                                <select id="map-price-select" onchange="setMapPriceSelect(this.value)">
                                    <?php foreach ($faixas as $val=>$lbl): ?>
                                    <option value="<?= $val ?>"><?= htmlspecialchars($lbl) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <svg class="select-caret" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Results count -->
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom"
                    style="flex-shrink:0">
                    <p class="mb-0" style="font-size:12px;font-weight:600;color:var(--gcb-warmgray)">
                        <span id="map-count" class="fw-black"
                            style="font-size:13px;color:var(--gcb-graphite)"><?= count($lugares_db) ?></span> lugares
                    </p>
                    <button id="map-clear-btn" class="d-none border-0 bg-transparent p-0"
                        style="font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--gcb-gold)"
                        onclick="clearMapFilters()">Limpar</button>
                </div>

                <!-- Cards -->
                <div id="cards-list">
                    <?php foreach ($lugares_db as $l): ?>
                    <div class="map-card" id="card-<?= $l['id'] ?>" data-id="<?= $l['id'] ?>"
                        data-nome="<?= htmlspecialchars(strtolower($l['nome'])) ?>"
                        data-cat="<?= htmlspecialchars($l['cat_slug']??'') ?>"
                        data-preco="<?= htmlspecialchars($l['preco_nivel']??'') ?>"
                        onclick="selectPlace(<?= $l['id'] ?>)">
                        <div class="map-card-img"><img src="<?= htmlspecialchars($l['img']) ?>"
                                alt="<?= htmlspecialchars($l['nome']) ?>" loading="lazy" /></div>
                        <div class="flex-fill min-w-0">
                            <p
                                style="font-size:9px;font-weight:800;letter-spacing:.16em;text-transform:uppercase;color:var(--gcb-gold);margin-bottom:3px">
                                <?= htmlspecialchars($l['cat_label']??$l['cat_nome']) ?></p>
                            <p class="font-display fw-bold mb-1 text-truncate"
                                style="font-size:15px;color:var(--gcb-green-dark)"><?= htmlspecialchars($l['nome']) ?>
                            </p>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="stars"
                                    style="font-size:11px"><?= str_repeat('★',(int)round($l['rating'])) ?></span>
                                <span class="fw-bold"
                                    style="font-size:12px;color:var(--gcb-graphite)"><?= number_format($l['rating'],1) ?></span>
                                <span
                                    style="font-size:11px;color:var(--gcb-warmgray)">(<?= (int)$l['total_reviews'] ?>)</span>
                                <span style="font-size:10px;color:var(--gcb-warmgray)">&middot;</span>
                                <span class="fw-bold"
                                    style="font-size:12px;color:var(--gcb-green)"><?= htmlspecialchars($l['preco_simbolo']??'') ?></span>
                            </div>
                            <div class="d-flex align-items-center gap-1"
                                style="font-size:10.5px;font-weight:600;color:<?= $l['aberto']?'#10b981':'#ef4444' ?>">
                                <span class="rounded-circle"
                                    style="width:6px;height:6px;display:inline-block;background:<?= $l['aberto']?'#34d399':'#f87171' ?>"></span>
                                <?= $l['aberto']?'Aberto agora':'Fechado' ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div id="map-no-results" class="d-none flex-column align-items-center py-5 text-center px-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center mb-3"
                            style="width:52px;height:52px;background:var(--gcb-offwhite);color:var(--gcb-warmgray)">
                            <?= icon('search',24) ?></div>
                        <p class="font-display fw-bold mb-1" style="color:var(--gcb-green-dark)">Nenhum resultado</p>
                        <p style="font-size:13px;color:var(--gcb-warmgray);max-width:220px">Ajuste os filtros para ver
                            mais lugares.</p>
                        <button onclick="clearMapFilters()" class="btn-gold mt-2" style="font-size:11px">Limpar
                            filtros</button>
                    </div>
                </div>
            </div>

            <!-- Toggle panel -->
            <button id="toggle-panel" onclick="togglePanel()" aria-label="Mostrar/ocultar painel">
                <svg id="toggle-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.2" stroke-linecap="round">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
            </button>

            <!-- Map -->
            <div id="map"></div>

            <!-- Locate btn -->
            <button id="locate-btn" onclick="locateMe()" title="Usar minha localização">
                <?= icon('navigation',18) ?>
            </button>

        </div>

        <!-- Mobile tabs -->
        <div id="mobile-tabs">
            <div class="mobile-tab-bar">
                <button id="tab-lista" class="mobile-tab-btn active" onclick="mobileTab('lista')"><?= icon('list',16) ?>
                    Lista</button>
                <button id="tab-mapa" class="mobile-tab-btn" onclick="mobileTab('mapa')"><?= icon('map',16) ?>
                    Mapa</button>
            </div>
        </div>
    </div><!-- /#main-area -->
    </div><!-- /#app -->

    <script>
    const LUGARES = <?= json_encode($lugares_db, JSON_UNESCAPED_UNICODE) ?>;

    /* Map init */
    const map = L.map('map', {
        center: [-23.617, -46.668],
        zoom: 15,
        zoomControl: false
    });
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CARTO',
        subdomains: 'abcd',
        maxZoom: 19
    }).addTo(map);
    L.control.zoom({
        position: 'bottomright'
    }).addTo(map);

    /* Icon factory */
    function makeIcon(catSlug, active = false) {
        const paths = {
            restaurantes: '<path d="M3 2v20M19 2v4a4 4 0 0 1-4 4h-1v12M15 2v4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>',
            cafes: '<path d="M18 8h1a4 4 0 0 1 0 8h-1M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/><line x1="6" y1="1" x2="6" y2="4" stroke="currentColor" stroke-width="1.75"/><line x1="10" y1="1" x2="10" y2="4" stroke="currentColor" stroke-width="1.75"/>',
            default: '<path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z" stroke="currentColor" stroke-width="1.75"/><circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="1.75"/>',
        };
        const p = paths[catSlug] || paths.default;
        const svg = `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">${p}</svg>`;
        return L.divIcon({
            html: `<div class="marker-pin ${active?'is-active':''}"><div class="marker-pin-inner">${svg}</div></div>`,
            className: '',
            iconSize: [38, 44],
            iconAnchor: [19, 44],
            popupAnchor: [0, -46]
        });
    }

    function makePopup(p) {
        const rating = parseFloat(p.rating) || 0; // ← converte string → number
        const totalReviews = parseInt(p.total_reviews) || 0;
        const stars = '★'.repeat(Math.round(rating)) + '☆'.repeat(5 - Math.round(rating));

        return `<img class="popup-img" src="${p.img}" alt="${p.nome}" loading="lazy"/>
  <div class="popup-body">
    <div class="popup-cat">${p.cat_label || p.cat_nome}</div>
    <div class="popup-name">${p.nome}</div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
      <div><span style="color:#c9aa6b">${stars}</span> <strong style="font-size:13px">${rating.toFixed(1)}</strong> <span style="font-size:11px;color:#8b8589">(${totalReviews})</span></div>
      <span style="font-size:12px;font-weight:700;color:#3d4733">${p.preco_simbolo || ''}</span>
    </div>
    <div style="display:flex;align-items:center;gap:5px;font-size:11.5px;color:#8b8589;margin-bottom:12px">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#c9aa6b" stroke-width="1.75"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      ${p.endereco || ''}
    </div>
    <div style="display:flex;align-items:center;gap:5px;font-size:11px;font-weight:600;margin-bottom:12px;color:${p.aberto ? '#10b981' : '#ef4444'}">
      <span style="width:7px;height:7px;border-radius:50%;background:${p.aberto ? '#34d399' : '#f87171'};display:inline-block"></span>
      ${p.aberto ? 'Aberto agora' : 'Fechado'}
    </div>
    <a href="/pages/lugar.php?slug=${p.slug}" class="popup-btn text-white">Ver estabelecimento →</a>
  </div>`;
    }

    const markers = {};
    let activeId = null;

    LUGARES.forEach(p => {
        const m = L.marker([p.lat, p.lng], {
            icon: makeIcon(p.cat_slug)
        }).addTo(map);
        const popup = L.popup({
                maxWidth: 260,
                minWidth: 260,
                className: 'gcb-popup'
            })
            .setContent(makePopup(p));
        m.bindPopup(popup);
        m.on('click', () => selectPlace(p.id));
        markers[p.id] = {
            marker: m,
            data: p
        };
    });

    function selectPlace(id) {
        if (activeId !== null) {
            const prev = markers[activeId];
            if (prev) {
                prev.marker.setIcon(makeIcon(prev.data.cat_slug, false));
            }
            document.getElementById('card-' + activeId)?.classList.remove('active');
        }
        activeId = id;
        const cur = markers[id];
        if (!cur) return;
        cur.marker.setIcon(makeIcon(cur.data.cat_slug, true));
        cur.marker.openPopup();
        map.setView([cur.data.lat, cur.data.lng], 16, {
            animate: true,
            duration: .5
        });
        const card = document.getElementById('card-' + id);
        if (card) {
            card.classList.add('active');
            card.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }
        if (window.innerWidth <= 768) mobileTab('mapa');
    }
    map.on('popupclose', (e) => {
        // Só reseta se o popup fechado for do marker ativo
        if (activeId !== null) {
            const cur = markers[activeId];
            if (cur && cur.marker.getPopup() === e.popup) {
                cur.marker.setIcon(makeIcon(cur.data.cat_slug, false));
                document.getElementById('card-' + activeId)?.classList.remove('active');
                activeId = null;
            }
        }
    });

    /* Filters */
    let activeCat = 'todos',
        activePrice = 'todos';

    function setMapCategorySelect(val) {
        activeCat = val;
        applyMapFilters();
    }

    function setMapPriceSelect(val) {
        activePrice = val;
        applyMapFilters();
    }

    // Mantém compatibilidade com chips se ainda existirem
    function setMapCategory(el, val) { activeCat = val; applyMapFilters(); }
    function setMapPrice(el, val)    { activePrice = val; applyMapFilters(); }

    function applyMapFilters() {
        const q = (document.getElementById('map-search').value || '').toLowerCase().trim();
        document.getElementById('map-search-clear').classList.toggle('d-none', !q);
        const hasFilter = activeCat !== 'todos' || activePrice !== 'todos' || q;
        document.getElementById('map-clear-btn').classList.toggle('d-none', !hasFilter);
        let visible = 0;
        LUGARES.forEach(p => {
            const nameOk = !q || (p.nome || '').toLowerCase().includes(q);
            const catOk = activeCat === 'todos' || p.cat_slug === activeCat;
            const priceOk = activePrice === 'todos' || p.preco_nivel === activePrice;
            const show = nameOk && catOk && priceOk;
            document.getElementById('card-' + p.id).style.display = show ? '' : 'none';
            if (show) {
                markers[p.id]?.marker.addTo(map);
                visible++;
            } else {
                markers[p.id]?.marker.remove();
            }
        });
        document.getElementById('map-count').textContent = visible;
        document.getElementById('map-no-results').style.display = visible === 0 ? 'flex' : 'none';
    }

    function clearMapSearch() {
        document.getElementById('map-search').value = '';
        applyMapFilters();
    }

    function clearMapFilters() {
        activeCat = 'todos';
        activePrice = 'todos';
        document.getElementById('map-search').value = '';
        const cs = document.getElementById('map-cat-select');
        const ps = document.getElementById('map-price-select');
        if (cs) cs.value = 'todos';
        if (ps) ps.value = 'todos';
        applyMapFilters();
    }

    /* Panel toggle */
    let panelOpen = true;

    function togglePanel() {
        panelOpen = !panelOpen;
        document.getElementById('panel').classList.toggle('collapsed', !panelOpen);
        const btn = document.getElementById('toggle-panel');
        btn.classList.toggle('closed', !panelOpen);
        document.getElementById('toggle-icon').innerHTML = panelOpen ? '<polyline points="15 18 9 12 15 6"/>' :
            '<polyline points="9 18 15 12 9 6"/>';
        setTimeout(() => map.invalidateSize(), 360);
    }

    /* Locate */
    let userMarker = null;

    function locateMe() {
        if (!navigator.geolocation) {
            alert('Geolocalização não suportada.');
            return;
        }
        navigator.geolocation.getCurrentPosition(pos => {
            const {
                latitude: lat,
                longitude: lng
            } = pos.coords;
            if (userMarker) userMarker.remove();
            userMarker = L.circleMarker([lat, lng], {
                radius: 10,
                color: '#c9aa6b',
                fillColor: '#c9aa6b',
                fillOpacity: .35,
                weight: 2.5
            }).addTo(map).bindPopup('<strong>Você está aqui</strong>');
            map.setView([lat, lng], 16, {
                animate: true
            });
        }, () => alert('Não foi possível obter sua localização.'));
    }

    /* Mobile tabs */
    function mobileTab(tab) {
        const panel = document.getElementById('panel');
        const mapEl = document.getElementById('map');
        const btnL = document.getElementById('tab-lista');
        const btnM = document.getElementById('tab-mapa');
        if (tab === 'lista') {
            panel.classList.add('mobile-visible');
            mapEl.classList.remove('mobile-visible');
            btnL.classList.add('active');
            btnM.classList.remove('active');
        } else {
            panel.classList.remove('mobile-visible');
            mapEl.classList.add('mobile-visible');
            btnM.classList.add('active');
            btnL.classList.remove('active');
            setTimeout(() => map.invalidateSize(), 50);
        }
    }
    if (window.innerWidth <= 768) document.getElementById('panel').classList.add('mobile-visible');
    </script>
</body>

</html>