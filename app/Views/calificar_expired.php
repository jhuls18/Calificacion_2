<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title>Enlace Expirado - Vuela Internet</title>
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
    .expired-card {
      max-width: 520px;
      width: 100%;
      border-radius: 28px;
      background: #ffffff;
      box-shadow: 0 25px 80px rgba(0, 0, 0, 0.12);
      border: 1px solid rgba(239, 68, 68, 0.2);
    }
  </style>
</head>
<body>

<div class="expired-card p-4 p-md-5 text-center bounce-in">
  <img src="<?= e(asset('img/logo-vuela.png')) ?>" height="52" alt="Vuela Internet" class="mb-3">
  
  <div class="display-1 text-danger mb-3">
    <i class="fa-solid fa-clock-rotate-left"></i>
  </div>

  <h3 class="fw-extrabold text-dark mb-2">Enlace de Calificación Expirado / Utilizado</h3>
  <p class="text-muted mb-4 fs-6">
    <?= e($error ?? 'Este enlace de evaluación caducó tras 5 minutos o ya fue utilizado para enviar una calificación.') ?>
  </p>

  <div class="alert alert-warning border-0 small py-3 px-4 rounded-4 mb-4">
    <i class="fa-solid fa-shield-cat text-warning me-2 fs-5"></i>
    <span>Los enlaces de calificación son estrictamente temporales (5 min) y de un solo uso por seguridad.</span>
  </div>

  <a href="https://www.vuela.bo/" target="_blank" rel="noopener" class="btn btn-vuela btn-lg rounded-pill px-4 py-3 fw-bold d-inline-flex align-items-center gap-2 shadow-lg hover-glow-btn">
    <i class="fa-solid fa-globe fs-5"></i>
    <span>Visitar Sitio Web Oficial www.vuela.bo</span>
    <i class="fa-solid fa-arrow-right ms-1"></i>
  </a>
</div>

<script>
  if (window.history && window.history.pushState) {
    window.history.pushState(null, null, window.location.href);
    window.history.pushState(null, null, window.location.href);
    window.onpopstate = function () {
      window.history.pushState(null, null, window.location.href);
    };
  }
</script>

</body>
</html>
