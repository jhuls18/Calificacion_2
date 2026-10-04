<!-- Relaxing Vuela Sunset Starfield Background -->
<div class="vuela-starfield">
  <div id="vuela-stars"></div>
  <div id="vuela-stars2"></div>
  <div id="vuela-stars3"></div>
</div>

<!-- Barra Superior de Navegación y Control de Pantalla -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div class="d-flex flex-wrap gap-2">
    <!-- Botón 1: Solo Calificación Pantalla Completa (Kiosco) -->
    <button type="button" class="btn btn-vuela btn-sm rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm" id="btnToggleKiosk">
      <i class="fa-solid fa-expand fs-6"></i>
      <span>🖥️ Solo Calificación (Pantalla Completa Kiosco)</span>
    </button>

    <!-- Botón 4: Modo Cliente Limpio (Ocultar Datos Internos) -->
    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm bg-white" id="btnToggleCleanMode">
      <i class="fa-solid fa-eye-slash text-primary fs-6"></i>
      <span id="lblCleanMode">Modo Cliente Limpio (Ocultar Datos Internos)</span>
    </button>
  </div>
  
  <a href="<?= e(url('/panel')) ?>" class="btn btn-sm btn-ghost px-3 py-2 d-flex align-items-center gap-2 bg-white">
    <i class="fa-solid fa-house"></i>
    <span>Volver al Menú Principal</span>
  </a>
</div>

<!-- Botón Flotante para Salir de Pantalla Completa -->
<button type="button" class="btn btn-dark btn-sm rounded-pill shadow-lg kiosk-exit-bar d-none" id="btnExitKiosk" style="position:fixed; top:18px; right:18px; z-index:999999; border: 2px solid #fff;">
  <i class="fa-solid fa-compress me-1"></i> Salir de Pantalla Completa
</button>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger alert-vuela d-flex align-items-center mb-3">
    <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i>
    <div><?= e($error) ?></div>
  </div>
<?php endif; ?>

