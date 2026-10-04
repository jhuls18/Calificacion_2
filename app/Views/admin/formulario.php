<style>
@media print {
  @page {
    size: A4 portrait;
    margin: 10mm 10mm 10mm 10mm;
  }
  
  html, body {
    background: #ffffff !important;
    color: #000000 !important;
    font-size: 10pt !important;
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
  }

  .navbar, .navbar-vuela,
  .btn-floating-assistant, .btn-floating-online,
  .offcanvas, .modal, .bg-shapes, .shape,
  .d-print-none, .btn, button {
    display: none !important;
  }

  .page-wrap, main, container {
    padding: 0 !important;
    margin: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
  }

  .card, .card-vuela {
    box-shadow: none !important;
    border: none !important;
    padding: 0 !important;
    margin: 0 !important;
    background: #ffffff !important;
  }

  .table-responsive {
    overflow: visible !important;
    display: block !important;
    border: none !important;
    box-shadow: none !important;
  }

  table {
    width: 100% !important;
    border-collapse: collapse !important;
    font-size: 9pt !important;
  }

  th, td {
    border: 1px solid #cbd5e1 !important;
    padding: 5px 7px !important;
  }

  canvas {
    max-width: 100% !important;
    max-height: 200px !important;
  }

  .print-signature-box {
    page-break-inside: avoid !important;
    break-inside: avoid !important;
    margin-top: 30px !important;
  }
}
</style>

