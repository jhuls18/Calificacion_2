<?php
declare(strict_types=1);

final class AdminController
{
    public static function index(): void
    {
        require_admin();
        redirect('/admin/reportes');
    }

    public static function usuarios(): void
    {
        $admin = require_admin();
        view('admin/usuarios', [
            'title' => 'Usuarios y accesos',
            'users' => User::all(),
            'roles' => ROLES,
            'ok' => flash('ok'),
            'error' => flash('error'),
            'admin' => $admin,
        ]);
    }

    public static function crearUsuario(): void
    {
        require_admin();
        csrf_verify();
        $res = User::create([
            'usuario' => $_POST['usuario'] ?? '',
            'nombre' => $_POST['nombre'] ?? '',
            'password' => $_POST['password'] ?? '',
            'rol' => $_POST['rol'] ?? 'ATC',
            'custom_rol' => $_POST['custom_rol'] ?? '',
            'foto' => $_POST['foto'] ?? '',
        ]);
        if (empty($res['ok'])) {
            flash('error', $res['error'] ?? 'No se creó el usuario.');
        } else {
            Sheets::pushAllUsers(User::all());
            flash('ok', 'Usuario creado correctamente.');
        }
        redirect('/admin/usuarios');
    }

    public static function eliminarUsuario(): void
    {
        $admin = require_admin();
        csrf_verify();
        $id = (string)($_POST['id'] ?? '');
        if ($id === ($admin['id'] ?? '')) {
            flash('error', 'No puede eliminarse a sí mismo.');
            redirect('/admin/usuarios');
        }
        $res = User::delete($id);
        if (empty($res['ok'])) {
            flash('error', $res['error'] ?? 'No se pudo eliminar.');
        } else {
            Sheets::pushAllUsers(User::all());
            flash('ok', 'Usuario eliminado.');
        }
        redirect('/admin/usuarios');
    }

    public static function editarUsuario(): void
    {
        $admin = require_admin();
        csrf_verify();
        $id = (string)($_POST['id'] ?? '');
        $res = User::update($id, [
            'nombre' => $_POST['nombre'] ?? '',
            'usuario' => $_POST['usuario'] ?? '',
            'password' => $_POST['password'] ?? '',
            'rol' => $_POST['rol'] ?? 'ATC',
            'custom_rol' => $_POST['custom_rol'] ?? '',
            'foto' => $_POST['foto'] ?? '',
            'activo' => isset($_POST['activo']),
        ]);
        if (empty($res['ok'])) {
            flash('error', $res['error'] ?? 'No se pudo actualizar el usuario.');
        } else {
            if ($id === ($admin['id'] ?? '')) {
                $_SESSION['user']['nombre'] = $res['user']['nombre'] ?? $_SESSION['user']['nombre'];
                $_SESSION['user']['usuario'] = $res['user']['usuario'] ?? $_SESSION['user']['usuario'];
                $_SESSION['user']['rol'] = $res['user']['rol'] ?? $_SESSION['user']['rol'];
                $_SESSION['user']['foto'] = $res['user']['foto'] ?? ($_SESSION['user']['foto'] ?? '');
            }
            Sheets::pushAllUsers(User::all());
            flash('ok', 'Datos del usuario "' . ($res['user']['nombre'] ?? '') . '" actualizados correctamente.');
        }
        redirect('/admin/usuarios');
    }

    public static function eliminarCalificacion(): void
    {
        $admin = require_admin();
        csrf_verify();
        $id = (string)($_POST['id'] ?? '');
        $confirm = !empty($_POST['confirm_delete']);

        $referer = $_SERVER['HTTP_REFERER'] ?? '/admin/reportes';
        if (str_contains($referer, '/https:/') || str_contains($referer, '/http:/')) {
            $referer = '/admin/reportes';
        }

        if ($id === '') {
            flash('error', 'Identificador de calificación no válido.');
            redirect($referer);
        }

        if (!$confirm) {
            flash('error', 'Debe marcar la casilla de confirmación final para proceder.');
            redirect($referer);
        }

        $res = Calificacion::delete($id);
        if (empty($res['ok'])) {
            flash('error', $res['error'] ?? 'No se pudo eliminar la calificación.');
        } else {
            flash('ok', 'La calificación ha sido eliminada permanentemente del sistema.');
        }

        redirect($referer);
    }