<div class="card card-vuela card-vuela-kiosk p-4 p-md-5">
  <div class="text-center mb-4">
    <img src="<?= e(asset('img/logo-vuela.png')) ?>" height="58" alt="Vuela" class="mb-2">
    <h2 class="fw-extrabold" style="color:var(--vuela)">Encuesta de Calificación</h2>
    <div class="kiosk-hide-internal">
      <p class="text-muted mb-0">Agente: <b class="text-dark"><?= e($user['nombre']) ?></b> · <span class="badge badge-vuela"><?= e($user['rol']) ?></span> · Escala 1 a <?= (int)$max ?></p>
    </div>
  </div>

  <form id="form-calificar" method="post" action="<?= e(url('/calificar')) ?>" data-once>
    <?= csrf_field() ?>
    <input type="hidden" name="tipo" id="tipo" value="cliente">
    <input type="hidden" name="score" id="score" value="">
    <input type="hidden" name="score_max" id="score_max" value="<?= (int)$max ?>">
    <input type="hidden" name="encuesta_tipo" id="encuesta_tipo" value="<?= e($cuestionarios[0]['titulo'] ?? 'Atención General') ?>">

    <!-- Selector de Cuestionario / Tipo de Encuesta (Oculto en Modo Cliente Limpio / Kiosco) -->
    <div class="mb-4 text-center kiosk-hide-internal">
      <label class="form-label fw-bold text-dark small mb-2"><i class="fa-solid fa-list-check text-vuela me-1"></i> Seleccionar Cuestionario / Tipo de Encuesta:</label>
      <div class="d-flex flex-wrap gap-2 justify-content-center" id="survey-type-selector">
        <?php 
        $cuestList = !empty($cuestionarios) ? $cuestionarios : [
          ['id' => 'cuest-gen', 'titulo' => 'Atención General', 'pregunta' => '¿Cómo califica la atención recibida hoy en caja u oficina?', 'escala' => $max, 'icono' => 'fa-headset']
        ];
        $isFirst = true;
        foreach ($cuestList as $c): 
          $activeClass = $isFirst ? 'btn-vuela active' : 'btn-outline-secondary';
          $isFirst = false;
        ?>
          <button type="button" class="btn btn-sm <?= $activeClass ?> btn-survey-type px-3 py-2 fw-bold rounded-pill" 
            data-tipo="<?= e($c['titulo']) ?>" 
            data-pregunta="<?= e($c['pregunta']) ?>"
            data-escala="<?= (int)($c['escala'] ?? $max) ?>">
            <i class="fa-solid <?= e($c['icono'] ?? 'fa-list-check') ?> me-1"></i> <?= e($c['titulo']) ?>
            <span class="badge text-bg-light border ms-1 fs-8"><?= (int)($c['escala'] ?? $max) ?> Caritas</span>
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- MÓDULO DE 4 OPCIONES DE REGISTRO DE ATENCIÓN (Kiosco / Caja) -->
    <div class="p-3 bg-light border rounded-4 mb-4">
      <label class="form-label fw-bold text-dark small mb-2"><i class="fa-solid fa-sliders text-vuela me-1"></i> Modo de Registro de Atención (4 Opciones):</label>
      
      <div class="row g-2 align-items-stretch">
        <!-- Opción 1: Código de Cliente -->
        <div class="col-md-5">
          <label class="form-label fw-bold fs-8 text-muted mb-1">1. Código de Cliente / C.I.</label>
          <div class="input-group-vuela">
            <span class="input-icon"><i class="fa-solid fa-hashtag"></i></span>
            <input class="form-control form-control-vuela" id="codigo_cliente" name="codigo" placeholder="Ej. 84650 o vacío" autocomplete="off">
          </div>
        </div>

        <!-- Opción 2: Visitante / Directo -->
        <div class="col-md-3 d-flex flex-column justify-content-end">
          <label class="form-label fw-bold fs-8 text-muted mb-1">2. Atención Directa</label>
          <button type="button" class="btn btn-ghost w-100 py-3 d-flex align-items-center justify-content-center gap-2" id="btn-visitante">
            <i class="fa-solid fa-person-walking text-vuela fs-5"></i>
            <span id="lbl-visitante-btn">Visitante / Directo</span>
          </button>
        </div>

        <!-- Opción 3: No Calificó / Cliente se Retiró -->
        <div class="col-md-4 d-flex flex-column justify-content-end">
          <label class="form-label fw-bold fs-8 text-muted mb-1">3. Abandono de Atención</label>
          <button type="button" class="btn btn-outline-danger w-100 py-3 d-flex align-items-center justify-content-center gap-2 fw-bold" id="btn-omitir-calificacion">
            <i class="fa-solid fa-user-slash fs-5"></i>
            <span>No Calificó / Se Retiró</span>
          </button>
        </div>
      </div>
    </div>
    
    <div id="cliente-info" class="alert alert-info border-0 shadow-sm d-none mb-3"></div>
    <script>
      window.VUELA_API_CLIENTE = <?= json_encode(url('/api/cliente')) ?>;
      window.VUELA_NIVELES_ALL = <?= json_encode($niveles_all ?? niveles_activos(11)) ?>;
      window.VUELA_MAX_CURRENT = <?= (int)$max ?>;
    </script>

    <div class="rating-box-wrap p-4 rounded-4 bg-white border text-center mb-4 shadow-sm">
      <h4 class="fw-bold text-dark mb-1" id="pregunta-encuesta-titulo">¿Cómo califica la atención recibida hoy en caja u oficina?</h4>
      <p class="text-muted small mb-4">Haz clic en la carita que represente tu experiencia de servicio.</p>

      <div class="rating-spectrum-container my-4">
        <div class="spectrum-bar">
          <div class="spectrum-fill" id="spectrum-fill"></div>
        </div>
        <div class="faces" id="caritas-container">
          <?php foreach ($niveles as $n): 
            $shortLabel = $n['etiqueta'] ?? null;
            if (!$shortLabel) {
              if ($n['n'] == 1) $shortLabel = 'Malo';
              elseif ($n['n'] == 2) $shortLabel = 'Regular';
              elseif ($n['n'] == 3) $shortLabel = 'Aceptable';
              elseif ($n['n'] == 4) $shortLabel = 'Bueno';
              elseif ($n['n'] == 5) $shortLabel = 'Excelente';
              else $shortLabel = 'Nivel ' . $n['n'];
            }
          ?>
            <div class="face-wrap">
              <button type="button" class="face-btn face-card-btn" style="background:<?= e($n['color']) ?>"
                data-score="<?= (int)$n['n'] ?>" data-max="<?= (int)$max ?>"
                data-msg="<?= e($n['mensaje']) ?>" data-icon="<?= e($n['icon']) ?>" data-label="<?= e($shortLabel) ?>">
                <i class="fa-solid <?= e($n['icon']) ?>"></i>
              </button>
              <div class="face-label-sub mt-2">
                <span class="badge rounded-pill text-bg-light border fw-bold px-2 py-1"><?= (int)$n['n'] ?></span>
                <small class="d-block text-dark fw-bold mt-1 fs-7"><?= e($shortLabel) ?></small>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-3 px-3 text-muted small fw-bold">
        <span class="text-danger"><i class="fa-solid fa-face-frown me-1"></i> Desfavorable (1)</span>
        <span class="text-success">Excelente (<?= (int)$max ?>) <i class="fa-solid fa-face-grin-stars ms-1"></i></span>
      </div>
    </div>

    <div id="feedback-dinamico" class="alert alert-success alert-vuela text-center d-none fw-bold fs-6"></div>

    <div class="d-grid mt-4">
      <button class="btn btn-vuela btn-lg py-3 fs-5 d-flex align-items-center justify-content-center gap-2 shadow-lg" type="submit">
        <i class="fa-solid fa-paper-plane me-1"></i>
        <span>Registrar Calificación</span>
      </button>
    </div>
  </form>
