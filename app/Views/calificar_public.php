<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Desactivar Cache en Navegador -->
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title><?= e($title ?? 'Calificar Atención - Vuela Internet') ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="<?= e(asset('css/vuela.css')) ?>">
  <style>
    body {
      background: linear-gradient(135deg, #fffaf6 0%, #fff0e8 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
    }
    .public-card {
      max-width: 650px;
      width: 100%;
      border-radius: 28px;
      box-shadow: 0 25px 80px rgba(241, 90, 34, 0.18);
      background: #ffffff;
      border: 1px solid rgba(241, 90, 34, 0.12);
    }
  </style>
</head>
<body>

<div class="public-card p-4 p-md-5 bounce-in">
  <div class="text-center mb-4">
    <img src="<?= e(asset('img/logo-vuela.png')) ?>" height="54" alt="Vuela Internet" class="mb-2">
    <h3 class="fw-extrabold text-dark mb-1">Encuesta de Calificación</h3>
    <p class="text-muted small mb-0">Atendido por: <b class="text-dark"><?= e($agentName) ?></b> <span class="badge badge-vuela ms-1"><?= e($agentRol) ?></span></p>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-vuela mb-3"><i class="fa-solid fa-circle-exclamation me-1"></i><?= e($error) ?></div>
  <?php endif; ?>

  <form id="form-calificar-public" method="post" action="<?= e(url('/calificar/link')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= e($token['token'] ?? '') ?>">
    <input type="hidden" name="tipo" id="tipo" value="visitante">
    <input type="hidden" name="score" id="score" value="">
    <input type="hidden" name="score_max" id="score_max" value="<?= (int)$max ?>">
    <input type="hidden" name="codigo" value="DIRECTO">

    <!-- Rating spectrum caritas -->
    <div class="rating-box-wrap p-4 rounded-4 bg-white border text-center mb-4 shadow-sm">
      <h5 class="fw-bold text-dark mb-2"><?= e($pregunta) ?></h5>
      <p class="text-muted small mb-4">Haz clic en la carita que mejor represente tu atención:</p>

      <div class="rating-spectrum-container my-3">
        <div class="faces" id="caritas-container" style="justify-content: space-around;">
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
                data-score="<?= (int)$n['n'] ?>" data-msg="<?= e($n['mensaje']) ?>" data-icon="<?= e($n['icon']) ?>">
                <i class="fa-solid <?= e($n['icon']) ?>"></i>
              </button>
              <div class="face-label-sub mt-2">
                <span class="badge rounded-pill text-bg-light border fw-bold px-2 py-1"><?= (int)$n['n'] ?></span>
                <small class="d-block text-dark fw-bold mt-1 fs-8"><?= e($shortLabel) ?></small>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div id="feedback-dinamico" class="alert alert-success text-center d-none fw-bold fs-6 mb-4"></div>

    <div class="d-grid">
      <button class="btn btn-vuela btn-lg py-3 fs-5 shadow-lg fw-bold d-flex align-items-center justify-content-center gap-2" type="submit" id="btnSubmit">
        <i class="fa-solid fa-paper-plane"></i>
        <span>Enviar Mi Calificación</span>
      </button>
    </div>
  </form>
</div>


<script>
(function() {
  var form = document.getElementById('form-calificar-public');
  var scoreInput = document.getElementById('score');
  var feedback = document.getElementById('feedback-dinamico');

  document.querySelectorAll('.face-card-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.face-card-btn').forEach(function(b) {
        b.classList.remove('selected', 'anim-happy', 'anim-sad');
      });
      btn.classList.add('selected');
      var score = btn.getAttribute('data-score');
      var msg = btn.getAttribute('data-msg');
      var icon = btn.getAttribute('data-icon');
      scoreInput.value = score;
      if (feedback) {
        feedback.classList.remove('d-none');
        feedback.innerHTML = '<i class="fa-solid ' + (icon || 'fa-star') + ' me-2"></i>' + msg;
      }
    });
  });

  if (form) {
    form.addEventListener('submit', function(ev) {
      if (!scoreInput.value) {
        ev.preventDefault();
        // Mostrar mensaje sin alert()
        var errBox = document.getElementById('public-err-msg');
        if (!errBox) {
          errBox = document.createElement('div');
          errBox.id = 'public-err-msg';
          errBox.className = 'alert alert-danger text-center fw-bold mb-3 bounce-in';
          form.insertBefore(errBox, form.firstChild);
        }
        errBox.textContent = '⚠️ Por favor selecciona una carita para calificar.';
        errBox.style.display = 'block';
        setTimeout(function() { errBox.style.display = 'none'; }, 3500);
        return;
      }
      // Reset la carita seleccionada visualmente antes de enviar
      document.querySelectorAll('.face-card-btn').forEach(function(b) {
        b.classList.remove('selected', 'anim-happy', 'anim-sad');
      });
    });
  }

  // Bloqueo estricto del botón "Volver atrás" del navegador
  if (window.history && window.history.pushState) {
    window.history.pushState(null, null, window.location.href);
    window.onpopstate = function () {
      window.history.pushState(null, null, window.location.href);
    };
  }

  // Si el usuario intenta presionar "Atrás" desde el navegador después de enviar, detectamos BF-Cache
  window.addEventListener('pageshow', function (event) {
    if (event.persisted || (window.performance && (window.performance.navigation.type === 2 || (window.performance.getEntriesByType && window.performance.getEntriesByType('navigation')[0] && window.performance.getEntriesByType('navigation')[0].type === 'back_forward')))) {
      window.location.reload();
    }
  });
})();
</script>
</body>
</html>
