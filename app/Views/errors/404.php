<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>No encontrado</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e(asset('css/vuela.css')) ?>" rel="stylesheet">
</head>
<body class="d-flex align-items-center min-vh-100">
  <div class="container text-center">
    <img src="<?= e(asset('img/logo-vuela.png')) ?>" height="64" alt="Vuela">
    <h1 class="mt-3">Página no encontrada</h1>
    <p class="text-muted">Use el menú del sistema. El botón atrás no recupera formularios ya enviados.</p>
    <a class="btn btn-vuela" href="<?= e(url('/')) ?>">Ir al inicio</a>
  </div>
</body>
</html>
