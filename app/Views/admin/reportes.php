<?php if (!empty($ok)): ?><div class="alert alert-success"><?= e($ok) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div>
    <h3 class="fw-extrabold mb-1 text-dark">Reportes y Análisis de Satisfacción</h3>
    <p class="text-muted small mb-0">Auditoría de encuestas, distribución porcentual y descargas.</p>
  </div>
  <div class="d-flex flex-wrap gap-2 align-items-center">
    <?php 
    $filterParams = 'anio=' . urlencode($anio ?? '') . '&mes=' . urlencode($mes ?? '') . '&encuesta=' . urlencode($encuesta ?? '') . '&fecha_inicio=' . urlencode($fecha_inicio ?? '') . '&fecha_fin=' . urlencode($fecha_fin ?? '');
    ?>
    <a class="btn btn-vuela fw-bold px-3 shadow-sm" href="<?= e(url('/exportar/formulario')) ?>?<?= $filterParams ?>" target="_blank">
      <i class="fa-solid fa-file-pdf me-1"></i>Ver Hoja de Reporte PDF / Imprimible
    </a>
    <?php $cfg = Store::read('config'); if (!empty($cfg['google_sheet_view_url'])): ?>
      <a class="btn btn-outline-success fw-bold" href="<?= e($cfg['google_sheet_view_url']) ?>" target="_blank" rel="noopener">
        <i class="fa-solid fa-table me-1"></i>Abrir Excel en línea
      </a>
    <?php endif; ?>
    <a class="btn btn-outline-secondary fw-bold" href="<?= e(url('/exportar/excel')) ?>?<?= $filterParams ?>"><i class="fa-solid fa-file-excel me-1"></i>Excel Local</a>
    <?php if ($sheets): ?>
      <form method="post" action="<?= e(url('/admin/sync')) ?>"><?= csrf_field() ?><button class="btn btn-outline-dark">Sincronizar Sheets</button></form>
    <?php endif; ?>
  </div>
</div>

<form class="card card-vuela p-3 mb-4" method="get">
  <div class="row g-3 align-items-end">
    <div class="col-md-3">
      <label class="form-label fw-bold text-dark small mb-1"><i class="fa-solid fa-list-check text-vuela me-1"></i>Clasificación / Encuesta</label>
      <select class="form-select form-select-sm" name="encuesta">
        <option value="">Todas las Clasificaciones</option>
        <?php foreach ($encuestas as $encName): ?>
          <option value="<?= e($encName) ?>" <?= ($encuesta ?? '') === $encName ? 'selected' : '' ?>><?= e($encName) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label fw-bold text-dark small mb-1">Año</label>
      <select class="form-select form-select-sm" name="anio">
        <option value="">Todos</option>
        <?php foreach ($anios as $a): ?><option <?= $anio === $a ? 'selected' : '' ?>><?= e($a) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label fw-bold text-dark small mb-1">Mes</label>
      <select class="form-select form-select-sm" name="mes">
        <option value="">Todos</option>
        <?php foreach ($meses as $m): ?><option <?= $mes === $m ? 'selected' : '' ?>><?= e($m) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label fw-bold text-dark small mb-1">Desde Fecha</label>
      <input type="date" class="form-control form-control-sm" name="fecha_inicio" value="<?= e($fecha_inicio ?? '') ?>">
    </div>
    <div class="col-md-2">
      <label class="form-label fw-bold text-dark small mb-1">Hasta Fecha</label>
      <input type="date" class="form-control form-control-sm" name="fecha_fin" value="<?= e($fecha_fin ?? '') ?>">
    </div>
    <div class="col-md-1">
      <button class="btn btn-vuela btn-sm w-100 fw-bold py-2"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
    </div>
  </div>
</form>

<div class="row g-4 mb-4">
  <div class="col-lg-6">
    <div class="card card-vuela p-4">
      <h5>Pastel por etiqueta (<?= (int)$stats['total'] ?> registros)</h5>
      <canvas id="pieEtq"></canvas>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card card-vuela p-4">
      <h5>Por mes</h5>
      <canvas id="pieMes"></canvas>
    </div>
  </div>
</div>

