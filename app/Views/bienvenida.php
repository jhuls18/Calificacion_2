<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <title>Bienvenida · Vuela Internet</title>

  <!-- Script de Activación Inmediata de Sesión de Pestaña -->
  <script>
    if (window.sessionStorage) {
      sessionStorage.setItem('vuela_tab_token', <?= json_encode($_SESSION['tab_auth_token'] ?? '') ?>);
      sessionStorage.setItem('vuela_tab_session', '1');
    }
  </script>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="<?= e(asset('css/vuela.css')) ?>" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #fffaf6 0%, #fff0e8 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  </style>
</head>
<body>

<div class="container text-center p-3">
  <div class="card card-vuela p-4 p-md-5 text-center mx-auto bounce-in" style="max-width: 500px; border-radius: 32px; background: #ffffff; box-shadow: 0 25px 80px rgba(241, 90, 34, 0.18);">
    
    <div class="mb-4">
      <img src="<?= e(asset('img/logo-vuela.png')) ?>" alt="Vuela Internet" height="68" class="hover-glow">
    </div>

    <div class="user-avatar-welcome mb-3">
      <?= user_avatar($user, 76, 'shadow-lg border border-3 border-white mx-auto') ?>
    </div>

    <h1 class="fw-extrabold text-dark mb-1 fs-2">Hola de nuevo a Vuela Internet</h1>
    <h2 class="fw-bold mb-2" style="color:var(--vuela)"><?= e($user['nombre'] ?? 'Agente') ?></h2>
    
    <p class="text-muted small mb-4">
      <span class="badge badge-vuela px-3 py-1"><?= e($user['rol'] ?? 'ATC') ?></span> · Internet de verdad
    </p>

    <div>
      <a class="btn btn-vuela btn-lg px-5 py-3 fw-bold shadow-lg rounded-pill hover-glow-btn" href="<?= e(url('/panel')) ?>" id="btnContinuarBienvenida">
        <span>Continuar</span>
        <i class="fa-solid fa-arrow-right ms-2 fs-6"></i>
      </a>
    </div>
  </div>
</div>

<script src="<?= e(asset('js/app.js')) ?>"></script>
<script>
  history.replaceState(null, '', <?= json_encode(url('/bienvenida')) ?>);
  setTimeout(function () {
    window.location.replace(<?= json_encode(url('/panel')) ?>);
  }, 2200);
</script>
</body>
</html>
