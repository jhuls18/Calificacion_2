<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h2 class="fw-extrabold mb-1" style="color:var(--ink)">
      <i class="fa-solid fa-trophy text-warning me-2"></i>Ranking de Agentes en Vivo
    </h2>
    <p class="text-muted mb-0">Tablero de puntuación acumulada en tiempo real según la satisfacción del cliente.</p>
  </div>

  <div class="d-flex align-items-center gap-2">
    <span class="badge text-bg-danger rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
      <span class="spinner-grow spinner-grow-sm text-light" role="status"></span>
      <span class="fw-bold">🔴 EN VIVO</span>
    </span>
    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="cargarRankingLive()">
      <i class="fa-solid fa-arrows-rotate me-1" id="iconRefresh"></i> Actualizar
    </button>
  </div>
</div>

<!-- Filtros de Vista: Todos vs Solo Mi Usuario vs Seleccionar Agente -->
<div class="card card-vuela p-3 mb-4">
  <div class="row align-items-center g-3">
    <div class="col-md-5">
      <label class="form-label fw-bold text-dark mb-1"><i class="fa-solid fa-filter me-1 text-vuela"></i>Modo de Visualización</label>
      <select class="form-select fw-semibold" id="filtroModo" onchange="cambiarModoVista()">
        <option value="todos">🏆 Todos los agentes (Tabla Ranked General)</option>
        <option value="mi_usuario" selected>👤 Solo mi usuario (@<?= e($user['usuario']) ?>)</option>
        <optgroup label="Seleccionar un agente específico">
          <?php foreach ($ranking as $a): ?>
            <option value="<?= e($a['agente_usuario']) ?>">
              👤 <?= e($a['agente_nombre']) ?> (@<?= e($a['agente_usuario']) ?>)
            </option>
          <?php endforeach; ?>
        </optgroup>
      </select>
    </div>

    <div class="col-md-4">
      <label class="form-label fw-bold text-dark mb-1"><i class="fa-solid fa-magnifying-glass me-1 text-primary"></i>Buscar Agente</label>
      <input type="text" class="form-control" id="inputBuscarAgente" placeholder="Filtrar por nombre o usuario..." oninput="filtrarTablaLive()">
    </div>

    <div class="col-md-3 text-md-end">
      <div class="form-check form-switch d-inline-block mt-4">
        <input class="form-check-input" type="checkbox" id="switchAutoRefresh" checked onchange="toggleAutoRefresh()">
        <label class="form-check-label small fw-bold text-dark" for="switchAutoRefresh">Auto-refresco (5s)</label>
      </div>
    </div>
  </div>
</div>

