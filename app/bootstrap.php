<?php
declare(strict_types=1);

require __DIR__ . '/config.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/Router.php';
require __DIR__ . '/Models/Store.php';
require __DIR__ . '/Models/Sheets.php';
require __DIR__ . '/Models/User.php';
require __DIR__ . '/Models/Cliente.php';
require __DIR__ . '/Models/Calificacion.php';
require __DIR__ . '/Controllers/AuthController.php';
require __DIR__ . '/Controllers/PanelController.php';
require __DIR__ . '/Controllers/CalificacionController.php';
require __DIR__ . '/Controllers/AtencionController.php';
require __DIR__ . '/Controllers/AdminController.php';
require __DIR__ . '/Controllers/ReporteController.php';

no_cache_headers();

// Inicializar almacenamiento ANTES de las sesiones
Store::boot();

// Guardar sesiones PHP junto con los datos JSON para que persistan juntos
$sessDir = DATA_PATH . '/sessions';
if (!is_dir($sessDir)) {
    mkdir($sessDir, 0775, true);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name(SESSION_NAME);
    session_save_path($sessDir);
    session_set_cookie_params([
        'lifetime' => 86400, // 24 horas (no se pierde al cerrar navegador)
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (auth_user()) {
    if (empty($_SESSION['tab_auth_token'])) {
        $_SESSION['tab_auth_token'] = bin2hex(random_bytes(16));
        $_SESSION['is_new_session'] = true;
    }
    $now = time();
    // Inactividad máxima de 2 horas y 30 minutos (9000 segundos) por seguridad
    if (isset($_SESSION['last_activity']) && ($now - (int)$_SESSION['last_activity']) > 9000) {
        unset($_SESSION['user'], $_SESSION['last_activity'], $_SESSION['tab_auth_token']);
        session_destroy();
    } else {
        $_SESSION['last_activity'] = $now;
        User::touchOnline();
    }
}

