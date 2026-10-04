<?php
declare(strict_types=1);

final class CalificacionController
{
    public static function form(): void
    {
        $u = require_login();
        $cfg = Store::read('config');
        $n = (int)($cfg['niveles_caritas'] ?? 5);
        $customs = $cfg['cuestionarios'] ?? [];

        $defaultCuest = [
            'id' => 'cuest-gen',
            'titulo' => 'Atención General',
            'pregunta' => '¿Cómo califica la atención recibida hoy en caja u oficina?',
            'escala' => $n,
            'icono' => 'fa-headset'
        ];

        $hasGen = false;
        foreach ($customs as $c) {
            if (($c['titulo'] ?? '') === 'Atención General' || ($c['id'] ?? '') === 'cuest-gen') {
                $hasGen = true;
                break;
            }
        }
        if (!$hasGen) {
            array_unshift($customs, $defaultCuest);
        }

        $firstScale = (int)($customs[0]['escala'] ?? $n);

        view('calificar', [
            'title' => 'Calificar atención',
            'user' => $u,
            'cfg' => $cfg,
            'niveles' => niveles_activos($firstScale),
            'cuestionarios' => $customs,
            'niveles_all' => niveles_activos(11),
            'max' => $firstScale,
            'extraJs' => 'calificar.js',
            'error' => flash('error'),
        ]);
    }

    public static function apiCliente(): void
    {
        require_login();
        $codigo = Cliente::normalize((string)($_GET['codigo'] ?? ''));
        $c = $codigo ? Cliente::lookup($codigo) : null;
        json_out([
            'ok' => true,
            'existe' => (bool)$c,
            'cliente' => $c,
        ]);
    }

