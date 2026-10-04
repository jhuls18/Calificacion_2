<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <title><?= e($title ?? APP_TITLE) ?> · <?= e(APP_NAME) ?></title>
  <link rel="icon" href="<?= e(asset('img/logo-vuela.png')) ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="<?= e(asset('css/vuela.css')) ?>" rel="stylesheet">
  <!-- Guard Estricto de Cierre de Pestaña/Navegador -->
  <script>
    (function() {
      var isAuthUser = <?= !empty($user) ? 'true' : 'false' ?>;
      var serverToken = <?= json_encode($_SESSION['tab_auth_token'] ?? '') ?>;
      var isNewSession = <?= !empty($_SESSION['is_new_session']) ? 'true' : 'false' ?>;

      if (window.sessionStorage && isAuthUser) {
        var localToken = sessionStorage.getItem('vuela_tab_token');
        if (isNewSession) {
          sessionStorage.setItem('vuela_tab_token', serverToken);
          <?php $_SESSION['is_new_session'] = false; ?>
        } else if (!localToken || (serverToken && localToken !== serverToken)) {
          // La pestaña o el navegador fue cerrado previamente. Forzar salida inmediata.
          sessionStorage.removeItem('vuela_tab_token');
          window.location.replace('<?= url('/logout') ?>');
          return;
        }
      }

      // Detector de Cierre de Pestaña / Navegador para matar la sesión en servidor en tiempo real
      var isInternalNav = false;
      document.addEventListener('click', function(e) {
        if (e.target.closest('a, button, form, input[type="submit"]')) {
          isInternalNav = true;
        }
      });
      window.addEventListener('beforeunload', function() {
        // Si no fue un clic interno (ej. cerró la pestaña X o la ventana), enviar beacon de logout
        if (!isInternalNav) {
          try {
            if (navigator.sendBeacon) {
              navigator.sendBeacon('<?= url('/logout') ?>');
            }
          } catch(err) {}
        }
      });
    })();
  </script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-vuela sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= e(url('/panel')) ?>">
      <img src="<?= e(asset('img/logo-vuela.png')) ?>" alt="Vuela" height="42">
      <span>
        <span class="brand-text d-block">Vuela Internet</span>
        <span class="tag">INTERNET DE VERDAD</span>
      </span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
        <li class="nav-item"><a class="nav-link" href="<?= e(url('/panel')) ?>"><i class="fa-solid fa-house me-1"></i>Panel</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(url('/calificar')) ?>"><i class="fa-solid fa-face-smile me-1"></i>Calificar</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(url('/atencion')) ?>"><i class="fa-solid fa-headset me-1"></i>Atención</a></li>
        <?php if (is_admin($user ?? null)): ?>
          <li class="nav-item"><a class="nav-link" href="<?= e(url('/admin/reportes')) ?>">Reportes</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= e(url('/admin/ranking')) ?>">Ranking</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= e(url('/admin/usuarios')) ?>">Usuarios</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= e(url('/admin/config')) ?>">Config</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= e(url('/admin/formulario')) ?>">Formulario</a></li>
        <?php endif; ?>
        
        <li class="nav-item ms-lg-2">
          <span class="badge badge-rol rounded-pill px-3 py-1 d-inline-flex align-items-center gap-2">
            <?= user_avatar($user, 26) ?>
            <span><?= e(($user['nombre'] ?? '') . ' · ' . ($user['rol'] ?? '')) ?></span>
          </span>
        </li>


        <li class="nav-item">
          <form method="post" action="<?= e(url('/logout')) ?>">
            <?= csrf_field() ?>
            <button class="btn btn-sm btn-ghost">Salir</button>
          </form>
        </li>
      </ul>
    </div>
  </div>
</nav>

<main class="page-wrap container">
  <?php require $viewFile; ?>
</main>

<!-- Botón Flotante Asistente Guía -->
<button type="button" class="btn-floating-assistant" data-bs-toggle="modal" data-bs-target="#assistantGuideModal" title="Abrir Guía del Sistema">
  <i class="fa-solid fa-robot"></i>
  <span class="pulse-ring"></span>
</button>

