<?php
declare(strict_types=1);

final class Cliente
{
    public static function all(): array
    {
        return Store::read('clients');
    }

    public static function lookup(string $codigo): ?array
    {
        $codigo = self::normalize($codigo);
        if ($codigo === '') {
            return null;
        }
        foreach (self::all() as $c) {
            if (($c['codigo'] ?? '') === $codigo) {
                return $c;
            }
        }
        return null;
    }

    public static function registrarVisita(string $codigo, string $tipo = 'cliente'): array
    {
        $codigo = self::normalize($codigo);
        $clients = self::all();
        foreach ($clients as &$c) {
            if (($c['codigo'] ?? '') === $codigo) {
                $c['visitas'] = (int)$c['visitas'] + 1;
                $c['ultima_visita'] = now();
                Store::write('clients', $clients);
                return $c;
            }
        }
        unset($c);
        $nuevo = [
            'codigo' => $codigo,
            'tipo' => $tipo,
            'visitas' => 1,
            'primera_visita' => now(),
            'ultima_visita' => now(),
        ];
        $clients[] = $nuevo;
        Store::write('clients', $clients);
        return $nuevo;
    }

    public static function normalize(string $codigo): string
    {
        return strtoupper(trim($codigo));
    }
}
