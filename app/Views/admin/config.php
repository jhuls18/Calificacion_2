<?php if (!empty($ok)): ?>
  <div class="alert alert-success alert-vuela d-flex align-items-center mb-3">
    <i class="fa-solid fa-circle-check me-2 fs-5"></i>
    <div><?= e($ok) ?></div>
  </div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger alert-vuela d-flex align-items-center mb-3">
    <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i>
    <div><?= e($error) ?></div>
  </div>
<?php endif; ?>

<div class="card card-vuela p-4 p-md-5">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h3 class="fw-extrabold mb-1 text-dark">Configuración General del Sistema</h3>
      <p class="text-muted small mb-0">Ajusta los parámetros operativos y vincula tu documento de Excel / Google Sheets.</p>
    </div>
    <span class="badge badge-vuela p-2"><i class="fa-solid fa-sliders me-1"></i> Control Admin</span>
  </div>

  <form method="post" action="<?= e(url('/admin/config')) ?>">
    <?= csrf_field() ?>

    <!-- Escala de Caritas -->
    <div class="p-4 bg-light border rounded-4 mb-4">
      <h5 class="fw-bold text-dark mb-2"><i class="fa-solid fa-face-smile text-vuela me-2"></i>Escala de Caritas Interactivas</h5>
      <p class="text-muted small mb-3">Define la cantidad de niveles de valoración que verán los clientes en la pantalla de calificación.</p>
      
      <div class="row align-items-center">
        <div class="col-md-9">
          <input class="form-range" type="range" min="3" max="11" name="niveles_caritas" id="rango" value="<?= (int)($cfg['niveles_caritas'] ?? 5) ?>" oninput="document.getElementById('nval').textContent=this.value">
        </div>
        <div class="col-md-3 text-center">
          <span class="badge text-bg-warning fs-6 px-3 py-2">Escala: <b id="nval"><?= (int)($cfg['niveles_caritas'] ?? 5) ?></b> Niveles</span>
        </div>
      </div>
    </div>

    <!-- Sección Especial: Vincular y Modificar Documento de Excel / Google Sheets (Estilo Uiverse Upload) -->
    <div class="p-4 border border-warning-subtle rounded-4 bg-warning-subtle bg-opacity-10 mb-4">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
          <i class="fa-solid fa-file-excel fs-3 text-success"></i>
          <h5 class="fw-bold text-dark mb-0">Vincular y Modificar Documento Excel (Google Sheets)</h5>
        </div>
        <span class="badge text-bg-success px-3 py-2 fw-bold"><i class="fa-solid fa-cloud-arrow-up me-1"></i>Sincronización en Vivo</span>
      </div>
      
      <!-- Zona Visual Uiverse Upload File -->
      <div class="vuela-upload-zone mb-4" onclick="document.getElementById('input_gs_url').focus()">
        <div class="vuela-upload-icon">
          <i class="fa-solid fa-file-csv"></i>
        </div>
        <h6 class="fw-bold text-dark mb-1">Vincule su Hoja de Cálculo o Excel de Google Sheets</h6>
        <p class="text-muted small mb-0">Todos los votos registrados se enviarán automáticamente a su documento compartido en la nube.</p>
      </div>

      <div class="mb-3">
        <label class="form-label fw-bold text-dark fs-7">1. URL Web App de Google Apps Script (Receptor de datos en vivo)</label>
        <div class="input-group-vuela">
          <span class="input-icon text-success"><i class="fa-solid fa-link"></i></span>
          <input class="form-control form-control-vuela" id="input_gs_url" name="google_script_url" value="<?= e($cfg['google_script_url'] ?? '') ?>" placeholder="https://script.google.com/macros/s/.../exec">
        </div>
        <small class="text-muted d-block mt-1">Pega la URL de la Web App desplegada en Apps Script para registrar votos automáticamente en el Excel.</small>
      </div>

      <div class="mb-3">
        <label class="form-label fw-bold text-dark fs-7">2. Enlace Directo para Abrir el Documento Excel en Línea</label>
        <div class="input-group-vuela">
          <span class="input-icon text-primary"><i class="fa-solid fa-file-lines"></i></span>
          <input class="form-control form-control-vuela" name="google_sheet_view_url" value="<?= e($cfg['google_sheet_view_url'] ?? '') ?>" placeholder="https://docs.google.com/spreadsheets/d/.../edit">
        </div>
        <small class="text-muted d-block mt-1">Este enlace habilitará el botón <strong>"Abrir Excel en Línea"</strong> en la pantalla de reportes.</small>
      </div>

      <!-- Guía de columnas para crear el Excel -->
      <div class="p-3 bg-white rounded-3 border mt-3 shadow-sm">
        <strong class="d-block text-dark small mb-2"><i class="fa-solid fa-table text-success me-1"></i> Estructura Exacta del Excel / Google Sheets (11 Columnas en Fila 1):</strong>
        <div class="d-flex flex-wrap gap-1 fs-8 mb-3">
          <span class="badge text-bg-light border text-dark fw-semibold">Col A (1): Fecha</span>
          <span class="badge text-bg-light border text-dark fw-semibold">Col B (2): Código / CI</span>
          <span class="badge text-bg-light border text-dark fw-semibold">Col C (3): Tipo</span>
          <span class="badge text-bg-light border text-dark fw-semibold">Col D (4): Cuestionario</span>
          <span class="badge text-bg-light border text-dark fw-semibold">Col E (5): Visita Nro</span>
          <span class="badge text-bg-light border text-dark fw-semibold">Col F (6): Score</span>
          <span class="badge text-bg-light border text-dark fw-semibold">Col G (7): Etiqueta</span>
          <span class="badge text-bg-light border text-dark fw-semibold">Col H (8): Puntos</span>
          <span class="badge text-bg-light border text-dark fw-semibold">Col I (9): Agente Nombre</span>
          <span class="badge text-bg-light border text-dark fw-semibold">Col J (10): Agente Rol</span>
          <span class="badge text-bg-light border text-dark fw-semibold">Col K (11): IP</span>
        </div>

        <div class="accordion border-0" id="accGoogleScript">
          <div class="accordion-item border rounded-3 overflow-hidden">
            <h2 class="accordion-header" id="headingScript">
              <button class="accordion-button collapsed bg-light text-dark fw-bold py-2 px-3 small" type="button" data-bs-toggle="collapse" data-bs-target="#collapseScript" aria-expanded="false" aria-controls="collapseScript">
                <i class="fa-solid fa-code text-warning me-2"></i> Ver Código de Google Apps Script para copiar a tu Hoja
              </button>
            </h2>
            <div id="collapseScript" class="accordion-collapse collapse" aria-labelledby="headingScript" data-bs-parent="#accGoogleScript">
              <div class="accordion-body p-3 bg-dark text-white rounded-bottom">
                <p class="small text-white-50 mb-2"><i class="fa-solid fa-info-circle me-1"></i> Copia este código en <strong>Extensiones > Apps Script</strong> de tu Google Sheet y publica como Aplicación Web ("Cualquiera"):</p>
                <pre class="bg-black text-warning p-3 rounded-3 small border border-secondary" style="max-height: 250px; overflow-y: auto; user-select: all; font-family: monospace;">