<div class="card card-vuela p-4">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
    <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-list-check me-2 text-vuela"></i>Tabla de Calificaciones Registradas</h5>
    
    <!-- Filtro de Búsqueda Rápida -->
    <div class="input-group-vuela style-filter-search" style="max-width: 300px;">
      <span class="input-icon"><i class="fa-solid fa-magnifying-glass"></i></span>
      <input type="text" id="reportTableSearch" class="form-control form-control-vuela form-control-sm" placeholder="Buscar por cliente, agente...">
    </div>
  </div>

  <!-- Botones Filtros por Nivel de Calificación -->
  <div class="d-flex flex-wrap gap-2 mb-3 align-items-center" id="scoreFilterChips">
    <span class="small fw-bold text-muted me-1"><i class="fa-solid fa-filter me-1"></i>Filtrar por resultado:</span>
    <button type="button" class="btn btn-sm btn-outline-secondary active btn-filter-chip" data-filter="all">Todos</button>
    <button type="button" class="btn btn-sm btn-outline-success btn-filter-chip" data-filter="5"><i class="fa-solid fa-face-grin-stars me-1"></i>Excelente (5)</button>
    <button type="button" class="btn btn-sm btn-outline-primary btn-filter-chip" data-filter="4"><i class="fa-solid fa-face-smile me-1"></i>Bueno (4)</button>
    <button type="button" class="btn btn-sm btn-outline-warning btn-filter-chip" data-filter="3"><i class="fa-solid fa-face-meh me-1"></i>Regular (3)</button>
    <button type="button" class="btn btn-sm btn-outline-danger btn-filter-chip" data-filter="malo"><i class="fa-solid fa-face-frown me-1"></i>Malo (1-2)</button>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle" id="reportesTable">
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Código</th>
          <th>Tipo</th>
          <th>Visita</th>
          <th>Score</th>
          <th>Etiqueta</th>
          <th>Pts</th>
          <th>Agente</th>
          <th class="text-end d-print-none">Acción</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach (array_reverse($stats['rows']) as $r): 
        $scoreVal = (int)($r['score'] ?? 0);
      ?>
        <tr class="report-row" data-score="<?= $scoreVal ?>">
          <td><span class="small text-muted fw-semibold"><?= e($r['fecha'] ?? '') ?></span></td>
          <td><strong class="text-dark">#<?= e($r['codigo'] ?? '') ?></strong></td>
          <td><span class="badge text-bg-light border"><?= e($r['tipo'] ?? '') ?></span></td>
          <td><span class="badge rounded-pill text-bg-secondary"><?= e((string)($r['visita_nro'] ?? '1')) ?></span></td>
          <td>
            <?php if ($scoreVal >= 5): ?>
              <span class="badge text-bg-success"><i class="fa-solid fa-star me-1"></i><?= $scoreVal ?>/<?= e($r['score_max'] ?? '5') ?></span>
            <?php elseif ($scoreVal >= 4): ?>
              <span class="badge text-bg-primary"><i class="fa-solid fa-thumbs-up me-1"></i><?= $scoreVal ?>/<?= e($r['score_max'] ?? '5') ?></span>
            <?php elseif ($scoreVal >= 3): ?>
              <span class="badge text-bg-warning"><i class="fa-solid fa-minus me-1"></i><?= $scoreVal ?>/<?= e($r['score_max'] ?? '5') ?></span>
            <?php else: ?>
              <span class="badge text-bg-danger"><i class="fa-solid fa-thumbs-down me-1"></i><?= $scoreVal ?>/<?= e($r['score_max'] ?? '5') ?></span>
            <?php endif; ?>
          </td>
          <td><span class="fw-semibold text-dark"><?= e($r['etiqueta'] ?? '') ?></span></td>
          <td><strong class="text-vuela"><?= (int)($r['puntos'] ?? 0) ?> pts</strong></td>
          <td><span class="fw-bold text-dark"><?= e($r['agente_nombre'] ?? '') ?></span></td>
          <td class="text-end d-print-none">
            <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar esta calificación con 2 confirmaciones"
                    onclick="abrirModalEliminarCalificacion(<?= e(json_encode($r, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)) ?>)">
              <i class="fa-solid fa-trash-can"></i>
            </button>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($stats['rows'])): ?>
        <tr><td colspan="9" class="text-center text-muted py-4">No hay registros de calificación disponibles.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL ELIMINAR CALIFICACIÓN CON 2 PASOS Y CONTRASEÑA DE ADMINISTRADOR -->
