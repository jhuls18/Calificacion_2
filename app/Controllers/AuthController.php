<?php
declare(strict_types=1);

final class AuthController
{
    public static function splash(): void
    {
        if (auth_user()) {
            redirect('/bienvenida');
        }
        redirect('/login');
    }

    public static function loginForm(): void
    {
        if (auth_user()) {
            redirect('/panel');
        }
        view_plain('login', [
            'error' => flash('error'),
            'roles' => ROLES,
        ]);
    }

    public static function login(): void
    {
        csrf_verify();
        $usuario = trim((string)($_POST['usuario'] ?? ''));
        $nombre = trim((string)($_POST['nombre'] ?? ''));
        if ($nombre === '') {
            $nombre = $usuario;
        }
        $password = (string)($_POST['password'] ?? '');
        $rol = (string)($_POST['rol'] ?? '');

        if ($usuario === '' || $password === '' || $rol === '') {
            flash('error', 'Por favor ingresa tu usuario, contraseña y rol.');
            redirect('/login');
        }

        $res = User::login($usuario, $password, $nombre, $rol);
        if (empty($res['ok'])) {
            flash('error', $res['error'] ?? 'No se pudo iniciar sesión.');
            redirect('/login');
        }

        session_regenerate_id(true);
        $_SESSION['user'] = $res['user'];
        $_SESSION['welcome_shown'] = false;
        $_SESSION['tab_auth_token'] = bin2hex(random_bytes(16));
        $_SESSION['is_new_session'] = true;
        redirect('/bienvenida');
    }

    public static function bienvenida(): void
    {
        $u = require_login();
        view_plain('bienvenida', ['user' => $u]);
    }

    public static function apiOnline(): void
    {
        $u = auth_user();
        if ($u) {
            User::touchOnline($u);
        }
        $onlineUsers = User::getOnlineUsers(45);
        $meId = $u['id'] ?? '';

        $formatted = array_map(static function($row) use ($meId) {
            return [
                'id' => $row['id'],
                'usuario' => $row['usuario'],
                'nombre' => $row['nombre'],
                'rol' => $row['rol'],
                'is_me' => ($row['id'] === $meId),
                'hace_cuanto' => $row['hace_cuanto'] ?? 'hace un momento',
                'ip' => $row['ip'] ?? '',
            ];
        }, $onlineUsers);

        json_out([
            'ok' => true,
            'total' => count($formatted),
            'me_id' => $meId,
            'users' => $formatted,
            'timestamp' => now(),
        ]);
    }

    public static function logout(): void
    {
        if (isset($_SESSION['user']['id'])) {
            User::removeOnline((string)$_SESSION['user']['id']);
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'] ?? '', (bool)$p['secure'], (bool)$p['httponly']);
        }
        session_destroy();
        redirect('/login');
    }
}