<div class="card card-vuela p-4 p-md-5">
  <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
    <div class="d-flex align-items-center gap-3">
      <img src="<?= e(asset('img/logo-vuela.png')) ?>" height="65" alt="Vuela Internet">
      <div>
        <h3 class="fw-extrabold text-dark mb-0">Formulario Oficial de Evaluación</h3>
        <p class="text-muted small mb-0">Vuela Internet · Reporte Ejecutivo de Satisfacción de Atención al Cliente</p>
      </div>
    </div>
    <div class="text-end">
      <span class="badge text-bg-warning px-3 py-2 fw-bold fs-7"><i class="fa-solid fa-file-contract me-1"></i>Documento Oficial</span>
      <small class="d-block text-muted mt-1">Fecha: <b><?= e(date('d/m/Y H:i')) ?></b></small>
    </div>
  </div>

  <!-- Filtros de Fecha, Meses y Clasificaciones para Formulario e Impresión -->
  <form class="p-3 bg-light rounded-4 border mb-4 d-print-none" method="get" action="<?= e(url('/admin/formulario')) ?>">
    <div class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label fw-bold text-dark small mb-1"><i class="fa-solid fa-list-check text-vuela me-1"></i>Clasificación / Cuestionario</label>
        <select class="form-select form-select-sm fw-bold" name="encuesta">
          <option value="">Todas las Clasificaciones</option>
          <?php foreach ($encuestas as $encName): ?>
            <option value="<?= e($encName) ?>" <?= ($encuestaActual ?? '') === $encName ? 'selected' : '' ?>><?= e($encName) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label fw-bold text-dark small mb-1">Año</label>
        <select class="form-select form-select-sm" name="anio">
          <option value="">Todos</option>
          <?php foreach ($anios as $a): ?><option <?= ($anio ?? '') === $a ? 'selected' : '' ?>><?= e($a) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label fw-bold text-dark small mb-1">Mes</label>
        <select class="form-select form-select-sm" name="mes">
          <option value="">Todos</option>
          <?php foreach ($meses as $m): ?><option <?= ($mes ?? '') === $m ? 'selected' : '' ?>><?= e($m) ?></option><?php endforeach; ?>
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
        <button type="submit" class="btn btn-vuela btn-sm w-100 fw-bold py-2"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
      </div>
    </div>
  </form>

  <!-- Indicadores Clave -->
  <div class="row g-3 mb-4">
    <div class="col-4">
      <div class="stat text-center border rounded-4 p-3 bg-light">
        <div class="text-muted small fw-semibold">Total Evaluaciones</div>
        <b class="fs-2 text-vuela"><?= (int)$stats['total'] ?></b>
      </div>
    </div>
    <div class="col-4">
      <div class="stat text-center border rounded-4 p-3 bg-light">
        <div class="text-muted small fw-semibold">Agentes en Ranking</div>
        <b class="fs-2 text-dark"><?= count($ranking) ?></b>
      </div>
    </div>
    <div class="col-4">
      <div class="stat text-center border rounded-4 p-3 bg-light">
        <div class="text-muted small fw-semibold">Cuestionario Mostrado</div>
        <b class="fs-6 text-dark d-block mt-1 text-truncate"><?= e($encuestaActual !== '' ? $encuestaActual : 'Todas las Encuestas') ?></b>
      </div>
    </div>
  </div>

  <!-- Gráfica Pastel / Dona de Calificaciones -->
  <div class="row align-items-center my-4 g-4">
    <div class="col-6 text-center">
      <h5 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-chart-pie me-2 text-vuela"></i>Distribución de Calificaciones</h5>
      <?php if ($stats['total'] > 0): ?>
        <div style="position: relative; height: 240px; width: 100%; margin: 0 auto;">
          <canvas id="formPieChart"></canvas>
        </div>
      <?php else: ?>
        <div class="p-5 border border-dashed rounded-4 bg-light text-center">
          <i class="fa-solid fa-chart-pie fs-1 text-muted mb-2"></i>
          <h6 class="fw-bold text-dark mb-1">Sin registros aún</h6>
          <p class="text-muted small mb-0">Los gráficos y métricas se actualizarán automáticamente cuando se registren calificaciones.</p>
        </div>
      <?php endif; ?>
    </div>

    <div class="col-6">
      <h5 class="fw-bold mb-3 text-dark">Resumen por Criterio de Calidad</h5>
      <ul class="list-group list-group-flush border rounded-3">
        <?php 
        $totalVotos = max(1, (int)$stats['total']);
        foreach ($stats['por_etiqueta'] as $etiqueta => $cant): 
          $pct = round(($cant / $totalVotos) * 100);
        ?>
          <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
            <span class="fw-semibold text-dark"><i class="fa-solid fa-circle me-2 text-vuela fs-8"></i><?= e($etiqueta) ?></span>
            <div>
              <span class="badge text-bg-light border me-2 fs-7"><?= (int)$cant ?> votos</span>
              <span class="badge text-bg-warning fw-bold fs-7"><?= $pct ?>%</span>
            </div>
          </li>
        <?php endforeach; ?>
        <?php if (empty($stats['por_etiqueta'])): ?>
          <li class="list-group-item text-muted text-center py-4">
            <i class="fa-solid fa-folder-open fs-4 d-block mb-1 text-secondary"></i>
            Sin calificaciones registradas para este filtro.
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>

  <!-- Tabla Detallada de Personas / Clientes y Calificaciones dadas con Fecha -->
  <div class="my-4 pt-4 border-top">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h5 class="fw-bold text-dark mb-0">
        <i class="fa-solid fa-table-list text-vuela me-2"></i>Registro Detallado de Personas / Clientes y Calificaciones
      </h5>
      <span class="badge text-bg-secondary px-3 py-2 fs-8"><?= count($stats['rows']) ?> registros</span>
    </div>

    <div class="table-responsive rounded-4 border bg-white shadow-sm">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3">#</th>
            <th>Fecha y Hora</th>
            <th>Cliente / Persona</th>
            <th>Tipo</th>
            <th>Cuestionario</th>
            <th>Calificación Dada</th>
            <th>Agente que Atendió</th>
            <th class="text-end d-print-none">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $i = 1;
          foreach (array_reverse($stats['rows']) as $row): 
            $isVis = ($row['tipo'] ?? '') === 'visitante';
            $score = (int)($row['score'] ?? 0);
            $badgeBg = 'text-bg-success';
            if ($score <= 2) $badgeBg = 'text-bg-danger';
            elseif ($score == 3) $badgeBg = 'text-bg-warning text-dark';
          ?>
            <tr>
              <td class="ps-3 fw-bold text-muted fs-8"><?= $i++ ?></td>
              <td>
                <span class="fw-semibold text-dark small d-block">
                  <i class="fa-regular fa-calendar-days me-1 text-vuela"></i><?= e(date('d/m/Y H:i', strtotime($row['fecha'] ?? 'now'))) ?>
                </span>
              </td>
              <td>
                <?php if ($isVis): ?>
                  <span class="badge text-bg-secondary px-2 py-1"><i class="fa-solid fa-user-tag me-1"></i>Visitante Directo</span>
                <?php else: ?>
                  <span class="fw-bold text-primary"><i class="fa-solid fa-user me-1"></i><?= e($row['codigo']) ?></span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge text-bg-light border text-dark"><?= ucfirst(e($row['tipo'] ?? 'cliente')) ?></span>
              </td>
              <td>
                <small class="text-muted fw-medium"><?= e($row['encuesta_tipo'] ?? 'Atención General') ?></small>
              </td>
              <td>
                <span class="badge <?= $badgeBg ?> px-3 py-2 fs-7">
                  <i class="fa-solid <?= $score >= 4 ? 'fa-face-smile' : ($score == 3 ? 'fa-face-meh' : 'fa-face-frown') ?> me-1"></i>
                  <?= (int)$row['score'] ?> · <?= e($row['etiqueta'] ?? '') ?>
                </span>
              </td>
              <td>
                <strong class="text-dark small"><i class="fa-solid fa-headset me-1 text-vuela"></i><?= e($row['agente_nombre'] ?? 'Agente') ?></strong>
                <small class="text-muted d-block fs-8">(<?= e($row['rol'] ?? 'ATC') ?>)</small>
              </td>
              <td class="text-end d-print-none">
                <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar esta calificación con 2 confirmaciones"
                        onclick="abrirModalEliminarCalificacionForm(<?= e(json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)) ?>)">
                  <i class="fa-solid fa-trash-can"></i>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($stats['rows'])): ?>
            <tr>
              <td colspan="8" class="text-center text-muted py-4">
                <i class="fa-solid fa-inbox fs-3 d-block mb-1 text-secondary"></i>
                No hay calificaciones ni clientes registrados en este periodo.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Única Firma Solicitada: Firma del Supervisor del Área Encargada -->
  <div class="mt-5 pt-4 text-center print-signature-box" style="max-width: 380px; margin: 40px auto 0 auto;">
    <div class="border-bottom border-dark border-2 mb-2" style="height: 50px;"></div>
    <h6 class="fw-bold text-dark mb-0">Firma del Supervisor del Área Encargada</h6>
    <p class="text-muted small mb-0">Vuela Internet · Dirección General</p>
  </div>

  <!-- Botones de Impresión / Guardar PDF -->
  <div class="d-flex flex-wrap justify-content-center gap-3 mt-4 d-print-none">
    <button type="button" class="btn btn-vuela btn-lg px-4 py-3 d-flex align-items-center gap-2 shadow-lg" onclick="window.print()">
      <i class="fa-solid fa-print fs-5"></i>
      <span>Imprimir Reporte Seleccionado / Filtrado</span>
    </button>

    <a href="<?= e(url('/admin/formulario')) ?>" class="btn btn-outline-dark btn-lg px-4 py-3 d-flex align-items-center gap-2">
      <i class="fa-solid fa-file-circle-check fs-5 text-vuela"></i>
      <span>Ver / Imprimir Reporte General Completo</span>
    </a>
  </div>