</div>

<!-- MÓDULO PARA ENVIAR ENLACE TEMPORAL DE 5 MINUTOS -->
<div class="card card-vuela p-4 mt-4 bg-white border border-primary-subtle shadow-sm">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div class="d-flex align-items-center gap-3">
      <div class="bg-primary text-white p-3 rounded-4 shadow-sm">
        <i class="fa-solid fa-link fs-2"></i>
      </div>
      <div>
        <h5 class="fw-bold text-dark mb-0">Enviar Enlace Temporal de Calificación al Cliente</h5>
        <p class="text-muted small mb-0">Genera un enlace seguro con <strong>validez de 5 minutos</strong>. La calificación se asignará automáticamente a tu cuenta (<b><?= e($user['nombre']) ?></b>).</p>
      </div>
    </div>
    <button type="button" class="btn btn-vuela btn-lg rounded-pill px-4 py-3 fw-bold d-flex align-items-center gap-2 shadow-sm" onclick="generarLinkWhatsapp()">
      <i class="fa-solid fa-paperclip fs-5"></i>
      <span>Generar Enlace Temporal (5 Min)</span>
    </button>
  </div>
</div>

<!-- MODAL ENLACE TEMPORAL -->
<div class="modal fade" id="modalLinkWhatsapp" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg style-assistant-modal" style="border-radius:24px; overflow:hidden;">
      <div class="modal-header bg-dark text-white p-3 px-4">
        <h5 class="modal-title fw-bold"><i class="fa-solid fa-link me-2 text-vuela"></i>Enlace Temporal de Calificación</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="alert alert-warning border-warning d-flex align-items-center justify-content-between mb-3">
          <span class="small fw-bold text-dark"><i class="fa-solid fa-clock-rotate-left text-warning me-1"></i> Validez del Enlace:</span>
          <span class="badge text-bg-warning fs-7 px-3 py-2 fw-bold" id="modalTimer">05:00 min</span>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold text-dark small">Mensaje Preparado para el Cliente:</label>
          <textarea class="form-control text-muted bg-light fw-medium mb-2" id="txtMsgWhatsapp" rows="5" style="resize:none; font-size:0.85rem;"></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold text-dark small">URL Directa del Enlace:</label>
          <div class="input-group">
            <input class="form-control form-control-sm text-primary fw-bold" id="txtUrlWhatsapp" readonly>
            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="copiarSoloUrl()"><i class="fa-solid fa-copy me-1"></i>Copiar URL</button>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light p-3 d-flex flex-wrap gap-2 justify-content-between">
        <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cerrar</button>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-vuela fw-bold px-4 shadow-sm" onclick="copiarMensajeCompleto()">
            <i class="fa-solid fa-copy me-1"></i> Copiar Mensaje con Enlace
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
var currentGeneratedUrl = '';
var currentGeneratedMsg = '';
var linkCountdownInterval = null;