<!-- FICHA / CARD DE RANKING DE SOLO UN USUARIO (MODO USUARIO INDIVIDUAL) -->
<div id="seccionUsuarioIndividual" class="mb-4">
  <?php
  // Determinar usuario actual o seleccionado
  $currentUser = $user['usuario'] ?? '';
  $userRank = null;
  $userPos = 0;
  foreach ($ranking as $idx => $item) {
    if (mb_strtolower((string)($item['agente_usuario'] ?? '')) === mb_strtolower((string)$currentUser)) {
      $userRank = $item;
      $userPos = $idx + 1;
      break;
    }
  }

  // Filtrar historial de calificaciones para este usuario
  $userRatings = [];
  if (isset($allRatings) && is_array($allRatings)) {
    foreach ($allRatings as $r) {
      if (mb_strtolower((string)($r['agente_usuario'] ?? '')) === mb_strtolower((string)$currentUser)) {
        $userRatings[] = $r;
      }
    }
  }
  ?>

  <div class="card card-vuela p-4 bg-gradient-subtle border-orange-subtle shadow">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
      <div class="d-flex align-items-center gap-3">
        <div class="position-relative">
          <?= user_avatar($userRank ?? $user, 64) ?>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-warning text-dark border border-white p-2" title="Ranking Position">

            #<span id="card_user_pos"><?= $userPos > 0 ? $userPos : '—' ?></span>
          </span>
        </div>
        <div>
          <h3 class="fw-extrabold text-dark mb-0" id="card_user_nombre"><?= e($userRank['agente_nombre'] ?? $user['nombre']) ?></h3>
          <div class="d-flex align-items-center gap-2 mt-1">
            <span class="badge text-bg-dark" id="card_user_usuario">@<?= e($userRank['agente_usuario'] ?? $user['usuario']) ?></span>
            <span class="badge badge-rol" id="card_user_rol"><?= e($userRank['rol'] ?? $user['rol']) ?></span>
            <span class="badge text-bg-warning text-dark fw-bold" id="card_user_medalla">
              <?php if ($userPos === 1): ?>🏆 Líder del Ranking
              <?php elseif ($userPos === 2): ?>🥈 2do Lugar
              <?php elseif ($userPos === 3): ?>🥉 3er Lugar
              <?php else: ?>⭐ Agente Activo<?php endif; ?>
            </span>
          </div>
        </div>
      </div>

      <div>
        <button class="btn btn-sm btn-outline-vuela rounded-pill px-3" onclick="verTodosLosAgentes()">
          <i class="fa-solid fa-users me-1"></i> Ver Tabla Global de Agentes
        </button>
      </div>
    </div>

    <!-- Indicadores individuales -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="p-3 bg-white border rounded-4 shadow-sm text-center">
          <span class="text-muted small fw-semibold d-block">PUNTOS ACUMULADOS</span>
          <h2 class="fw-extrabold text-vuela my-1" id="card_user_puntos"><?= (int)($userRank['puntos'] ?? 0) ?></h2>
          <small class="text-muted">en la tabla ranked</small>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="p-3 bg-white border rounded-4 shadow-sm text-center">
          <span class="text-muted small fw-semibold d-block">ATENCIONES EVALUADAS</span>
          <h2 class="fw-extrabold text-dark my-1" id="card_user_total"><?= (int)($userRank['total'] ?? 0) ?></h2>
          <small class="text-muted">calificaciones recibidas</small>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="p-3 bg-white border rounded-4 shadow-sm text-center">
          <span class="text-muted small fw-semibold d-block">POSITIVAS vs NEGATIVAS</span>
          <h2 class="fw-extrabold my-1">
            <span class="text-success" id="card_user_positivos">+<?= (int)($userRank['positivos'] ?? 0) ?></span>
            <span class="text-muted fs-4">/</span>
            <span class="text-danger" id="card_user_negativos">−<?= (int)($userRank['negativos'] ?? 0) ?></span>
          </h2>
          <small class="text-muted">balance de votos</small>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="p-3 bg-white border rounded-4 shadow-sm text-center">
          <span class="text-muted small fw-semibold d-block">PROMEDIO SCORE</span>
          <h2 class="fw-extrabold text-warning my-1" id="card_user_promedio">
            <?= e((string)($userRank['promedio'] ?? '0.00')) ?> <small class="fs-6 text-warning">⭐</small>
          </h2>
          <small class="text-muted">calificación promedio</small>
        </div>
      </div>
    </div>

    <!-- Historial del Usuario Seleccionado -->
    <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-clock-rotate-left me-2 text-vuela"></i>Últimas Calificaciones Recibidas por este Usuario</h5>
    <div class="table-responsive bg-white rounded-4 border p-2">
      <table class="table align-middle table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>Fecha</th>
            <th>Cliente / Código</th>
            <th>Tipo</th>
            <th>Calificación</th>
            <th>Puntos</th>
          </tr>
        </thead>
        <tbody id="tablaHistorialUsuario">
        <?php foreach (array_slice(array_reverse($userRatings), 0, 10) as $r): ?>
          <tr>
            <td class="small fw-semibold"><?= e($r['fecha'] ?? '') ?></td>
            <td><code><?= e($r['codigo'] ?? '') ?></code></td>
            <td><span class="badge text-bg-light border"><?= e($r['tipo'] ?? 'cliente') ?></span></td>
            <td>
              <span class="badge text-bg-dark px-2 py-1"><?= e($r['etiqueta'] ?? 'Atención') ?></span>
              <small class="text-muted ms-1">(<?= (int)($r['score'] ?? 0) ?>/<?= (int)($r['score_max'] ?? 5) ?>)</small>
            </td>
            <td>
              <?php if ((int)($r['puntos'] ?? 0) >= 0): ?>
                <span class="badge text-bg-success fw-bold">+<?= (int)($r['puntos'] ?? 0) ?> pts</span>
              <?php else: ?>
                <span class="badge text-bg-danger fw-bold"><?= (int)($r['puntos'] ?? 0) ?> pts</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$userRatings): ?>
          <tr><td colspan="5" class="text-center text-muted py-3">Este usuario aún no tiene atenciones registradas.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- TABLA GENERAL DE RANKING (TABLA RANKED) -->