</div>

<!-- MODAL ELIMINAR CALIFICACIÓN (EN FORMULARIO) CON 2 CONFIRMACIONES -->
<div class="modal fade" id="modalEliminarForm" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg style-assistant-modal" style="border-radius:20px; overflow:hidden;">
      <div class="modal-header bg-danger text-white p-3 px-4">
        <h5 class="modal-title fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i>Eliminar Registro de Calificación</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form method="post" action="<?= e(url('/admin/calificaciones/eliminar')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="id" id="form_del_rating_id">

        <!-- PASO 1: Primera Confirmación -->
        <div id="formDelStep1" class="modal-body p-4">
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
              <strong class="text-dark" id="form_del_codigo">—</strong>
            </div>
            <div class="d-flex justify-content-between small text-muted mb-1">
              <span>Calificación:</span>
              <strong class="text-vuela" id="del_form_score">—</strong>
            </div>
            <div class="d-flex justify-content-between small text-muted">
              <span>Agente:</span>
              <strong class="text-dark" id="del_form_agente">—</strong>
            </div>
          </div>

          <p class="small text-muted mb-0">
            ¿Desea proceder al siguiente paso de confirmación para eliminar este registro de evaluación?
          </p>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-danger px-4 fw-bold" onclick="irAPaso2Form()">
              Siguiente: Confirmación Final <i class="fa-solid fa-arrow-right me-1"></i>
            </button>
          </div>
        </div>

        <!-- PASO 2: Confirmación Definitiva -->
        <div id="formDelStep2" class="modal-body p-4 d-none">
          <div class="alert alert-danger border-danger mb-3">
            <h6 class="fw-bold mb-1"><i class="fa-solid fa-radiation me-2"></i>PREGUNTA DE SEGURIDAD FINAL (2/2)</h6>
            <p class="small mb-0">¿Realmente está seguro de eliminar este registro permanentemente?</p>
          </div>

          <p class="small text-muted mb-3">
            Esta acción eliminará el registro de la base de datos local y de Google Sheets. Los puntos acumulados por el agente se recalcularán automáticamente.
          </p>

          <div class="form-check p-3 bg-danger-subtle rounded-3 border border-danger-subtle mb-4">
            <input class="form-check-input ms-0 me-2" type="checkbox" name="confirm_delete" value="1" id="form_confirm_chk" required>
            <label class="form-check-label fw-bold text-danger small" for="form_confirm_chk">
              Sí, confirmo que deseo eliminar definitivamente esta calificación del sistema.
            </label>
          </div>

          <div class="d-flex justify-content-between align-items-center">
            <button type="button" class="btn btn-outline-secondary px-3" onclick="volverAPaso1Form()">
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
function abrirModalEliminarCalificacionForm(r) {
  document.getElementById('form_del_rating_id').value = r.id || '';
  document.getElementById('form_del_codigo').textContent = r.codigo || '—';
  document.getElementById('del_form_score').textContent = (r.score || '0') + ' pts (' + (r.etiqueta || '') + ')';
  document.getElementById('del_form_agente').textContent = r.agente_nombre || '—';
  document.getElementById('form_confirm_chk').checked = false;

  volverAPaso1Form();

  var modal = new bootstrap.Modal(document.getElementById('modalEliminarForm'));
  modal.show();
}

function irAPaso2Form() {
  document.getElementById('formDelStep1').classList.add('d-none');
  document.getElementById('formDelStep2').classList.remove('d-none');
}

function volverAPaso1Form() {
  document.getElementById('formDelStep2').classList.add('d-none');
  document.getElementById('formDelStep1').classList.remove('d-none');
}
</script>



<?php if ($stats['total'] > 0): ?>
<script>
new Chart(document.getElementById('formPieChart'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode(array_keys($stats['por_etiqueta']), JSON_UNESCAPED_UNICODE) ?>,
    datasets: [{
      data: <?= json_encode(array_values($stats['por_etiqueta'])) ?>,
      backgroundColor: ['#26a69a', '#66bb6a', '#ffca28', '#ef5350', '#F15A22', '#3b82f6', '#8e44ad'],
      borderWidth: 0,
      hoverOffset: 12
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: 'bottom', labels: { font: { family: 'Plus Jakarta Sans', size: 12 }, usePointStyle: true } }
    },
    cutout: '65%',
    animation: { animateScale: true, animateRotate: true }
  }
});
</script>
<?php endif; ?>