<div class="modal fade" id="modalEliminarCalificacion" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg style-assistant-modal" style="border-radius:20px; overflow:hidden;">
      <div class="modal-header bg-danger text-white p-3 px-4">
        <h5 class="modal-title fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i>Eliminar Registro de Calificación</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form method="post" action="<?= e(url('/admin/calificaciones/eliminar')) ?>" id="formEliminarCalificacion">
        <?= csrf_field() ?>
        <input type="hidden" name="id" id="delete_rating_id">

        <!-- PASO 1: Primera Confirmación del Registro a Eliminar -->
        <div id="deleteStep1" class="modal-body p-4">
          <div class="alert alert-warning border-warning d-flex align-items-center gap-3 mb-3">
            <i class="fa-solid fa-shield-cat fs-2 text-warning"></i>
            <div>
              <strong class="d-block text-dark">Primera Confirmación de Eliminación (1/2)</strong>
              <small class="text-muted">Revise los datos del registro que desea eliminar.</small>
            </div>
          </div>

          <div class="p-3 bg-light rounded-3 border mb-3">
            <div class="d-flex justify-content-between small text-muted mb-1">
              <span>Cliente / Código:</span>
              <strong class="text-dark" id="del_info_codigo">—</strong>
            </div>
            <div class="d-flex justify-content-between small text-muted mb-1">
              <span>Calificación / Pts:</span>
              <strong class="text-vuela" id="del_info_score">—</strong>
            </div>
            <div class="d-flex justify-content-between small text-muted">
              <span>Agente:</span>
              <strong class="text-dark" id="del_info_agente">—</strong>
            </div>
          </div>

          <p class="small text-muted mb-0">
            ¿Desea proceder al siguiente paso de confirmación para eliminar este registro de evaluación?
          </p>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-danger px-4 fw-bold" onclick="irAPaso2Eliminar()">
              Siguiente: Confirmación Final <i class="fa-solid fa-arrow-right me-1"></i>
            </button>
          </div>
        </div>

        <!-- PASO 2: Pregunta de Seguridad Final -->
        <div id="deleteStep2" class="modal-body p-4 d-none">
          <div class="alert alert-danger border-danger mb-3">
            <h6 class="fw-bold mb-1"><i class="fa-solid fa-radiation me-2"></i>PREGUNTA DE SEGURIDAD FINAL (2/2)</h6>
            <p class="small mb-0">¿Realmente está seguro de eliminar este registro permanentemente?</p>
          </div>

          <p class="small text-muted mb-3">
            Esta acción eliminará el registro de la base de datos local y de Google Sheets. Los puntos acumulados por el agente se recalcularán automáticamente.
          </p>

          <div class="form-check p-3 bg-danger-subtle rounded-3 border border-danger-subtle mb-4">
            <input class="form-check-input ms-0 me-2" type="checkbox" name="confirm_delete" value="1" id="confirm_delete_chk" required>
            <label class="form-check-label fw-bold text-danger small" for="confirm_delete_chk">
              Sí, confirmo que deseo eliminar definitivamente esta calificación del sistema.
            </label>
          </div>

          <div class="d-flex justify-content-between align-items-center">
            <button type="button" class="btn btn-outline-secondary px-3" onclick="volverAPaso1Eliminar()">
              <i class="fa-solid fa-arrow-left me-1"></i> Regresar
            </button>
            <button type="submit" class="btn btn-danger px-4 py-2 fw-bold d-flex align-items-center gap-2">
              <i class="fa-solid fa-trash-can"></i> Sí, Eliminar Definitivamente
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function abrirModalEliminarCalificacion(r) {
  document.getElementById('delete_rating_id').value = r.id || '';
  document.getElementById('del_info_codigo').textContent = r.codigo || '—';
  document.getElementById('del_info_score').textContent = (r.score || '0') + ' pts (' + (r.etiqueta || '') + ')';
  document.getElementById('del_info_agente').textContent = r.agente_nombre || '—';
  document.getElementById('confirm_delete_chk').checked = false;

  volverAPaso1Eliminar();

  var modal = new bootstrap.Modal(document.getElementById('modalEliminarCalificacion'));
  modal.show();
}

function irAPaso2Eliminar() {
  document.getElementById('deleteStep1').classList.add('d-none');
  document.getElementById('deleteStep2').classList.remove('d-none');
}

function volverAPaso1Eliminar() {
  document.getElementById('deleteStep2').classList.add('d-none');
  document.getElementById('deleteStep1').classList.remove('d-none');
}
</script>



<script>
  // Script de Filtrado Interactivo
  document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('reportTableSearch');
    var filterChips = document.querySelectorAll('.btn-filter-chip');
    var rows = document.querySelectorAll('.report-row');
    var activeFilter = 'all';

    function applyTableFilters() {
      var query = searchInput ? searchInput.value.toLowerCase().trim() : '';

      rows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        var score = parseInt(row.getAttribute('data-score'), 10) || 0;
        
        var matchesQuery = !query || text.indexOf(query) !== -1;
        var matchesScore = false;

        if (activeFilter === 'all') {
          matchesScore = true;
        } else if (activeFilter === '5' && score >= 5) {
          matchesScore = true;
        } else if (activeFilter === '4' && score === 4) {
          matchesScore = true;
        } else if (activeFilter === '3' && score === 3) {
          matchesScore = true;
        } else if (activeFilter === 'malo' && score <= 2) {
          matchesScore = true;
        }

        if (matchesQuery && matchesScore) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }

    if (searchInput) {
      searchInput.addEventListener('input', applyTableFilters);
    }

    filterChips.forEach(function(chip) {
      chip.addEventListener('click', function() {
        filterChips.forEach(function(c) { c.classList.remove('active'); });
        chip.classList.add('active');
        activeFilter = chip.getAttribute('data-filter');
        applyTableFilters();
      });
    });
  });
</script>
<script>
new Chart(document.getElementById('pieEtq'), {
  type: 'pie',
  data: {
    labels: <?= json_encode(array_keys($stats['por_etiqueta']), JSON_UNESCAPED_UNICODE) ?>,
    datasets: [{ data: <?= json_encode(array_values($stats['por_etiqueta'])) ?>, backgroundColor: ['#b42318','#dc6803','#ca8504','#4ca30d','#087443','#F15A22','#17a2b8'] }]
  }
});
new Chart(document.getElementById('pieMes'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode(array_keys($stats['por_mes']), JSON_UNESCAPED_UNICODE) ?>,
    datasets: [{ data: <?= json_encode(array_values($stats['por_mes'])) ?>, backgroundColor: ['#F15A22','#ffc107','#28a745','#6f42c1','#0dcaf0','#dc3545'] }]
  }
});
</script>
