<?php
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('DATA_PATH', ROOT_PATH . '/data');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('BASE_URL', '');

define('APP_NAME', 'Vuela Internet');
define('APP_TAGLINE', 'Internet de verdad');
define('APP_TITLE', 'Sistema de Calificación de Atención');

define('SESSION_NAME', 'vuela_calif');
define('CSRF_KEY', '_csrf');

define('ROLES', [
    'Administrador',
    'ATC',
    'NOC',
    'Setter de Ventas',
    'Cajero / Caja',
    'Soporte Técnico',
    'Cobranzas',
    'Supervisor',
    'Freelancer',
]);

define('VUELA_ORANGE', '#F15A22');
define('VUELA_ORANGE_DARK', '#D94A12');