function generarLinkWhatsapp() {
  var encuestaInput = document.getElementById('encuesta_tipo');
  var encuestaVal = encuestaInput ? encuestaInput.value : 'Atención General';
  var formData = new FormData();
  formData.append('csrf', '<?= csrf_token() ?>');
  formData.append('encuesta_tipo', encuestaVal);

  fetch('<?= e(url('/api/token/crear')) ?>', {
    method: 'POST',
    body: formData
  })
  .then(function(r) {
    var ct = (r.headers.get('content-type') || '');
    if (!ct.includes('application/json')) {
      throw new Error('Sesión expirada. Por favor, recargue la página e inicie sesión nuevamente.');
    }
    return r.json();
  })
  .then(function(data) {
    if (!data.ok || !data.url) {
      alert(data.error || 'Error al generar el enlace temporal.');
      if (data.error && data.error.toLowerCase().includes('sesión')) {
        location.reload();
      }
      return;
    }
    currentGeneratedUrl = data.url;
    
    currentGeneratedMsg = '⭐ *Vuela Internet* - Queremos saber tu opinión. Por favor califica la atención recibida (válido 5 min):\n' + currentGeneratedUrl;

    document.getElementById('txtUrlWhatsapp').value = currentGeneratedUrl;
    document.getElementById('txtMsgWhatsapp').value = currentGeneratedMsg;

    startModalTimer(data.expires_at || (Math.floor(Date.now()/1000) + 300));

    var modal = new bootstrap.Modal(document.getElementById('modalLinkWhatsapp'));
    modal.show();
  })
  .catch(function(err) {
    alert('No se pudo generar el enlace: ' + err);
  });
}

function startModalTimer(expiresAt) {
  clearInterval(linkCountdownInterval);
  var badge = document.getElementById('modalTimer');
  
  function tick() {
    var now = Math.floor(Date.now() / 1000);
    var diff = expiresAt - now;
    if (diff <= 0) {
      if (badge) badge.textContent = 'EXPIRADO (00:00)';
      clearInterval(linkCountdownInterval);
      return;
    }
    var m = Math.floor(diff / 60);
    var s = diff % 60;
    if (badge) badge.textContent = (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s) + ' min';
  }
  tick();
  linkCountdownInterval = setInterval(tick, 1000);
}

function copiarMensajeCompleto() {
  if (!currentGeneratedMsg) return;
  navigator.clipboard.writeText(currentGeneratedMsg).then(function() {
    showCopyToast('¡Mensaje copiado al portapapeles! ✅');
  });
}

function copiarSoloUrl() {
  if (!currentGeneratedUrl) return;
  navigator.clipboard.writeText(currentGeneratedUrl).then(function() {
    showCopyToast('¡Enlace copiado! ✅');
  });
}

function showCopyToast(msg) {
  var t = document.getElementById('vuela-copy-toast');
  if (!t) {
    t = document.createElement('div');
    t.id = 'vuela-copy-toast';
    t.style.cssText = 'position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(20px);background:#1e293b;color:#fff;padding:10px 22px;border-radius:50px;font-weight:700;font-size:0.9rem;z-index:99999;opacity:0;transition:all 0.35s cubic-bezier(0.16,1,0.3,1);pointer-events:none;box-shadow:0 8px 30px rgba(0,0,0,0.25);';
    document.body.appendChild(t);
  }
  t.textContent = msg;
  t.style.opacity = '1';
  t.style.transform = 'translateX(-50%) translateY(0)';
  clearTimeout(t._timeout);
  t._timeout = setTimeout(function() {
    t.style.opacity = '0';
    t.style.transform = 'translateX(-50%) translateY(20px)';
  }, 2200);
}
</script>
