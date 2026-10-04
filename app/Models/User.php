<?php
declare(strict_types=1);

final class User
{
    public static function all(): array
    {
        return Store::read('users');
    }

    public static function findByUsuario(string $usuario): ?array
    {
        $usuario = mb_strtolower(trim($usuario));
        foreach (self::all() as $u) {
            if (mb_strtolower((string)$u['usuario']) === $usuario) {
                return $u;
            }
        }
        return null;
    }

    public static function find(string $id): ?array
    {
        foreach (self::all() as $u) {
            if (($u['id'] ?? '') === $id) {
                return $u;
            }
        }
        return null;
    }

    public static function login(string $usuario, string $password, string $nombreFormulario, string $rol): array
    {
        $u = self::findByUsuario($usuario);
        if (!$u || empty($u['activo'])) {
            return ['ok' => false, 'error' => 'Usuario no autorizado o inactivo.'];
        }
        if (!password_verify($password, (string)$u['password'])) {
            return ['ok' => false, 'error' => 'Contraseña incorrecta.'];
        }
        if (($u['rol'] ?? '') !== $rol) {
            return ['ok' => false, 'error' => 'El rol no coincide con el usuario.'];
        }

        $nombre = trim($nombreFormulario) !== '' ? trim($nombreFormulario) : (string)$u['nombre'];
        $users = self::all();
        foreach ($users as &$row) {
            if ($row['id'] === $u['id']) {
                $row['last_login'] = now();
                $row['last_ip'] = client_ip();
                $row['nombre_sesion'] = $nombre;
                $u = $row;
            }
        }
        unset($row);
        Store::write('users', $users);

        Sheets::request([
            'action' => 'loginLog',
            'usuario' => $u['usuario'],
            'nombre' => $nombre,
            'rol' => $u['rol'],
            'ip' => client_ip(),
            'fecha' => now(),
        ]);

        return ['ok' => true, 'user' => [
            'id' => $u['id'],
            'usuario' => $u['usuario'],
            'nombre' => $nombre,
            'rol' => $u['rol'],
            'last_login' => $u['last_login'],
            'last_ip' => $u['last_ip'],
        ]];
    }

    public static function create(array $data): array
    {
        $users = self::all();
        if (self::findByUsuario((string)$data['usuario'])) {
            return ['ok' => false, 'error' => 'El usuario ya existe.'];
        }
        $usuario = trim((string)$data['usuario']);
        $nombre = trim((string)$data['nombre']);
        $password = (string)$data['password'];
        $rol = trim((string)($data['rol'] ?? ''));
        if ($rol === 'OTRO' && !empty($data['custom_rol'])) {
            $rol = trim((string)$data['custom_rol']);
        }
        if ($usuario === '' || $nombre === '' || $password === '') {
            return ['ok' => false, 'error' => 'Usuario, nombre y contraseña son obligatorios.'];
        }
        if ($rol === '') {
            return ['ok' => false, 'error' => 'Debe ingresar o seleccionar un rol válido.'];
        }
        $foto = trim((string)($data['foto'] ?? ''));
        $row = [
            'id' => 'u-' . uuid(),
            'usuario' => $usuario,
            'nombre' => $nombre,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'rol' => $rol,
            'foto' => $foto,
            'activo' => true,
            'creado' => now(),
            'last_login' => null,
            'last_ip' => null,
        ];
        $users[] = $row;
        Store::write('users', $users);
        Sheets::request(['action' => 'saveUser', 'row' => array_diff_key($row, ['password' => 1])]);
        return ['ok' => true, 'user' => $row];
    }

    public static function isProtected(array $u): bool
    {
        return !empty($u['protegido'])
            || ($u['id'] ?? '') === 'u-admin'
            || mb_strtolower((string)($u['usuario'] ?? '')) === 'admin';
    }

    public static function delete(string $id): array
    {
        $target = self::find($id);
        if (!$target) {
            return ['ok' => false, 'error' => 'Usuario no encontrado.'];
        }
        if (self::isProtected($target)) {
            return ['ok' => false, 'error' => 'El administrador principal no se puede eliminar.'];
        }
        $keep = [];
        foreach (self::all() as $u) {
            if (($u['id'] ?? '') !== $id) {
                $keep[] = $u;
            }
        }
        Store::write('users', $keep);
        Sheets::request(['action' => 'deleteUser', 'id' => $id]);
        return ['ok' => true];
    }