<div class="card card-vuela p-4" id="seccionTablaGlobal">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h4 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-list-ol me-2 text-vuela"></i>Tabla General Ranked de Agentes</h4>
      <small class="text-muted">Ordenado de mayor a menor por puntos acumulados</small>
    </div>
    <span class="badge text-bg-primary rounded-pill px-3 py-2" id="badgeTotalAgentes"><?= count($ranking) ?> Agentes en Tabla</span>
  </div>

  <div class="table-responsive">
    <table class="table align-middle table-hover" id="tablaRankingLive">
      <thead class="table-light">
        <tr>
          <th style="width:60px">#</th>
          <th>Agente</th>
          <th>Rol</th>
          <th class="text-center">Atenciones</th>
          <th class="text-center">Promedio ⭐</th>
          <th class="text-center">Puntos Ranked</th>
          <th class="text-success text-center">+ Positivos</th>
          <th class="text-danger text-center">− Negativos</th>
          <th class="text-end">Acción</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($ranking as $i => $a): ?>
        <tr class="fila-agente" data-usuario="<?= e($a['agente_usuario']) ?>" data-nombre="<?= e(mb_strtolower($a['agente_nombre'])) ?>">
          <td class="fw-bold">
            <?php if ($i === 0): ?>
              <span class="badge text-bg-warning rounded-circle p-2 fs-6" title="1er Lugar - Líder"><i class="fa-solid fa-crown"></i></span>
            <?php elseif ($i === 1): ?>
              <span class="badge text-bg-secondary rounded-circle p-2 fs-6" title="2do Lugar"><i class="fa-solid fa-medal"></i></span>
            <?php elseif ($i === 2): ?>
              <span class="badge text-bg-danger rounded-circle p-2 fs-6" title="3er Lugar" style="background-color:#cd7f32 !important;"><i class="fa-solid fa-award"></i></span>
            <?php else: ?>
              <span class="text-muted">#<?= $i + 1 ?></span>
            <?php endif; ?>
          </td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="bg-vuela-subtle text-vuela rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:36px; height:36px;">
                <?= strtoupper(substr((string)$a['agente_nombre'], 0, 2)) ?>
              </div>
              <div>
                <strong class="text-dark d-block mb-0"><?= e($a['agente_nombre']) ?></strong>
                <small class="text-muted">@<?= e($a['agente_usuario']) ?></small>
              </div>
            </div>
          </td>
          <td><span class="badge badge-rol"><?= e($a['rol']) ?></span></td>
          <td class="text-center fw-semibold"><?= (int)$a['total'] ?></td>
          <td class="text-center fw-bold text-warning"><?= e((string)$a['promedio']) ?> ⭐</td>
          <td class="text-center fw-extrabold text-vuela fs-5"><?= (int)$a['puntos'] ?></td>
          <td class="text-center text-success fw-bold">+<?= (int)$a['positivos'] ?></td>
          <td class="text-center text-danger fw-bold">−<?= (int)$a['negativos'] ?></td>
          <td class="text-end">
            <button type="button" class="btn btn-sm btn-outline-vuela rounded-pill px-3" onclick="verSoloUsuario('<?= e($a['agente_usuario']) ?>')">
              <i class="fa-solid fa-user me-1"></i> Ver Rendimiento
            </button>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$ranking): ?>
        <tr><td colspan="9" class="text-center text-muted py-4">Aún no hay datos registrados en el ranking.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
var autoRefreshTimer = null;
var RAW_RANKING_DATA = <?= json_encode($ranking, JSON_UNESCAPED_UNICODE) ?>;
var ALL_RATINGS_DATA = <?= json_encode($allRatings ?? [], JSON_UNESCAPED_UNICODE) ?>;
var CURRENT_USER_LOGGED = <?= json_encode($user['usuario'] ?? '') ?>;

