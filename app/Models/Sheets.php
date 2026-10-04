<?php
declare(strict_types=1);

final class Sheets
{
    public static function url(): string
    {
        $cfg = Store::read('config');
        return trim((string)($cfg['google_script_url'] ?? ''));
    }

    public static function enabled(): bool
    {
        return self::url() !== '';
    }

    /**
     * Enviar request al Google Apps Script (GET primero, fallback POST)
     */
    public static function request(array $payload): ?array
    {
        $url = self::url();
        if ($url === '') {
            return null;
        }
        
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $action = (string)($payload['action'] ?? '');

        // 1. Intento GET (Máxima compatibilidad con redirecciones 302 de Google Apps Script)
        $getUrl = $url . (str_contains($url, '?') ? '&' : '?') . 'action=' . urlencode($action) . '&data=' . urlencode($json);
        
        // Solo usar GET para lecturas (payload pequeño)
        $useGet = in_array($action, ['getRatings', 'getUsers', 'getConfig', 'getClients', 'fullRestore'], true);
        
        if ($useGet || strlen($getUrl) < 2000) {
            $ch = curl_init($getUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_CONNECTTIMEOUT => 5,
            ]);
            $raw = curl_exec($ch);
            curl_close($ch);

            if (is_string($raw) && $raw !== '') {
                $data = json_decode($raw, true);
                if (is_array($data) && !empty($data['ok'])) {
                    return $data;
                }
            }
        }

        // 2. Fallback POST para escrituras o payloads grandes
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $json,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_POSTREDIR => CURL_REDIR_POST_ALL,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $raw = curl_exec($ch);
            curl_close($ch);
            if (is_string($raw) && $raw !== '') {
                $data = json_decode($raw, true);
                if (is_array($data)) return $data;
            }
        }

        return ['ok' => false, 'error' => 'Sin respuesta de Google Apps Script.'];
    }

    // ===== CALIFICACIONES =====

    public static function pushRating(array $row): void
    {
        if (!self::enabled()) return;
        try {
            self::request(['action' => 'saveRating', 'row' => $row]);
        } catch (\Throwable $e) {
            // local-first: no bloquear
        }
    }

    public static function pullRatings(): ?array
    {
        if (!self::enabled()) return null;
        $res = self::request(['action' => 'getRatings']);
        if (!$res || empty($res['ok'])) return null;
        return $res['ratings'] ?? null;
    }

    // ===== USUARIOS =====

    public static function pushUser(array $row): void
    {
        if (!self::enabled()) return;
        try {
            // No enviar passwords al Sheet
            $safe = array_diff_key($row, ['password' => 1]);
            self::request(['action' => 'saveUser', 'row' => $safe]);
        } catch (\Throwable $e) {}
    }

    public static function pushAllUsers(array $users): void
    {
        if (!self::enabled()) return;
        try {
            $safe = array_map(fn($u) => array_diff_key($u, ['password' => 1]), $users);
            self::request(['action' => 'saveAllUsers', 'rows' => $safe]);
        } catch (\Throwable $e) {}
    }

    public static function pullUsers(): ?array
    {
        if (!self::enabled()) return null;
        $res = self::request(['action' => 'getUsers']);
        if (!$res || empty($res['ok'])) return null;
        return $res['users'] ?? null;
    }

    public static function deleteUser(string $id): void
    {
        if (!self::enabled()) return;
        try {
            self::request(['action' => 'deleteUser', 'id' => $id]);
        } catch (\Throwable $e) {}
    }

    // ===== CONFIGURACIÓN =====

    public static function pushConfig(array $cfg): void
    {
        if (!self::enabled()) return;
        try {
            self::request(['action' => 'saveConfig', 'config' => $cfg]);
        } catch (\Throwable $e) {}
    }

    public static function pullConfig(): ?array
    {
        if (!self::enabled()) return null;
        $res = self::request(['action' => 'getConfig']);
        if (!$res || empty($res['ok'])) return null;
        return $res['config'] ?? null;
    }

    // ===== CLIENTES =====

    public static function pushClient(array $row): void
    {
        if (!self::enabled()) return;
        try {
            self::request(['action' => 'saveClient', 'row' => $row]);
        } catch (\Throwable $e) {}
    }

    public static function pushAllClients(array $clients): void
    {
        if (!self::enabled()) return;
        try {
            self::request(['action' => 'saveAllClients', 'rows' => $clients]);
        } catch (\Throwable $e) {}
    }

    // ===== SYNC COMPLETO =====

    /**
     * Restaurar TODOS los datos desde Google Sheets al arrancar.
     * Retorna los datos o null si Sheets no está habilitado.
     */
    public static function fullRestore(): ?array
    {
        if (!self::enabled()) return null;
        $res = self::request(['action' => 'fullRestore']);
        if (!$res || empty($res['ok'])) return null;
        return $res;
    }

    /**
     * Sincronizar TODO hacia Google Sheets (backup completo)
     */
    public static function fullSync(array $users, array $config, array $clients): void
    {
        if (!self::enabled()) return;
        try {
            $safeUsers = array_map(fn($u) => array_diff_key($u, ['password' => 1]), $users);
            self::request([
                'action' => 'fullSync',
                'users' => $safeUsers,
                'config' => $config,
                'clients' => $clients,
            ]);
        } catch (\Throwable $e) {}
    }

    // ===== TEST =====

    public static function testConnection(): array
    {
        if (!self::enabled()) {
            return ['ok' => false, 'error' => 'No ha configurado una URL de Google Apps Script.'];
        }
        $testRow = [
            'fecha' => date('d/m/Y H:i:s'),
            'codigo' => 'TEST-PRUEBA',
            'tipo' => 'cliente',
            'encuesta_tipo' => 'Prueba de Conexión',
            'visita_nro' => 1,
            'score' => 5,
            'etiqueta' => 'Excelente',
            'puntos' => 1,
            'agente_nombre' => 'Sistema Vuela',
            'rol' => 'ADMIN',
            'ip' => client_ip(),
        ];
        $res = self::request(['action' => 'saveRating', 'row' => $testRow]);
        if (is_array($res) && !empty($res['ok'])) {
            return ['ok' => true, 'mensaje' => '¡Conexión Exitosa! Se insertó una fila de prueba en tu Hoja de Cálculo.'];
        }
        $err = $res['error'] ?? 'No se pudo conectar con Google Sheets.';
        return ['ok' => false, 'error' => $err];
    }
}