function doPost(e) {
  return handleRequest(e);
}

function doGet(e) {
  return handleRequest(e);
}

function handleRequest(e) {
  var lock = LockService.getScriptLock();
  lock.tryLock(10000);
  try {
    var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
    var contents = "";
    if (e && e.postData && e.postData.contents) {
      contents = e.postData.contents;
    } else if (e && e.parameter && e.parameter.data) {
      contents = e.parameter.data;
    }

    var data = {};
    if (contents) {
      try { data = JSON.parse(contents); } catch(err) { data = {}; }
    }

    var action = data.action || (e && e.parameter ? e.parameter.action : "");

    if (action === "saveRating" || action === "save") {
      var r = data.row || data;
      sheet.appendRow([
        r.fecha || new Date().toLocaleString("es-BO"),
        r.codigo || "",
        r.tipo || "cliente",
        r.encuesta_tipo || "Atención General",
        r.visita_nro || 1,
        r.score || 0,
        r.etiqueta || "",
        r.puntos || 0,
        r.agente_nombre || "",
        r.rol || "",
        r.ip || ""
      ]);
      return ContentService.createTextOutput(JSON.stringify({ ok: true, mensaje: "Voto registrado exitosamente" })).setMimeType(ContentService.MimeType.JSON);
    }

    if (action === "getRatings") {
      var rows = sheet.getDataRange().getValues();
      var ratings = [];
      for (var i = 1; i < rows.length; i++) {
        ratings.push({
          fecha: rows[i][0],
          codigo: rows[i][1],
          tipo: rows[i][2],
          encuesta_tipo: rows[i][3],
          visita_nro: rows[i][4],
          score: rows[i][5],
          etiqueta: rows[i][6],
          puntos: rows[i][7],
          agente_nombre: rows[i][8],
          rol: rows[i][9],
          ip: rows[i][10]
        });
      }
      return ContentService.createTextOutput(JSON.stringify({ ok: true, ratings: ratings })).setMimeType(ContentService.MimeType.JSON);
    }
    return ContentService.createTextOutput(JSON.stringify({ ok: true, mensaje: "Google Script Activo" })).setMimeType(ContentService.MimeType.JSON);
  } catch (err) {
    return ContentService.createTextOutput(JSON.stringify({ ok: false, error: err.toString() })).setMimeType(ContentService.MimeType.JSON);
  } finally {
    lock.releaseLock();
  }
}
</pre>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="form-check form-switch mb-4 fs-6">
      <input class="form-check-input" type="checkbox" name="permitir_visitante" id="pv" <?= !empty($cfg['permitir_visitante']) ? 'checked' : '' ?>>
      <label class="form-check-label fw-bold text-dark ms-2" for="pv">Permitir calificación en modo Visitante / Directo (sin código obligante)</label>
    </div>

    <div class="d-flex flex-wrap gap-3 mb-5">
      <button type="submit" class="btn btn-vuela btn-lg py-3 px-5 d-flex align-items-center gap-2 shadow-sm">
        <i class="fa-solid fa-floppy-disk fs-5"></i>
        <span>Guardar Cambios de Configuración</span>
      </button>

      <?php if (!empty($cfg['google_script_url'])): ?>
        <form method="post" action="<?= e(url('/admin/sync')) ?>" class="d-inline">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-success btn-lg py-3 px-4 d-flex align-items-center gap-2 shadow-sm">
            <i class="fa-solid fa-rotate me-1 fs-5"></i>
            <span>🔄 Sincronizar Votos desde Google Sheets</span>
          </button>
        </form>

        <form method="post" action="<?= e(url('/admin/config/test-sheets')) ?>" class="d-inline">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-outline-success btn-lg py-3 px-4 d-flex align-items-center gap-2">
            <i class="fa-solid fa-vial-circle-check fs-5"></i>
            <span>⚡ Probar Conexión</span>
          </button>
        </form>
      <?php endif; ?>

    </div>
  </form>

  <!-- ZONA PELIGROSA: ELIMINAR TODOS LOS REGISTROS -->
  <div class="card border-danger border-2 rounded-4 p-4 mb-5 shadow-sm" style="background: #fff5f5;">
    <div class="d-flex align-items-center gap-3 mb-3">
      <div style="width:52px;height:52px;border-radius:50%;background:#ef4444;display:flex;align-items:center;justify-content:center;">
        <i class="fa-solid fa-skull-crossbones text-white fs-4"></i>
      </div>
      <div>
        <h5 class="fw-extrabold text-danger mb-0">⚠️ Zona Peligrosa — Eliminar Todos los Registros</h5>
        <p class="text-muted small mb-0">Esta acción borra <strong>TODAS</strong> las calificaciones de la base de datos local y del documento Excel/Sheets. Esta acción es <strong>irreversible</strong>.</p>
      </div>
    </div>
    <div class="alert alert-danger border-danger d-flex align-items-center gap-2 mb-3 py-2 px-3" style="font-size:0.88rem;">
      <i class="fa-solid fa-triangle-exclamation"></i>
      <span>Se requieren <strong>3 confirmaciones</strong> para ejecutar esta acción. Los datos no podrán recuperarse una vez eliminados.</span>
    </div>
    <button type="button" class="btn btn-outline-danger fw-bold px-4 py-2 d-flex align-items-center gap-2" id="btnBorrarTodo"
      onclick="confirmarBorrarTodo()">
      <i class="fa-solid fa-trash-can fs-5"></i>
      <span>🗑️ Borrar Todos los Registros (Base de Datos + Excel)</span>
    </button>
  </div>

  <script>
  function confirmarBorrarTodo() {
    if (!confirm('⚠️ CONFIRMACIÓN 1 de 3:\n\n¿Está seguro de que desea BORRAR TODOS los registros de calificaciones?\n\nEsta acción eliminará todos los datos de la base de datos local.')) return;
    if (!confirm('🚨 CONFIRMACIÓN 2 de 3:\n\n¡ADVERTENCIA CRÍTICA!\nSe eliminarán PERMANENTEMENTE todos los registros.\n¿Confirma que desea continuar? NO hay forma de deshacer esto.')) return;
    if (!confirm('⛔ CONFIRMACIÓN 3 de 3 — ÚLTIMA OPORTUNIDAD:\n\nEscriba mentalmente "ELIMINAR TODO" y presione Aceptar para confirmar la eliminación definitiva de todos los registros.')) return;

    // Crear y enviar formulario
    var frm = document.createElement('form');
    frm.method = 'POST';
    frm.action = '<?= e(url('/admin/reset-data')) ?>';
    var csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = 'csrf';
    csrf.value = '<?= csrf_token() ?>';
    frm.appendChild(csrf);
    document.body.appendChild(frm);
    frm.submit();
  }
  </script>

  <!-- MÓDULO ESPECIAL: GESTOR DE CUESTIONARIOS Y ENCUESTAS PERSONALIZADAS -->
  <div class="border-top pt-4 mt-2">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <h4 class="fw-extrabold text-dark mb-1"><i class="fa-solid fa-list-check text-vuela me-2"></i>Gestor de Cuestionarios y Encuestas Personalizadas</h4>
        <p class="text-muted small mb-0">Crea encuestas independientes con su propia escala de caritas (3, 4, 5 o 10 niveles) y preguntas personalizadas.</p>
      </div>
    </div>

    <!-- Lista de Cuestionarios Configurados -->
    <div class="row g-3 mb-4">
      <?php 
      $cuestionarios = $cfg['cuestionarios'] ?? [];
      foreach ($cuestionarios as $c): 
      ?>
        <div class="col-md-6">
          <div class="border rounded-4 p-3 bg-light h-100 d-flex flex-column justify-content-between">
            <div>
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="fw-bold text-dark mb-0"><i class="fa-solid <?= e($c['icono'] ?? 'fa-list-check') ?> text-vuela me-1"></i> <?= e($c['titulo']) ?></h6>
                <span class="badge text-bg-warning fw-bold px-3 py-1">Escala: <?= (int)($c['escala'] ?? 5) ?> Caritas</span>
              </div>
              <p class="small text-muted mb-2"><strong>Pregunta:</strong> "<?= e($c['pregunta']) ?>"</p>
            </div>
            <?php if (($c['id'] ?? '') !== 'cuest-general'): ?>
              <form method="post" action="<?= e(url('/admin/cuestionario/eliminar')) ?>" class="mt-2 text-end">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= e($c['id']) ?>">
                <button type="submit" class="btn btn-outline-danger btn-sm px-3 fw-bold" onclick="return confirm('¿Eliminar este cuestionario?')">
                  <i class="fa-solid fa-trash me-1"></i>Eliminar
                </button>
              </form>
            <?php else: ?>
              <span class="badge bg-secondary-subtle text-secondary align-self-end mt-2 fs-8">Cuestionario Principal</span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Formulario para Crear Nuevo Cuestionario -->
    <div class="card p-4 border border-primary-subtle rounded-4 bg-primary-subtle bg-opacity-10">
      <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-plus-circle text-primary me-2"></i>Crear Nuevo Cuestionario / Encuesta</h5>
      <form method="post" action="<?= e(url('/admin/cuestionario/crear')) ?>">
        <?= csrf_field() ?>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-bold text-dark small">Título del Cuestionario</label>
            <input type="text" class="form-control form-control-vuela" name="titulo" placeholder="Ej. Encuesta Rápida de Baja" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold text-dark small">Escala de Caritas para este Cuestionario</label>
            <select class="form-select form-select-lg fw-bold" name="escala" style="border-radius:14px; height:52px;">
              <option value="3">3 Caritas (Malo, Regular, Excelente)</option>
              <option value="4">4 Caritas (Malo, Regular, Bueno, Excelente)</option>
              <option value="5" selected>5 Caritas (Estándar Completo)</option>
              <option value="6">6 Caritas (Escala 1 a 6)</option>
              <option value="7">7 Caritas (Escala 1 a 7)</option>
              <option value="8">8 Caritas (Escala 1 a 8)</option>
              <option value="9">9 Caritas (Escala 1 a 9)</option>
              <option value="10">10 Niveles (Escala 1 a 10)</option>
              <option value="11">11 Niveles (Escala 1 a 11)</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label fw-bold text-dark small">Pregunta Principal que verá el Cliente</label>
            <input type="text" class="form-control form-control-vuela" name="pregunta" placeholder="Ej. ¿Cómo evalúa la atención recibida durante el trámite de su baja?" required>
          </div>
          <div class="col-12 text-end">
            <button type="submit" class="btn btn-vuela fw-bold px-4 py-2">
              <i class="fa-solid fa-plus me-1"></i> Registrar Cuestionario
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
