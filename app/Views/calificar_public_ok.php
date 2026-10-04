<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title>¡Gracias por tu Calificación! - Vuela Internet</title>
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
    .ok-card {
      max-width: 580px;
      width: 100%;
      border-radius: 28px;
      background: #ffffff;
      box-shadow: 0 25px 80px rgba(241, 90, 34, 0.2);
      border: 1px solid rgba(241, 90, 34, 0.15);
    }
  </style>
</head>
<body>

<div class="ok-card p-4 p-md-5 text-center bounce-in">
  <img src="<?= e(asset('img/logo-vuela.png')) ?>" height="55" alt="Vuela Internet" class="mb-3">

  <?php $buena = !empty($rating['positivo']); ?>
  <div class="display-1 mb-3" style="color:<?= e($rating['color'] ?? '#F15A22') ?>">
    <i class="fa-solid <?= e($rating['icono'] ?? 'fa-circle-check') ?>"></i>
  </div>

  <h2 class="fw-extrabold text-dark mb-2"><?= $buena ? '¡Gracias por volar con nosotros!' : 'Registramos su valoración' ?></h2>
  <p class="fs-5 text-muted mb-3"><?= e($rating['mensaje'] ?? 'Agradecemos tu tiempo para evaluar nuestra atención.') ?></p>

  <div class="p-3 bg-light rounded-4 border d-inline-block mb-4 text-start" style="width: 100%; max-width: 420px;">
    <div class="d-flex justify-content-between small text-muted mb-1">
      <span>Agente evaluado:</span>
      <strong class="text-dark"><?= e($agentName ?? $rating['agente_nombre'] ?? 'Agente') ?></strong>
    </div>
    <div class="d-flex justify-content-between small text-muted mb-1">
      <span>Calificación asignada:</span>
      <strong class="text-vuela"><?= (int)($rating['score'] ?? 0) ?> / <?= (int)($rating['score_max'] ?? 5) ?> (<?= e($rating['etiqueta'] ?? '') ?>)</strong>
    </div>
    <div class="d-flex justify-content-between small text-muted">
      <span>Cuestionario / Fecha:</span>
      <span class="fw-semibold text-dark"><?= e($rating['encuesta_tipo'] ?? 'Atención General') ?></span>
    </div>
  </div>

  <div class="alert alert-success border-0 small py-3 px-4 rounded-4 mb-4">
    <i class="fa-solid fa-heart text-danger me-2 fs-5"></i>
    <span>Tu respuesta ha sido registrada exitosamente en el sistema de Vuela Internet. ¡Que tengas un excelente día!</span>
  </div>

  <!-- Botón oficial a Vuela.bo -->
  <div class="mt-2">
    <a href="https://www.vuela.bo/" target="_blank" rel="noopener" class="btn btn-vuela btn-lg rounded-pill px-4 py-3 fw-bold d-inline-flex align-items-center gap-2 shadow-lg hover-glow-btn">
      <i class="fa-solid fa-globe fs-5"></i>
      <span>Visitar Sitio Web Oficial www.vuela.bo</span>
      <i class="fa-solid fa-arrow-right ms-1"></i>
    </a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
<script>
  if (typeof confetti === 'function') {
    confetti({ particleCount: 70, spread: 70, origin: { y: 0.6 } });
  }

  // 1. Bloqueo estricto del botón "Volver atrás" del navegador o buscador
  if (window.history && window.history.pushState) {
    window.history.pushState(null, null, window.location.href);
    window.history.pushState(null, null, window.location.href);
    window.onpopstate = function () {
      window.history.pushState(null, null, window.location.href);
    };
  }

  // 2. Prevenir BF-Cache (Back-Forward Cache): Si el usuario intenta ir hacia atrás desde la caché del navegador, fuerza recarga
  window.addEventListener('pageshow', function (event) {
    if (event.persisted || (window.performance && (window.performance.navigation.type === 2 || (window.performance.getEntriesByType && window.performance.getEntriesByType('navigation')[0] && window.performance.getEntriesByType('navigation')[0].type === 'back_forward')))) {
      window.location.reload();
    }
  });
</script>

</body>
</html>