    public static function apiRanking(): void
    {
        $u = require_login();
        header('Content-Type: application/json');
        $ranking = Calificacion::ranking();
        $allRatings = Calificacion::all();

        echo json_encode([
            'ok' => true,
            'ranking' => $ranking,
            'total_ratings' => count($allRatings),
            'me_id' => $u['id'] ?? '',
            'me_usuario' => $u['usuario'] ?? '',
            'timestamp' => now(),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function config(): void
    {
        require_admin();
        view('admin/config', [
            'title' => 'Configuración',
            'cfg' => Store::read('config'),
            'ok' => flash('ok'),
            'error' => flash('error'),
        ]);
    }

    public static function guardarConfig(): void
    {
        require_admin();
        csrf_verify();
        $cfg = Store::read('config');
        $cfg['google_script_url'] = trim((string)($_POST['google_script_url'] ?? ''));
        $cfg['google_sheet_view_url'] = trim((string)($_POST['google_sheet_view_url'] ?? ''));
        $cfg['niveles_caritas'] = max(3, min(11, (int)($_POST['niveles_caritas'] ?? 5)));
        $cfg['permitir_visitante'] = isset($_POST['permitir_visitante']);
        Store::write('config', $cfg);
        Sheets::pushConfig($cfg);
        flash('ok', 'Configuración guardada y sincronizada.');
        redirect('/admin/config');
    }

    public static function probarSheets(): void
    {
        require_admin();
        csrf_verify();
        $res = Sheets::testConnection();
        if (!empty($res['ok'])) {
            flash('ok', $res['mensaje'] ?? 'Conexión exitosa con Google Sheets.');
        } else {
            flash('error', $res['error'] ?? 'Error al conectar con Google Sheets.');
        }
        redirect('/admin/config');
    }

    public static function resetData(): void
    {
        require_admin();
        csrf_verify();
        Store::write('ratings', []);
        Store::write('clientes', []);

        if (Sheets::enabled()) {
            $remote = Sheets::pullRatings();
            if (is_array($remote) && !empty($remote)) {
                $formatted = [];
                foreach ($remote as $r) {
                    if (empty($r['fecha'])) continue;
                    $score = (int)($r['score'] ?? 0);
                    $max = 5;
                    $puntos = puntos_por_score($score, $max);
                    $formatted[] = [
                        'id' => 'r-' . uuid(),
                        'fecha' => (string)($r['fecha'] ?? now()),
                        'anio' => date('Y', strtotime((string)($r['fecha'] ?? 'now'))),
                        'mes' => date('Y-m', strtotime((string)($r['fecha'] ?? 'now'))),
                        'codigo' => (string)($r['codigo'] ?? 'VISITANTE'),
                        'tipo' => (string)($r['tipo'] ?? 'cliente'),
                        'visita_nro' => (int)($r['visita_nro'] ?? 1),
                        'score' => $score,
                        'score_max' => $max,
                        'etiqueta' => (string)($r['etiqueta'] ?? 'Atención'),
                        'puntos' => $puntos,
                        'positivo' => $puntos >= 0,
                        'encuesta_tipo' => (string)($r['encuesta_tipo'] ?? 'Atención General'),
                        'agente_id' => 'na',
                        'agente_nombre' => (string)($r['agente_nombre'] ?? 'Agente'),
                        'rol' => (string)($r['rol'] ?? 'ATC'),
                        'ip' => (string)($r['ip'] ?? '127.0.0.1'),
                    ];
                }
                Store::write('ratings', $formatted);
                flash('ok', 'Datos reiniciados a 0 e importados desde Google Sheets. Registros sincronizados: ' . count($formatted));
                redirect('/admin/config');
                return;
            }
        }

        flash('ok', 'Todos los datos de evaluaciones locales fueron reiniciados a 0 correctamente.');
        redirect('/admin/config');
    }

    public static function crearCuestionario(): void
    {
        require_admin();
        csrf_verify();
        $cfg = Store::read('config');
        $titulo = trim((string)($_POST['titulo'] ?? ''));
        $pregunta = trim((string)($_POST['pregunta'] ?? ''));
        $escala = max(3, min(11, (int)($_POST['escala'] ?? 5)));
        $icono = trim((string)($_POST['icono'] ?? 'fa-list-check'));

        if ($titulo === '' || $pregunta === '') {
            flash('error', 'Ingrese el título y la pregunta del cuestionario.');
            redirect('/admin/config');
        }

        $cuestionarios = $cfg['cuestionarios'] ?? [];
        $cuestionarios[] = [
            'id' => 'cuest-' . uuid(),
            'titulo' => $titulo,
            'pregunta' => $pregunta,
            'escala' => $escala,
            'icono' => $icono !== '' ? $icono : 'fa-list-check',
        ];
        $cfg['cuestionarios'] = $cuestionarios;
        Store::write('config', $cfg);
        Sheets::pushConfig($cfg);
        flash('ok', 'Nuevo cuestionario "' . $titulo . '" creado con escala de ' . $escala . ' caritas.');
        redirect('/admin/config');
    }

    public static function eliminarCuestionario(): void
    {
        require_admin();
        csrf_verify();
        $cfg = Store::read('config');
        $id = (string)($_POST['id'] ?? '');
        $cuestionarios = $cfg['cuestionarios'] ?? [];
        $nuevos = [];
        foreach ($cuestionarios as $c) {
            if (($c['id'] ?? '') !== $id) {
                $nuevos[] = $c;
            }
        }
        $cfg['cuestionarios'] = $nuevos;
        Store::write('config', $cfg);
        Sheets::pushConfig($cfg);
        flash('ok', 'Cuestionario eliminado.');
        redirect('/admin/config');
    }

    public static function reportes(): void
    {
        require_admin();
        $anio = trim((string)($_GET['anio'] ?? ''));
        $mes = trim((string)($_GET['mes'] ?? ''));
        $encuesta = trim((string)($_GET['encuesta'] ?? ''));
        $fecha_inicio = trim((string)($_GET['fecha_inicio'] ?? ''));
        $fecha_fin = trim((string)($_GET['fecha_fin'] ?? ''));

        $stats = Calificacion::stats(
            $anio !== '' ? $anio : null,
            $mes !== '' ? $mes : null,
            $encuesta !== '' ? $encuesta : null,
            $fecha_inicio !== '' ? $fecha_inicio : null,
            $fecha_fin !== '' ? $fecha_fin : null
        );

        $anios = [];
        $meses = [];
        $encuestasMap = [];
        foreach (Calificacion::all() as $r) {
            if (!empty($r['anio'])) {
                $anios[(string)$r['anio']] = true;
            }
            if (!empty($r['mes'])) {
                $meses[(string)$r['mes']] = true;
            }
            $encName = (string)($r['encuesta_tipo'] ?? 'Atención General');
            if ($encName) {
                $encuestasMap[$encName] = true;
            }
        }

        $cfg = Store::read('config');
        if (!empty($cfg['cuestionarios'])) {
            foreach ($cfg['cuestionarios'] as $c) {
                if (!empty($c['titulo'])) {
                    $encuestasMap[(string)$c['titulo']] = true;
                }
            }
        }

        krsort($anios);
        krsort($meses);
        ksort($encuestasMap);

        view('admin/reportes', [
            'title' => 'Reportes y estadísticas',
            'stats' => $stats,
            'anio' => $anio,
            'mes' => $mes,
            'encuesta' => $encuesta,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'anios' => array_keys($anios),
            'meses' => array_keys($meses),
            'encuestas' => array_keys($encuestasMap),
            'sheets' => Sheets::enabled(),
            'ok' => flash('ok'),
            'error' => flash('error'),
        ]);
    }

    public static function sync(): void
    {
        require_admin();
        csrf_verify();
        $total = Calificacion::importFromSheets();
        if ($total > 0) {
            flash('ok', '¡Sincronización Exitosa! Se importaron y registraron ' . $total . ' calificaciones existentes desde Google Sheets.');
        } else {
            flash('error', 'No se pudieron obtener registros desde Google Sheets. Verifique la conexión.');
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? url('/admin/config');
        redirect($referer);
    }

    public static function ranking(): void
    {
        $u = require_admin();
        view('admin/ranking', [
            'title' => 'Ranking de agentes en vivo',
            'ranking' => Calificacion::ranking(),
            'user' => $u,
            'allRatings' => Calificacion::all(),
        ]);
    }

    public static function formulario(): void
    {
        require_admin();
        $anio = trim((string)($_GET['anio'] ?? ''));
        $mes = trim((string)($_GET['mes'] ?? ''));
        $encuesta = trim((string)($_GET['encuesta'] ?? ''));
        $fecha_inicio = trim((string)($_GET['fecha_inicio'] ?? ''));
        $fecha_fin = trim((string)($_GET['fecha_fin'] ?? ''));

        $stats = Calificacion::stats($anio ?: null, $mes ?: null, $encuesta ?: null, $fecha_inicio ?: null, $fecha_fin ?: null);
        $cfg = Store::read('config');

        $allRatings = Calificacion::all();
        $anios = [];
        $meses = [];
        $encuestasMap = ['Atención General' => true];
        foreach ($allRatings as $r) {
            if (!empty($r['anio'])) $anios[(string)$r['anio']] = true;
            if (!empty($r['mes'])) $meses[(string)$r['mes']] = true;
            $encName = (string)($r['encuesta_tipo'] ?? 'Atención General');
            if ($encName) $encuestasMap[$encName] = true;
        }
        if (!empty($cfg['cuestionarios'])) {
            foreach ($cfg['cuestionarios'] as $c) {
                if (!empty($c['titulo'])) $encuestasMap[(string)$c['titulo']] = true;
            }
        }

        krsort($anios);
        krsort($meses);
        ksort($encuestasMap);

        view('admin/formulario', [
            'title' => 'Formulario Oficial de Evaluación',
            'stats' => $stats,
            'ranking' => Calificacion::ranking(),
            'cfg' => $cfg,
            'encuestaActual' => $encuesta,
            'anio' => $anio,
            'mes' => $mes,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'anios' => array_keys($anios),
            'meses' => array_keys($meses),
            'encuestas' => array_keys($encuestasMap),
        ]);
    }
}