    public static function setActivo(string $id, bool $activo): void
    {
        $users = self::all();
        foreach ($users as &$u) {
            if ($u['id'] === $id) {
                $u['activo'] = $activo;
            }
        }
        unset($u);
        Store::write('users', $users);
    }

    public static function update(string $id, array $data): array
    {
        $target = self::find($id);
        if (!$target) {
            return ['ok' => false, 'error' => 'Usuario no encontrado.'];
        }

        $usuario = trim((string)($data['usuario'] ?? ''));
        $nombre = trim((string)($data['nombre'] ?? ''));
        $password = (string)($data['password'] ?? '');
        $rol = trim((string)($data['rol'] ?? ''));
        if ($rol === 'OTRO' && !empty($data['custom_rol'])) {
            $rol = trim((string)$data['custom_rol']);
        }
        $foto = isset($data['foto']) ? trim((string)$data['foto']) : ($target['foto'] ?? '');
        $activo = isset($data['activo']) ? !empty($data['activo']) : true;

        if ($usuario === '' || $nombre === '') {
            return ['ok' => false, 'error' => 'El nombre completo y el nombre de usuario son obligatorios.'];
        }

        if ($rol === '') {
            return ['ok' => false, 'error' => 'El rol seleccionado no es válido.'];
        }

        if (mb_strtolower($usuario) !== mb_strtolower((string)$target['usuario'])) {
            $existing = self::findByUsuario($usuario);
            if ($existing && ($existing['id'] ?? '') !== $id) {
                return ['ok' => false, 'error' => 'El nombre de usuario "@' . $usuario . '" ya está registrado por otra cuenta.'];
            }
        }

        if (self::isProtected($target)) {
            if ($rol !== 'Administrador') {
                return ['ok' => false, 'error' => 'No se puede modificar el rol del administrador principal.'];
            }
            if (!$activo) {
                return ['ok' => false, 'error' => 'El administrador principal debe permanecer activo.'];
            }
        }

        $users = self::all();
        $updatedUser = null;
        foreach ($users as &$u) {
            if (($u['id'] ?? '') === $id) {
                $u['usuario'] = $usuario;
                $u['nombre'] = $nombre;
                $u['rol'] = $rol;
                $u['foto'] = $foto;
                $u['activo'] = $activo;
                if ($password !== '') {
                    $u['password'] = password_hash($password, PASSWORD_DEFAULT);
                }
                $updatedUser = $u;
                break;
            }
        }

        unset($u);

        Store::write('users', $users);

        if ($updatedUser) {
            Sheets::request(['action' => 'saveUser', 'row' => array_diff_key($updatedUser, ['password' => 1])]);
        }

        return ['ok' => true, 'user' => $updatedUser];
    }

    public static function touchOnline(?array $user = null): void
    {
        $u = $user ?? auth_user();
        if (!$u || empty($u['id'])) {
            return;
        }

        $online = Store::read('online');
        if (!is_array($online)) {
            $online = [];
        }

        $id = (string)$u['id'];
        $now = time();

        $online[$id] = [
            'id' => $id,
            'usuario' => (string)($u['usuario'] ?? ''),
            'nombre' => (string)($u['nombre'] ?? ''),
            'rol' => (string)($u['rol'] ?? ''),
            'last_seen' => $now,
            'ip' => client_ip(),
        ];

        foreach ($online as $k => $item) {
            if (($item['last_seen'] ?? 0) < ($now - 120)) {
                unset($online[$k]);
            }
        }

        Store::write('online', $online);
    }

    public static function getOnlineUsers(int $withinSeconds = 45): array
    {
        $online = Store::read('online');
        if (!is_array($online)) {
            return [];
        }

        $now = time();
        $active = [];

        foreach ($online as $item) {
            $diff = $now - (int)($item['last_seen'] ?? 0);
            if ($diff <= $withinSeconds) {
                $item['diff_segundos'] = $diff;
                $item['hace_cuanto'] = $diff <= 5 ? 'hace un momento' : "hace {$diff}s";
                $active[] = $item;
            }
        }

        usort($active, static fn($a, $b) => strcmp((string)($a['nombre'] ?? ''), (string)($b['nombre'] ?? '')));
        return $active;
    }

    public static function removeOnline(string $id): void
    {
        if ($id === '') return;
        $online = Store::read('online');
        if (is_array($online) && isset($online[$id])) {
            unset($online[$id]);
            Store::write('online', $online);
        }
    }
}