    public static function guardar(): void
    {
        require_login();
        csrf_verify();
        $res = Calificacion::registrar([
            'codigo' => $_POST['codigo'] ?? '',
            'tipo' => $_POST['tipo'] ?? 'cliente',
            'score' => $_POST['score'] ?? 0,
            'score_max' => $_POST['score_max'] ?? $_POST['max'] ?? null,
            'encuesta_tipo' => $_POST['encuesta_tipo'] ?? 'Atención General',
        ]);
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            if (empty($res['ok'])) {
                json_out(['ok' => false, 'error' => $res['error'] ?? 'No se pudo guardar.'], 400);
            }
            json_out(['ok' => true, 'rating' => $res['rating'] ?? null]);
        }
        if (empty($res['ok'])) {
            flash('error', $res['error'] ?? 'No se pudo guardar.');
            redirect('/calificar');
        }
        $_SESSION['last_rating'] = $res['rating'];
        $_SESSION['last_cliente'] = $res['cliente'];
        redirect('/calificar/ok');
    }

    public static function ok(): void
    {
        require_login();
        $rating = $_SESSION['last_rating'] ?? null;
        if (!$rating) {
            redirect('/calificar');
        }
        view('calificar_ok', [
            'title' => 'Calificación registrada',
            'rating' => $rating,
            'cliente' => $_SESSION['last_cliente'] ?? null,
        ]);
    }

    public static function apiCrearTokenLink(): void
    {
        // Para endpoints API: verificar sesión sin redirigir (evita devolver HTML)
        $u = auth_user();
        if (!$u) {
            json_out(['ok' => false, 'error' => 'Sesión expirada. Por favor, recargue la página e inicie sesión nuevamente.'], 401);
            return;
        }

        // Verificar CSRF con respuesta JSON en vez de texto plano
        $csrfOk = isset($_POST['csrf'], $_SESSION[CSRF_KEY])
            && hash_equals($_SESSION[CSRF_KEY], (string)$_POST['csrf']);
        if (!$csrfOk) {
            json_out(['ok' => false, 'error' => 'Token de seguridad expirado. Recargue la página e intente de nuevo.'], 419);
            return;
        }

        $encuesta = trim((string)($_POST['encuesta_tipo'] ?? 'Atención General'));
        $duration = 300; // 5 minutos de validez estricta
        $now = time();
        $expiresAt = $now + $duration;
        $token = bin2hex(random_bytes(16));

        $tokens = Store::read('tokens');
        if (!is_array($tokens)) {
            $tokens = [];
        }

        // Limpiar tokens con más de 10 minutos de antigüedad
        $tokens = array_values(array_filter($tokens, function ($t) use ($now) {
            return (int)($t['expires_at'] ?? 0) > ($now - 600);
        }));

        $record = [
            'token' => $token,
            'agente_id' => $u['id'] ?? '',
            'agente_usuario' => $u['usuario'] ?? '',
            'agente_nombre' => $u['nombre'] ?? 'Agente',
            'agente_rol' => $u['rol'] ?? 'ATC',
            'encuesta_tipo' => $encuesta,
            'created_at' => $now,
            'expires_at' => $expiresAt,
            'usado' => false,
        ];

        $tokens[] = $record;
        Store::write('tokens', $tokens);

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'sis-calif.onrender.com';
        $linkUrl = $scheme . '://' . $host . url('/calificar/link?t=' . $token);

        json_out([
            'ok' => true,
            'token' => $token,
            'url' => $linkUrl,
            'expires_at' => $expiresAt,
            'expires_in' => $duration,
            'agente_nombre' => $u['nombre'] ?? 'Agente',
            'agente_rol' => $u['rol'] ?? 'ATC',
            'encuesta_tipo' => $encuesta,
        ]);
    }

    public static function formLink(): void
    {
        no_cache_headers();
        $tokenStr = trim((string)($_GET['t'] ?? ''));
        if ($tokenStr === '') {
            view_plain('calificar_expired', ['error' => 'Enlace no especificado.']);
            return;
        }

        $tokens = Store::read('tokens');
        $found = null;
        $now = time();

        if (is_array($tokens)) {
            foreach ($tokens as $t) {
                if (($t['token'] ?? '') === $tokenStr) {
                    $found = $t;
                    break;
                }
            }
        }

        if (!$found || (int)($found['expires_at'] ?? 0) < $now) {
            view_plain('calificar_expired', ['error' => 'Este enlace de calificación ha expirado (Validez: 5 minutos).']);
            return;
        }

        if (!empty($found['usado'])) {
            view_plain('calificar_expired', ['error' => 'Este enlace ya fue utilizado para enviar una calificación. No se permite reingresar ni volver atrás.']);
            return;
        }

        $cfg = Store::read('config');
        $n = (int)($cfg['niveles_caritas'] ?? 5);
        $encuestaTipo = $found['encuesta_tipo'] ?? 'Atención General';

        $escala = $n;
        $pregunta = '¿Cómo califica la atención recibida hoy en caja u oficina?';
        $customs = $cfg['cuestionarios'] ?? [];
        foreach ($customs as $c) {
            if (($c['titulo'] ?? '') === $encuestaTipo) {
                $escala = (int)($c['escala'] ?? $n);
                $pregunta = $c['pregunta'] ?? $pregunta;
                break;
            }
        }

        view_plain('calificar_public', [
            'title' => 'Calificar atención de ' . ($found['agente_nombre'] ?? 'Agente') . ' - Vuela Internet',
            'agentName' => $found['agente_nombre'] ?? 'Agente',
            'agentRol' => $found['agente_rol'] ?? 'ATC',
            'token' => $found,
            'cfg' => $cfg,
            'pregunta' => $pregunta,
            'niveles' => niveles_activos($escala),
            'niveles_all' => niveles_activos(11),
            'max' => $escala,
            'encuesta_tipo' => $encuestaTipo,
        ]);
    }

    public static function guardarLink(): void
    {
        no_cache_headers();
        $tokenStr = trim((string)($_POST['token'] ?? ''));
        $tokens = Store::read('tokens');
        $foundIndex = -1;
        $found = null;
        $now = time();

        if (is_array($tokens)) {
            foreach ($tokens as $idx => $t) {
                if (($t['token'] ?? '') === $tokenStr) {
                    $found = $t;
                    $foundIndex = $idx;
                    break;
                }
            }
        }

        if (!$found || (int)($found['expires_at'] ?? 0) < $now) {
            view_plain('calificar_expired', ['error' => 'El enlace expiró antes de enviar su calificación.']);
            return;
        }

        if (!empty($found['usado'])) {
            view_plain('calificar_expired', ['error' => 'Este enlace ya fue utilizado para enviar una calificación.']);
            return;
        }

        // Determinar escala del cuestionario del token
        $cfg = Store::read('config');
        $n = (int)($cfg['niveles_caritas'] ?? 5);
        $encuestaTipo = $found['encuesta_tipo'] ?? 'Atención General';
        $escalaToken = $n;
        $customs = $cfg['cuestionarios'] ?? [];
        foreach ($customs as $c) {
            if (($c['titulo'] ?? '') === $encuestaTipo) {
                $escalaToken = (int)($c['escala'] ?? $n);
                break;
            }
        }

        $res = Calificacion::registrar([
            'codigo' => $_POST['codigo'] ?? '',
            'tipo' => $_POST['tipo'] ?? 'visitante',
            'score' => $_POST['score'] ?? 0,
            'score_max' => $_POST['score_max'] ?? $escalaToken,
            'encuesta_tipo' => $encuestaTipo,
            'agente_id' => $found['agente_id'],
            'agente_usuario' => $found['agente_usuario'] ?? '',
            'agente_nombre' => $found['agente_nombre'],
            'rol' => $found['agente_rol'],
        ]);

        if (empty($res['ok'])) {
            view_plain('calificar_public', [
                'title' => 'Calificar atención',
                'agentName' => $found['agente_nombre'] ?? 'Agente',
                'agentRol' => $found['agente_rol'] ?? 'ATC',
                'token' => $found,
                'cfg' => $cfg,
                'pregunta' => '¿Cómo califica la atención recibida?',
                'niveles' => niveles_activos($escalaToken),
                'niveles_all' => niveles_activos(11),
                'max' => $escalaToken,
                'encuesta_tipo' => $encuestaTipo,
                'error' => $res['error'] ?? 'Error al registrar la calificación.',
            ]);
            return;
        }

        // Marcar token como usado inmediatamente para invalidarlo permanentemente
        if ($foundIndex >= 0 && isset($tokens[$foundIndex])) {
            $tokens[$foundIndex]['usado'] = true;
            $tokens[$foundIndex]['used_at'] = $now;
            Store::write('tokens', $tokens);
        }

        view_plain('calificar_public_ok', [
            'title' => '¡Gracias por su calificación!',
            'rating' => $res['rating'] ?? null,
            'cliente' => $res['cliente'] ?? null,
            'agentName' => $found['agente_nombre'] ?? 'Agente',
        ]);
    }
}