<!-- Botón Flotante Usuarios Conectados (Borde Derecho) -->
<button type="button" class="btn-floating-online" data-bs-toggle="offcanvas" data-bs-target="#offcanvasOnlineUsers" title="Usuarios Conectados al Mismo Tiempo">
  <span class="live-dot-pulse"></span>
  <i class="fa-solid fa-users"></i>
  <span>Conectados</span>
  <span class="online-badge-count" id="floatingOnlineCount">1</span>
</button>

<!-- PANEL LATERAL USUARIOS CONECTADOS EN TIEMPO REAL -->
<div class="offcanvas offcanvas-end style-online-offcanvas border-0 shadow-lg" tabindex="-1" id="offcanvasOnlineUsers" aria-labelledby="offcanvasOnlineUsersLabel">
  <div class="offcanvas-header bg-dark text-white p-3 px-4">
    <div class="d-flex align-items-center gap-2">
      <span class="live-dot-pulse me-1"></span>
      <div>
        <h5 class="offcanvas-title fw-bold" id="offcanvasOnlineUsersLabel">Usuarios Conectados</h5>
        <small class="text-white-50 d-block" style="font-size:0.75rem;">Actualización automática cada 10s</small>
      </div>
    </div>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>
  <div class="offcanvas-body p-3">
    <div class="d-flex align-items-center justify-content-between mb-3 px-1">
      <span class="text-muted small fw-bold"><i class="fa-solid fa-signal text-success me-1"></i>En línea ahora:</span>
      <span class="badge text-bg-success rounded-pill px-3 py-1" id="offcanvasOnlineTotal">1 usuario(s)</span>
    </div>

    <!-- Lista de Usuarios Conectados -->
    <div id="onlineUsersList" class="d-flex flex-column gap-2">
      <div class="text-center py-4 text-muted">
        <div class="spinner-border spinner-border-sm text-vuela mb-2" role="status"></div>
        <div class="small">Cargando conectados...</div>
      </div>
    </div>
  </div>
  <div class="offcanvas-footer bg-light p-3 border-top text-center">
    <div class="d-flex align-items-center justify-content-between">
      <small class="text-muted" style="font-size:0.78rem;">
        <i class="fa-solid fa-arrows-rotate text-vuela me-1"></i>Refresco en: <strong id="onlineCountdown">10</strong>s
      </small>
      <button type="button" class="btn btn-sm btn-outline-primary py-1 px-3 fw-bold" onclick="refreshOnlineUsersManually()">
        <i class="fa-solid fa-rotate me-1"></i> Actualizar Ahora
      </button>
    </div>
  </div>
</div>

<?php
$pageKey = 'panel';
if (isset($viewFile)) {
  if (str_contains($viewFile, 'calificar')) $pageKey = 'calificar';
  elseif (str_contains($viewFile, 'atencion')) $pageKey = 'atencion';
  elseif (str_contains($viewFile, 'reportes')) $pageKey = 'reportes';
  elseif (str_contains($viewFile, 'ranking')) $pageKey = 'ranking';
  elseif (str_contains($viewFile, 'usuarios')) $pageKey = 'usuarios';
  elseif (str_contains($viewFile, 'config')) $pageKey = 'config';
  elseif (str_contains($viewFile, 'formulario')) $pageKey = 'formulario';
}
?>
<script>window.VUELA_CURRENT_PAGE = <?= json_encode($pageKey) ?>;</script>

