<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

$router = new Router();

$router->get('/', [AuthController::class, 'splash']);
$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/bienvenida', [AuthController::class, 'bienvenida']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/api/online', [AuthController::class, 'apiOnline']);


$router->get('/panel', [PanelController::class, 'index']);

$router->get('/calificar', [CalificacionController::class, 'form']);
$router->post('/calificar', [CalificacionController::class, 'guardar']);
$router->get('/calificar/ok', [CalificacionController::class, 'ok']);
$router->post('/api/token/crear', [CalificacionController::class, 'apiCrearTokenLink']);
$router->get('/calificar/link', [CalificacionController::class, 'formLink']);
$router->post('/calificar/link', [CalificacionController::class, 'guardarLink']);
$router->get('/api/cliente', [CalificacionController::class, 'apiCliente']);
$router->get('/atencion', [AtencionController::class, 'index']);

$router->get('/admin', [AdminController::class, 'index']);
$router->get('/admin/usuarios', [AdminController::class, 'usuarios']);
$router->post('/admin/usuarios', [AdminController::class, 'crearUsuario']);
$router->post('/admin/usuarios/editar', [AdminController::class, 'editarUsuario']);
$router->post('/admin/usuarios/eliminar', [AdminController::class, 'eliminarUsuario']);
$router->get('/api/ranking', [AdminController::class, 'apiRanking']);
$router->get('/admin/config', [AdminController::class, 'config']);
$router->post('/admin/config', [AdminController::class, 'guardarConfig']);
$router->post('/admin/config/test-sheets', [AdminController::class, 'probarSheets']);
$router->post('/admin/reset-data', [AdminController::class, 'resetData']);
$router->post('/admin/cuestionario/crear', [AdminController::class, 'crearCuestionario']);
$router->post('/admin/cuestionario/eliminar', [AdminController::class, 'eliminarCuestionario']);
$router->post('/admin/calificaciones/eliminar', [AdminController::class, 'eliminarCalificacion']);
$router->get('/admin/reportes', [AdminController::class, 'reportes']);
$router->post('/admin/sync', [AdminController::class, 'sync']);
$router->get('/admin/ranking', [AdminController::class, 'ranking']);
$router->get('/admin/formulario', [AdminController::class, 'formulario']);

$router->get('/exportar/excel', [ReporteController::class, 'excel']);
$router->get('/exportar/pdf', [ReporteController::class, 'pdf']);
$router->get('/exportar/formulario', [ReporteController::class, 'formularioPdf']);

$router->dispatch();