function cambiarModoVista() {
  var modo = document.getElementById('filtroModo').value;
  var secInd = document.getElementById('seccionUsuarioIndividual');
  var secGlob = document.getElementById('seccionTablaGlobal');

  if (modo === 'todos') {
    secInd.style.display = 'none';
    secGlob.style.display = 'block';
  } else {
    secInd.style.display = 'block';
    secGlob.style.display = 'block';
    var targetUser = modo === 'mi_usuario' ? CURRENT_USER_LOGGED : modo;
    renderFichaUsuario(targetUser);
  }
}

function verSoloUsuario(usuario) {
  var select = document.getElementById('filtroModo');
  select.value = usuario;
  cambiarModoVista();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function verTodosLosAgentes() {
  var select = document.getElementById('filtroModo');
  select.value = 'todos';
  cambiarModoVista();
}

function renderFichaUsuario(usuario) {
  usuario = (usuario || '').toLowerCase();
  var uRank = null;
  var pos = 0;

  for (var i = 0; i < RAW_RANKING_DATA.length; i++) {
    if ((RAW_RANKING_DATA[i].agente_usuario || '').toLowerCase() === usuario) {
      uRank = RAW_RANKING_DATA[i];
      pos = i + 1;
      break;
    }
  }

  if (!uRank) {
    uRank = {
      agente_nombre: usuario,
      agente_usuario: usuario,
      rol: 'Agente',
      puntos: 0,
      total: 0,
      positivos: 0,
      negativos: 0,
      promedio: 0
    };
  }

  document.getElementById('card_user_pos').textContent = pos > 0 ? pos : '—';
  document.getElementById('card_user_nombre').textContent = uRank.agente_nombre || usuario;
  document.getElementById('card_user_usuario').textContent = '@' + (uRank.agente_usuario || usuario);
  document.getElementById('card_user_rol').textContent = uRank.rol || 'Agente';
  document.getElementById('card_user_puntos').textContent = uRank.puntos || 0;
  document.getElementById('card_user_total').textContent = uRank.total || 0;
  document.getElementById('card_user_positivos').textContent = '+' + (uRank.positivos || 0);
  document.getElementById('card_user_negativos').textContent = '−' + (uRank.negativos || 0);
  document.getElementById('card_user_promedio').innerHTML = (uRank.promedio || 0) + ' <small class="fs-6 text-warning">⭐</small>';

  var medalla = '⭐ Agente Activo';
  if (pos === 1) medalla = '🏆 Líder del Ranking';
  else if (pos === 2) medalla = '🥈 2do Lugar';
  else if (pos === 3) medalla = '🥉 3er Lugar';
  document.getElementById('card_user_medalla').textContent = medalla;

  // Filtrar Historial
  var tbody = document.getElementById('tablaHistorialUsuario');
  tbody.innerHTML = '';
  var userRatings = ALL_RATINGS_DATA.filter(function(r) {
    return (r.agente_usuario || '').toLowerCase() === usuario;
  }).reverse();

  if (userRatings.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Este usuario aún no tiene atenciones registradas.</td></tr>';
  } else {
    userRatings.slice(0, 10).forEach(function(r) {
      var tr = document.createElement('tr');
      var ptsClass = (parseInt(r.puntos) || 0) >= 0 ? 'text-bg-success' : 'text-bg-danger';
      var ptsSign = (parseInt(r.puntos) || 0) >= 0 ? '+' : '';

      tr.innerHTML = '<td class="small fw-semibold">' + (r.fecha || '') + '</td>' +
        '<td><code>' + (r.codigo || '') + '</code></td>' +
        '<td><span class="badge text-bg-light border">' + (r.tipo || 'cliente') + '</span></td>' +
        '<td><span class="badge text-bg-dark px-2 py-1">' + (r.etiqueta || 'Atención') + '</span> <small class="text-muted">(' + (r.score||0) + '/' + (r.score_max||5) + ')</small></td>' +
        '<td><span class="badge ' + ptsClass + ' fw-bold">' + ptsSign + (r.puntos || 0) + ' pts</span></td>';
      tbody.appendChild(tr);
    });
  }
}

function filtrarTablaLive() {
  var q = (document.getElementById('inputBuscarAgente').value || '').toLowerCase();
  var filas = document.querySelectorAll('#tablaRankingLive .fila-agente');
  filas.forEach(function(f) {
    var u = f.getAttribute('data-usuario') || '';
    var n = f.getAttribute('data-nombre') || '';
    if (u.toLowerCase().includes(q) || n.toLowerCase().includes(q)) {
      f.style.display = '';
    } else {
      f.style.display = 'none';
    }
  });
}

function cargarRankingLive() {
  var icon = document.getElementById('iconRefresh');
  if (icon) icon.classList.add('fa-spin');

  fetch('<?= e(url('/api/ranking')) ?>')
    .then(function(res) { return res.json(); })
    .then(function(data) {
      if (data && data.ok) {
        RAW_RANKING_DATA = data.ranking || [];
        actualizarTablaDOM(RAW_RANKING_DATA);
        var modo = document.getElementById('filtroModo').value;
        if (modo !== 'todos') {
          var targetUser = modo === 'mi_usuario' ? CURRENT_USER_LOGGED : modo;
          renderFichaUsuario(targetUser);
        }
      }
    })
    .catch(function(err) { console.error('Error actualizando ranking en vivo:', err); })
    .finally(function() {
      if (icon) icon.classList.remove('fa-spin');
    });
}

function actualizarTablaDOM(ranking) {
  var tbody = document.querySelector('#tablaRankingLive tbody');
  if (!tbody) return;

  if (ranking.length === 0) {
    tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4">Aún no hay datos registrados en el ranking.</td></tr>';
    return;
  }

  tbody.innerHTML = '';
  ranking.forEach(function(a, i) {
    var tr = document.createElement('tr');
    tr.className = 'fila-agente';
    tr.setAttribute('data-usuario', a.agente_usuario || '');
    tr.setAttribute('data-nombre', (a.agente_nombre || '').toLowerCase());

    var badgePos = '<span class="text-muted">#' + (i + 1) + '</span>';
    if (i === 0) badgePos = '<span class="badge text-bg-warning rounded-circle p-2 fs-6" title="1er Lugar - Líder"><i class="fa-solid fa-crown"></i></span>';
    else if (i === 1) badgePos = '<span class="badge text-bg-secondary rounded-circle p-2 fs-6" title="2do Lugar"><i class="fa-solid fa-medal"></i></span>';
    else if (i === 2) badgePos = '<span class="badge text-bg-danger rounded-circle p-2 fs-6" title="3er Lugar" style="background-color:#cd7f32 !important;"><i class="fa-solid fa-award"></i></span>';

    var iniciales = ((a.agente_nombre || '').substring(0, 2)).toUpperCase();

    tr.innerHTML = '<td class="fw-bold">' + badgePos + '</td>' +
      '<td><div class="d-flex align-items-center gap-2">' +
      '<div class="bg-vuela-subtle text-vuela rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:36px; height:36px;">' + iniciales + '</div>' +
      '<div><strong class="text-dark d-block mb-0">' + (a.agente_nombre || '') + '</strong><small class="text-muted">@' + (a.agente_usuario || '') + '</small></div>' +
      '</div></td>' +
      '<td><span class="badge badge-rol">' + (a.rol || '') + '</span></td>' +
      '<td class="text-center fw-semibold">' + (a.total || 0) + '</td>' +
      '<td class="text-center fw-bold text-warning">' + (a.promedio || 0) + ' ⭐</td>' +
      '<td class="text-center fw-extrabold text-vuela fs-5">' + (a.puntos || 0) + '</td>' +
      '<td class="text-center text-success fw-bold">+' + (a.positivos || 0) + '</td>' +
      '<td class="text-center text-danger fw-bold">−' + (a.negativos || 0) + '</td>' +
      '<td class="text-end"><button type="button" class="btn btn-sm btn-outline-vuela rounded-pill px-3" onclick="verSoloUsuario(\'' + (a.agente_usuario || '') + '\')"><i class="fa-solid fa-user me-1"></i> Ver Rendimiento</button></td>';

    tbody.appendChild(tr);
  });

  filtrarTablaLive();
}

function toggleAutoRefresh() {
  var chk = document.getElementById('switchAutoRefresh');
  if (chk.checked) {
    if (!autoRefreshTimer) {
      autoRefreshTimer = setInterval(cargarRankingLive, 5000);
    }
  } else {
    if (autoRefreshTimer) {
      clearInterval(autoRefreshTimer);
      autoRefreshTimer = null;
    }
  }
}

// Iniciar en modo solo mi usuario por defecto al cargar
document.addEventListener('DOMContentLoaded', function() {
  cambiarModoVista();
  toggleAutoRefresh();
});
</script>