<!-- Modal Asistente Interactivo Dinámico -->
<div class="modal fade" id="assistantGuideModal" tabindex="-1" aria-labelledby="assistantGuideModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg style-assistant-modal" style="border-radius:24px; overflow:hidden;">
      <div class="modal-header border-0 bg-dark text-white p-4 align-items-center">
        <div class="d-flex align-items-center gap-3">
          <div class="assistant-avatar">
            <i class="fa-solid fa-robot fs-3 text-warning"></i>
          </div>
          <div>
            <h4 class="fw-extrabold mb-0" id="assistantGuideModalLabel">🤖 Asistente Virtual Vuela</h4>
            <p class="text-white-50 small mb-0">Guía interactiva paso a paso e independiente para cada módulo</p>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body p-4 p-md-5">
        <!-- Navegación de pestañas dentro del asistente -->
        <div class="assistant-nav-tabs d-flex flex-wrap gap-2 justify-content-center mb-4 p-2 bg-light rounded-4 border">
          <button type="button" class="btn btn-sm btn-assistant-tab active" data-tab="panel"><i class="fa-solid fa-house me-1"></i>Panel</button>
          <button type="button" class="btn btn-sm btn-assistant-tab" data-tab="calificar"><i class="fa-solid fa-face-smile me-1"></i>Calificar</button>
          <button type="button" class="btn btn-sm btn-assistant-tab" data-tab="atencion"><i class="fa-solid fa-headset me-1"></i>Atención</button>
          <?php if (is_admin($user ?? null)): ?>
            <button type="button" class="btn btn-sm btn-assistant-tab" data-tab="reportes"><i class="fa-solid fa-chart-pie me-1"></i>Reportes</button>
            <button type="button" class="btn btn-sm btn-assistant-tab" data-tab="ranking"><i class="fa-solid fa-ranking-star me-1"></i>Ranking</button>
            <button type="button" class="btn btn-sm btn-assistant-tab" data-tab="usuarios"><i class="fa-solid fa-users me-1"></i>Usuarios</button>
            <button type="button" class="btn btn-sm btn-assistant-tab" data-tab="config"><i class="fa-solid fa-sliders me-1"></i>Config</button>
            <button type="button" class="btn btn-sm btn-assistant-tab" data-tab="formulario"><i class="fa-solid fa-file-contract me-1"></i>Formulario</button>
          <?php endif; ?>
        </div>

        <!-- Contenidos Explicativos de cada Pestaña -->
        <div id="assistantStepContent">
          
          <!-- Pestaña: Panel -->
          <div class="assistant-tab-card d-block" id="tabCard_panel">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <span class="badge text-bg-warning px-3 py-2 rounded-pill fw-bold"><i class="fa-solid fa-house me-1"></i> MÓDULO: PANEL PRINCIPAL</span>
              <span class="badge text-bg-primary px-3 py-2 rounded-pill d-none" id="currentBadge_panel">📍 Pestaña Actual</span>
            </div>
            <h4 class="fw-bold text-dark mb-2"><i class="fa-solid fa-chart-line text-vuela me-2"></i>Centro de Control y Métricas</h4>
            <p class="text-muted fs-6 mb-3">En esta pestaña monitoreas tus indicadores de atención personales y las métricas generales de Vuela Internet:</p>
            
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-cubes text-primary me-2"></i>Tarjetas de KPIs Personales</h6>
                  <ul class="small text-muted ps-3 mb-0">
                    <li><strong>Tus Atenciones:</strong> Total de calificaciones registradas bajo tu usuario activo.</li>
                    <li><strong>Tus Puntos:</strong> Puntuación acumulada en el ranking (+1 por voto positivo, -1 por reclamo).</li>
                    <li><strong>Total Votos Sistema:</strong> Mapeo global de evaluaciones recibidas.</li>
                    <li><strong>Escala Activa:</strong> Niveles configurados actualmente (ej. 4 caritas).</li>
                  </ul>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-temperature-half text-danger me-2"></i>Termómetro de Satisfacción</h6>
                  <p class="small text-muted mb-2">Medidor dinámico del 0% al 100% que refleja la calidad de atención promedio del equipo.</p>
                  <span class="badge text-bg-success me-1">Excelente > 80%</span>
                  <span class="badge text-bg-warning text-dark me-1">Aceptable 60-80%</span>
                  <span class="badge text-bg-danger">Crítico < 60%</span>
                </div>
              </div>
            </div>

            <div class="p-3 border rounded-3 bg-light">
              <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-ranking-star text-warning me-2"></i>Resumen de Ranking de Agentes en Vivo</h6>
              <p class="small text-muted mb-0">Muestra la posición actual de los asesores con mayor nivel de satisfacción del usuario en la jornada.</p>
            </div>
          </div>

          <!-- Pestaña: Calificar -->
          <div class="assistant-tab-card d-none" id="tabCard_calificar">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <span class="badge text-bg-warning px-3 py-2 rounded-pill fw-bold"><i class="fa-solid fa-face-smile me-1"></i> MÓDULO: PANTALLA DE CALIFICACIÓN</span>
              <span class="badge text-bg-primary px-3 py-2 rounded-pill d-none" id="currentBadge_calificar">📍 Pestaña Actual</span>
            </div>
            <h4 class="fw-bold text-dark mb-2"><i class="fa-solid fa-star text-warning me-2"></i>Evaluación de Atención para Clientes</h4>
            <p class="text-muted fs-6 mb-3">Diseñado para ser desplegado en tabletas o monitores de cara al cliente en mostradores y cajas:</p>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-id-card text-primary me-2"></i>1. Registro de Cliente</h6>
                  <p class="small text-muted mb-1">Escriba el código o CI del cliente. Si la consulta es rápida o no requiere registro:</p>
                  <button type="button" class="btn btn-sm btn-outline-secondary disabled py-0 px-2 fw-bold"><i class="fa-solid fa-user-check me-1"></i>Visitante / Directo</button>
                  <p class="small text-muted mt-1 mb-0">Asigna automáticamente `VISITANTE` con un solo toque.</p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-display text-success me-2"></i>2. Modo Kiosco Pantalla Completa</h6>
                  <p class="small text-muted mb-1">Presione el botón superior:</p>
                  <span class="badge text-bg-dark"><i class="fa-solid fa-expand me-1"></i>Modo Pantalla Completa</span>
                  <p class="small text-muted mt-1 mb-0">Oculta barras y navegación para uso público seguro.</p>
                </div>
              </div>
            </div>

            <div class="p-3 border rounded-3 bg-light">
              <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-icons text-vuela me-2"></i>3. Espectro de Caritas Interactivas</h6>
              <p class="small text-muted mb-0">Dispone de caritas horizontales con 1 sola palabra corta (Malo, Regular, Aceptable, Bueno, Excelente). Al presionar, muestra una confirmación animada al cliente sin reiniciar abruptamente la interfaz.</p>
            </div>
          </div>

          <!-- Pestaña: Atención -->
          <div class="assistant-tab-card d-none" id="tabCard_atencion">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <span class="badge text-bg-warning px-3 py-2 rounded-pill fw-bold"><i class="fa-solid fa-headset me-1"></i> MÓDULO: HERRAMIENTAS DE ATENCIÓN</span>
              <span class="badge text-bg-primary px-3 py-2 rounded-pill d-none" id="currentBadge_atencion">📍 Pestaña Actual</span>
            </div>
            <h4 class="fw-bold text-dark mb-2"><i class="fa-solid fa-calculator text-info me-2"></i>Calculadora de Penalidades e Información Oficial</h4>
            <p class="text-muted fs-6 mb-3">Herramientas esenciales para el agente durante la cancelación o resolución de dudas de clientes:</p>

            <div class="row g-3 mb-3">
              <div class="col-md-12">
                <div class="p-3 border rounded-3 bg-light">
                  <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-calendar-days text-vuela me-2"></i>Calculadora de Penalidad (Contrato 18 Meses)</h6>
                  <ul class="small text-muted ps-3 mb-2">
                    <li><strong>Monto Fijo de Multa:</strong> Tarifa estandarizada de <strong>159 Bs.</strong> por mes faltante.</li>
                    <li><strong>Fecha de Instalación / Fecha de Baja:</strong> Selecciona el mes y año de inicio y retiro.</li>
                    <li><strong>Bloqueo Temporal (1 o 2 Meses):</strong> Si el usuario solicitó congelamiento de servicio, selecciona el beneficio para deducir esos meses automáticamente del cálculo final.</li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-building text-warning me-2"></i>Oficina Central El Alto</h6>
                  <p class="small text-muted mb-1"><strong>Dirección:</strong> Av. Litoral esq. Carabuco N° 6066 (Frente a la Parroquia Madre Ignacia Nazaria).</p>
                  <p class="small text-muted mb-0"><strong>Horarios:</strong> Lun - Vie: 08:30 a 16:30 | Sáb: 08:00 a 12:00.</p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-comments text-success me-2"></i>Protocolo CALMA</h6>
                  <p class="small text-muted mb-0">Plantillas de chat instantáneas. Haz clic en <strong>📋 Copiar Mensaje</strong> para enviar la respuesta preaprobada al cliente.</p>
                </div>
              </div>
            </div>
          </div>

          <?php if (is_admin($user ?? null)): ?>
          <!-- Pestaña: Reportes -->
          <div class="assistant-tab-card d-none" id="tabCard_reportes">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <span class="badge text-bg-warning px-3 py-2 rounded-pill fw-bold"><i class="fa-solid fa-chart-pie me-1"></i> MÓDULO: REPORTES Y EXPORTACIÓN</span>
              <span class="badge text-bg-primary px-3 py-2 rounded-pill d-none" id="currentBadge_reportes">📍 Pestaña Actual</span>
            </div>
            <h4 class="fw-bold text-dark mb-2"><i class="fa-solid fa-chart-pie text-danger me-2"></i>Análisis Gráfico y Descarga de Datos</h4>
            <p class="text-muted fs-6 mb-3">Módulo para la auditoría gerencial de votaciones y filtros en tiempo real:</p>

            <ul class="list-group list-group-flush mb-3">
              <li class="list-group-item border-0 bg-light mb-2 rounded-3">
                <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-chart-pie text-primary me-2"></i>Gráfico de Dona (Doughnut Chart)</h6>
                <p class="small text-muted mb-0">Visualiza la proporción porcentual exacta de votos recibidos por cada rango de calidad.</p>
              </li>
              <li class="list-group-item border-0 bg-light mb-2 rounded-3">
                <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-filter text-vuela me-2"></i>Filtros Chips y Buscador Directo</h6>
                <p class="small text-muted mb-0">Usa las pestañas <span class="badge text-bg-secondary">Excelente 5⭐</span>, <span class="badge text-bg-secondary">Bueno 4⭐</span>, <span class="badge text-bg-secondary">Regular 3⭐</span>, <span class="badge text-bg-secondary">Malo 1-2⭐</span> o el buscador por texto para auditar casos en segundos.</p>
              </li>
              <li class="list-group-item border-0 bg-light rounded-3">
                <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-file-excel text-success me-2"></i>Descarga y Respaldo en la Nube</h6>
                <p class="small text-muted mb-0">Exporta en archivo Excel local o accede directamente al Google Sheets sincronizado en vivo.</p>
              </li>
            </ul>
          </div>

          <!-- Pestaña: Ranking -->
          <div class="assistant-tab-card d-none" id="tabCard_ranking">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <span class="badge text-bg-warning px-3 py-2 rounded-pill fw-bold"><i class="fa-solid fa-ranking-star me-1"></i> MÓDULO: RANKING DE AGENTES</span>
              <span class="badge text-bg-primary px-3 py-2 rounded-pill d-none" id="currentBadge_ranking">📍 Pestaña Actual</span>
            </div>
            <h4 class="fw-bold text-dark mb-2"><i class="fa-solid fa-trophy text-warning me-2"></i>Tabla de Posiciones e Incentivos</h4>
            <p class="text-muted fs-6 mb-3">Sistema de gamificación para premiar la calidad de atención de los agentes y freelancers:</p>

            <div class="p-3 border rounded-3 bg-light mb-3">
              <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-calculator text-vuela me-2"></i>Fórmula de Puntuación Neto</h6>
              <div class="d-flex align-items-center gap-2 mt-2">
                <span class="badge text-bg-success px-3 py-2">+1 Punto (Excelente / Bueno)</span>
                <span class="badge text-bg-danger px-3 py-2">-1 Punto (Malo)</span>
                <span class="badge text-bg-secondary px-3 py-2">0 Puntos (Regular)</span>
              </div>
            </div>

            <div class="p-3 border rounded-3 bg-light">
              <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-medal text-warning me-2"></i>Podio de Honor (Top 3)</h6>
              <p class="small text-muted mb-0">Resalta con medallas de Oro 🥇, Plata 🥈 y Bronce 🥉 a los mejores profesionales del periodo.</p>
            </div>
          </div>

          <!-- Pestaña: Usuarios -->
          <div class="assistant-tab-card d-none" id="tabCard_usuarios">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <span class="badge text-bg-warning px-3 py-2 rounded-pill fw-bold"><i class="fa-solid fa-users me-1"></i> MÓDULO: GESTIÓN DE USUARIOS</span>
              <span class="badge text-bg-primary px-3 py-2 rounded-pill d-none" id="currentBadge_usuarios">📍 Pestaña Actual</span>
            </div>
            <h4 class="fw-bold text-dark mb-2"><i class="fa-solid fa-user-gear text-primary me-2"></i>Administración de Personal y Roles</h4>
            <p class="text-muted fs-6 mb-3">Control total para crear nuevos accesos y monitorear la actividad de los usuarios:</p>

            <div class="row g-3">
              <div class="col-md-6">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-user-plus text-success me-2"></i>Crear Operador / Freelancer</h6>
                  <p class="small text-muted mb-0">Permite registrar usuarios asignando nombre completo, alias de acceso, clave cifrada y su rol respectivo (<em>ATC, Setter de Ventas, Freelancer, NOC, Admin</em>).</p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-shield-halved text-danger me-2"></i>Auditoría de Accesos</h6>
                  <p class="small text-muted mb-0">Muestra la fecha y hora del último ingreso de cada agente, junto con su dirección IP de red registrada.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Pestaña: Config -->
          <div class="assistant-tab-card d-none" id="tabCard_config">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <span class="badge text-bg-warning px-3 py-2 rounded-pill fw-bold"><i class="fa-solid fa-sliders me-1"></i> MÓDULO: CONFIGURACIÓN Y GOOGLE SHEETS</span>
              <span class="badge text-bg-primary px-3 py-2 rounded-pill d-none" id="currentBadge_config">📍 Pestaña Actual</span>
            </div>
            <h4 class="fw-bold text-dark mb-2"><i class="fa-solid fa-gear text-secondary me-2"></i>Ajustes del Sistema y Vínculo de Datos</h4>
            <p class="text-muted fs-6 mb-3">Modifica parámetros globales del portal y la sincronización con la nube:</p>

            <ul class="list-group list-group-flush">
              <li class="list-group-item border-0 bg-light mb-2 rounded-3">
                <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-sliders text-vuela me-2"></i>Cambio de Escala de Calificación</h6>
                <p class="small text-muted mb-0">Selecciona entre 3, 4, 5 o 11 caritas según el requerimiento de la campaña de evaluación.</p>
              </li>
              <li class="list-group-item border-0 bg-light rounded-3">
                <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-link text-primary me-2"></i>Integración con Google Sheets / Excel</h6>
                <p class="small text-muted mb-0">Modifica el enlace del documento Excel y la URL de Google Apps Script para enviar cada voto en tiempo real a tu hoja de cálculo compartida.</p>
              </li>
            </ul>
          </div>

          <!-- Pestaña: Formulario -->
          <div class="assistant-tab-card d-none" id="tabCard_formulario">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <span class="badge text-bg-warning px-3 py-2 rounded-pill fw-bold"><i class="fa-solid fa-file-contract me-1"></i> MÓDULO: FORMULARIO OFICIAL</span>
              <span class="badge text-bg-primary px-3 py-2 rounded-pill d-none" id="currentBadge_formulario">📍 Pestaña Actual</span>
            </div>
            <h4 class="fw-bold text-dark mb-2"><i class="fa-solid fa-file-pdf text-danger me-2"></i>Documento Gerencial Imprimible</h4>
            <p class="text-muted fs-6 mb-3">Genera el reporte ejecutivo oficial en formato de documento para firma o archivo:</p>

            <div class="p-3 border rounded-3 bg-light mb-3">
              <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-chart-pie text-vuela me-2"></i>Gráfica de Dona Integrada</h6>
              <p class="small text-muted mb-0">Incluye el gráfico porcentual del estado de opinión de la clientela directo en la hoja impresa.</p>
            </div>

            <div class="p-3 border rounded-3 bg-light">
              <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-print text-primary me-2"></i>Impresión Limpia o Exportación PDF</h6>
              <p class="small text-muted mb-0">Presiona <span class="badge text-bg-dark"><i class="fa-solid fa-print me-1"></i>Imprimir Documento</span> para guardar como PDF o imprimir sin encabezados ni menús de navegación.</p>
            </div>
          </div>
          <?php endif; ?>

        </div>
      </div>

      <div class="modal-footer border-0 bg-light p-3 d-flex justify-content-between">
        <button type="button" class="btn btn-outline-secondary px-4 fw-bold" data-bs-dismiss="modal">
          <i class="fa-solid fa-xmark me-1"></i> Cerrar Asistente
        </button>
        <a href="<?= e(url('/calificar')) ?>" class="btn btn-vuela px-4 fw-bold">
          <i class="fa-solid fa-star me-1"></i> Ir a Calificar
        </a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('js/app.js')) ?>"></script>
