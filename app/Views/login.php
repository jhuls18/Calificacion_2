<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-store">
  <title>Ingreso · Vuela Internet</title>
  <link rel="icon" href="<?= e(asset('img/logo-vuela.png')) ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="<?= e(asset('css/vuela.css')) ?>" rel="stylesheet">
</head>
<body>
<?php $splashText = 'vuela internet'; require APP_PATH . '/Views/partials/splash.php'; ?>

<!-- Shapes de fondo animados -->
<div class="bg-shapes">
  <div class="shape shape-1"></div>
  <div class="shape shape-2"></div>
  <div class="shape shape-3"></div>
</div>

<div class="container login-shell">
  <div class="row justify-content-center w-100 position-relative" style="z-index: 5;">
    <div class="col-lg-9 col-xl-8">
      <div class="card card-vuela login-card">
        
        <!-- Botón discreto para login de Admin (Esquina Superior Derecha) -->
        <button type="button" class="btn-secret-admin" id="btnAdminSecret" title="Acceso Administrativo" aria-label="Acceso Administrador">
          <i class="fa-solid fa-lock-keyhole" id="secretAdminIcon"></i>
        </button>

        <div class="row g-0">
          <!-- Lateral Izquierdo Vuela -->
          <div class="col-md-5 login-side">
            <div class="login-side-glow"></div>
            <div class="login-brand-wrap">
              <img src="<?= e(asset('img/logo-gota.png')) ?>" alt="Vuela Logo" class="brand-logo-img">
              <h2 class="fw-extrabold mt-3 mb-1 text-white">Portal de Atención</h2>
              <p class="mb-4 opacity-85 text-white-50 fs-6">Sistema de Calificación en Línea</p>
              
              <div class="vuela-features d-none d-md-flex flex-column gap-2 mt-2">
                <div class="feature-chip"><i class="fa-solid fa-bolt text-warning me-2"></i> Rápido e instantáneo</div>
                <div class="feature-chip"><i class="fa-solid fa-mobile-screen-button me-2"></i> PC, Tablet y Celular</div>
                <div class="feature-chip"><i class="fa-solid fa-users me-2"></i> Multiusuario en vivo</div>
              </div>
            </div>
          </div>

          <!-- Formulario Principal (Agentes) -->
          <div class="col-md-7 p-4 p-md-5 position-relative d-flex flex-column justify-content-center" id="mainLoginFormBox">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div>
                <span class="badge badge-vuela mb-2"><i class="fa-solid fa-user-check me-1"></i> Acceso Agentes</span>
                <h3 class="fw-extrabold mb-1 title-heading">Iniciar sesión</h3>
                <p class="text-muted small mb-0">Ingresa tus datos para acceder al sistema.</p>
              </div>
            </div>

            <?php if (!empty($error)): ?>
              <div class="alert alert-danger alert-vuela d-flex align-items-center mb-3">
                <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i>
                <div><?= e($error) ?></div>
              </div>
            <?php endif; ?>

            <form method="post" action="<?= e(url('/login')) ?>" data-once id="formLoginUser">
              <?= csrf_field() ?>
              
              <!-- Campo Único: Usuario -->
              <div class="mb-3">
                <label class="form-label fw-bold text-dark fs-7 mb-1">Usuario</label>
                <div class="input-group-vuela">
                  <span class="input-icon"><i class="fa-solid fa-user"></i></span>
                  <input class="form-control form-control-vuela" name="usuario" required placeholder="Ingresa tu usuario" autocomplete="username">
                </div>
              </div>

              <!-- Rol Fijo: ATC (Atención al Cliente) -->
              <input type="hidden" name="rol" value="ATC">
              <div class="mb-3">
                <span class="badge text-bg-light border px-3 py-2 fw-bold text-dark w-100 text-start">
                  <i class="fa-solid fa-headset text-vuela me-2"></i> Rol de Agente: <strong>ATC (Atención al Cliente)</strong>
                </span>
              </div>

              <!-- Contraseña -->
              <div class="mb-4">
                <label class="form-label fw-bold text-dark fs-7 mb-1">Contraseña</label>
                <div class="input-group-vuela">
                  <span class="input-icon"><i class="fa-solid fa-key"></i></span>
                  <input class="form-control form-control-vuela" type="password" name="password" id="userPassInput" required placeholder="••••••••" autocomplete="current-password">
                  <button type="button" class="btn-pass-toggle" data-target="userPassInput"><i class="fa-regular fa-eye"></i></button>
                </div>
              </div>

              <button class="btn btn-vuela btn-lg w-100 py-3 d-flex align-items-center justify-content-center gap-2" type="submit">
                <span>Entrar al sistema</span>
                <i class="fa-solid fa-arrow-right fs-6"></i>
              </button>
            </form>
          </div>

          <!-- Formulario Oculto: Login Administrador (Mini Modal / Card Overlay) -->
          <div class="col-md-7 p-4 p-md-5 position-relative d-none flex-column justify-content-center bg-admin-panel" id="adminLoginFormBox">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div>
                <span class="badge badge-admin-dark mb-2"><i class="fa-solid fa-shield-halved me-1"></i> Modo Administrador</span>
                <h3 class="fw-extrabold mb-1 text-dark title-heading">Acceso de Control</h3>
                <p class="text-muted small mb-0">Panel exclusivo para la administración del sistema.</p>
              </div>
            </div>

            <form method="post" action="<?= e(url('/login')) ?>" data-once id="formLoginAdmin">
              <?= csrf_field() ?>
              <input type="hidden" name="rol" value="Administrador">

              <div class="mb-3">
                <label class="form-label fw-bold text-dark fs-7 mb-1">Usuario Admin</label>
                <div class="input-group-vuela">
                  <span class="input-icon text-danger"><i class="fa-solid fa-user-shield"></i></span>
                  <input class="form-control form-control-vuela border-danger-subtle" name="usuario" value="admin" required placeholder="Usuario administrador" autocomplete="username">
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label fw-bold text-dark fs-7 mb-1">Contraseña Administrador</label>
                <div class="input-group-vuela">
                  <span class="input-icon text-danger"><i class="fa-solid fa-lock"></i></span>
                  <input class="form-control form-control-vuela border-danger-subtle" type="password" name="password" id="adminPassInput" required placeholder="••••••••" autocomplete="current-password">
                  <button type="button" class="btn-pass-toggle" data-target="adminPassInput"><i class="fa-regular fa-eye"></i></button>
                </div>
              </div>

              <button class="btn btn-admin-submit btn-lg w-100 py-3 d-flex align-items-center justify-content-center gap-2 mb-3" type="submit">
                <i class="fa-solid fa-key me-1"></i>
                <span>Ingresar como Admin</span>
              </button>

              <button type="button" class="btn btn-link text-muted text-decoration-none w-100 btn-sm" id="btnBackToUserLogin">
                <i class="fa-solid fa-arrow-left me-1"></i> Volver a ingreso de agentes
              </button>
            </form>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<script src="<?= e(asset('js/app.js')) ?>"></script>
<script>hideSplash('splash', 1400);</script>
</body>
</html>