<?php if (!empty($extraJs)): ?>
  <script src="<?= e(asset('js/' . $extraJs)) ?>"></script>
<?php endif; ?>

<script>
(function() {
  let timerSec = 10;

  function formatRoleBadge(rol) {
    if (rol === 'Administrador') return '<span class="badge text-bg-dark" style="font-size:0.68rem;">Admin</span>';
    if (rol === 'ATC') return '<span class="badge text-bg-primary" style="font-size:0.68rem;">ATC</span>';
    if (rol === 'Setter de Ventas') return '<span class="badge text-bg-warning text-dark" style="font-size:0.68rem;">Ventas</span>';
    if (rol === 'NOC') return '<span class="badge text-bg-info text-dark" style="font-size:0.68rem;">NOC</span>';
    return '<span class="badge text-bg-secondary" style="font-size:0.68rem;">' + (rol || 'Agente') + '</span>';
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  window.fetchOnlineUsers = function() {
    fetch('<?= e(url('/api/online')) ?>', { cache: 'no-store' })
      .then(r => r.json())
      .then(data => {
        if (!data || !data.ok) return;
        
        const count = data.total || 0;
        const navEl = document.getElementById('navOnlineCount');
        const floatEl = document.getElementById('floatingOnlineCount');
        const totalEl = document.getElementById('offcanvasOnlineTotal');
        const listEl = document.getElementById('onlineUsersList');

        if (navEl) navEl.textContent = count;
        if (floatEl) floatEl.textContent = count;
        if (totalEl) totalEl.textContent = count + ' usuario(s)';

        if (listEl) {
          if (!data.users || data.users.length === 0) {
            listEl.innerHTML = '<div class="text-center py-4 text-muted small">No hay usuarios activos registrados.</div>';
            return;
          }

          let html = '';
          data.users.forEach(u => {
            const isMe = !!u.is_me;
            const initial = (u.nombre || u.usuario || 'U').charAt(0).toUpperCase();
            let avatarHtml = '';
            if (u.foto) {
              avatarHtml = `<img src="${escapeHtml(u.foto)}" width="40" height="40" class="rounded-circle object-fit-cover shadow-sm" alt="Foto">`;
            } else {
              avatarHtml = `<div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold" style="width:40px; height:40px; font-size:1.1rem;">${initial}</div>`;
            }

            html += `
              <div class="user-online-item p-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                  <div class="position-relative">
                    ${avatarHtml}
                    <span class="live-dot-pulse position-absolute bottom-0 end-0 border border-2 border-white"></span>
                  </div>

                  <div>
                    <div class="fw-bold text-dark d-flex align-items-center gap-1" style="font-size:0.92rem;">
                      ${escapeHtml(u.nombre || u.usuario)}
                      ${isMe ? '<span class="badge text-bg-success rounded-pill ms-1" style="font-size:0.68rem;">Tú</span>' : ''}
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                      <small class="text-muted">@${escapeHtml(u.usuario)}</small>
                      ${formatRoleBadge(u.rol)}
                    </div>
                  </div>
                </div>
                <div class="text-end">
                  <span class="badge text-bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" style="font-size:0.7rem;">
                    <i class="fa-solid fa-wifi me-1"></i>En línea
                  </span>
                  <small class="d-block text-muted mt-1" style="font-size:0.7rem;">${escapeHtml(u.hace_cuanto)}</small>
                </div>
              </div>
            `;
          });
          listEl.innerHTML = html;
        }

        timerSec = 10;
        updateCountdownUI();
      })
      .catch(err => console.error('Error fetching online users:', err));
  };

  function updateCountdownUI() {
    const cdEl = document.getElementById('onlineCountdown');
    if (cdEl) cdEl.textContent = timerSec;
  }

  window.refreshOnlineUsersManually = function() {
    fetchOnlineUsers();
  };

  document.addEventListener('DOMContentLoaded', function() {
    fetchOnlineUsers();
    
    setInterval(function() {
      timerSec--;
      if (timerSec <= 0) {
        fetchOnlineUsers();
      } else {
        updateCountdownUI();
      }
    }, 1000);
  });
})();
</script>
</body>
</html>

