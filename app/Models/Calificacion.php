<?php
declare(strict_types=1);

final class Calificacion
{
    public static function all(bool $sync = false): array
    {
        $local = Store::read('ratings');
        if (!$sync) {
            return $local;
        }
        $remote = Sheets::pullRatings();
        if (is_array($remote) && $remote) {
            $merged = self::merge($local, $remote);
            Store::write('ratings', $merged);
            return $merged;
        }
        return $local;
    }

    public static function registrar(array $in): array
    {
        $cfg = Store::read('config');
        $score = (int)($in['score'] ?? 0);
        $user = auth_user();

        // Soporte para registro de cliente que No Calificó / Omitió
        if (($in['tipo'] ?? '') === 'omitido' || ($in['omitido'] ?? false)) {
            $max = max(3, min(11, (int)($in['score_max'] ?? $cfg['niveles_caritas'] ?? 5)));
            $row = [
                'id' => 'r-' . uuid(),
                'fecha' => now(),
                'anio' => date('Y'),
                'mes' => date('Y-m'),
                'codigo' => 'OMITIDO-' . date('His'),
                'tipo' => 'visitante',
                'visita_nro' => 1,
                'score' => 0,
                'score_max' => $max,
                'etiqueta' => 'Sin Calificación',
                'mensaje' => 'El cliente no emitió calificación (Se retiró)',
                'icono' => 'fa-user-slash',
                'color' => '#64748b',
                'puntos' => 0,
                'positivo' => false,
                'encuesta_tipo' => (string)($in['encuesta_tipo'] ?? 'Atención General'),
                'agente_id' => $user['id'] ?? '',
                'agente_usuario' => $user['usuario'] ?? '',
                'agente_nombre' => $user['nombre'] ?? '',
                'rol' => $user['rol'] ?? '',
                'ip' => client_ip(),
            ];
            $ratings = Store::read('ratings');
            $ratings[] = $row;
            Store::write('ratings', $ratings);
            Sheets::pushRating($row);
            return ['ok' => true, 'rating' => $row, 'cliente' => null];
        }

        // Determinar max dinámicamente según la escala del cuestionario o parámetro score_max
        $max = 5;
        if (!empty($in['score_max'])) {
            $max = (int)$in['score_max'];
        } elseif (!empty($in['max'])) {
            $max = (int)$in['max'];
        } else {
            $encuesta = (string)($in['encuesta_tipo'] ?? 'Atención General');
            $customs = $cfg['cuestionarios'] ?? [];
            foreach ($customs as $c) {
                if (($c['titulo'] ?? '') === $encuesta) {
                    $max = (int)($c['escala'] ?? 5);
                    break;
                }
            }
            if ($max === 5 && !empty($cfg['niveles_caritas'])) {
                $max = (int)$cfg['niveles_caritas'];
            }
        }
        $max = max(3, min(11, $max));
        if ($score > $max) {
            $max = min(11, $score);
        }

        $niveles = niveles_activos($max);
        $item = null;
        foreach ($niveles as $n) {
            if ((int)$n['n'] === $score) {
                $item = $n;
                break;
            }
        }
        if (!$item) {
            return ['ok' => false, 'error' => 'Calificación fuera de rango (' . $score . ' de ' . $max . ').'];
        }

        $tipo = ($in['tipo'] ?? 'cliente') === 'visitante' ? 'visitante' : 'cliente';
        if ($tipo === 'visitante') {
            $codigo = 'VIS-' . date('Ymd-His') . '-' . substr(uuid(), 0, 4);
            $cliente = Cliente::registrarVisita($codigo, 'visitante');
        } else {
            $codigo = Cliente::normalize((string)($in['codigo'] ?? ''));
            if ($codigo === '') {
                return ['ok' => false, 'error' => 'Ingrese el código de cliente o use calificación visitante.'];
            }
            $cliente = Cliente::registrarVisita($codigo, 'cliente');
        }

        $user = auth_user();
        $agenteId = !empty($in['agente_id']) ? (string)$in['agente_id'] : ($user['id'] ?? '');
        $agenteUsuario = !empty($in['agente_usuario']) ? (string)$in['agente_usuario'] : ($user['usuario'] ?? '');
        $agenteNombre = !empty($in['agente_nombre']) ? (string)$in['agente_nombre'] : ($user['nombre'] ?? '');
        $agenteRol = !empty($in['rol']) ? (string)$in['rol'] : ($user['rol'] ?? '');

        $puntos = puntos_por_score($score, $max);
        $row = [
            'id' => 'r-' . uuid(),
            'fecha' => now(),
            'anio' => date('Y'),
            'mes' => date('Y-m'),
            'codigo' => $codigo,
            'tipo' => $tipo,
            'visita_nro' => (int)$cliente['visitas'],
            'score' => $score,
            'score_max' => $max,
            'etiqueta' => etiqueta_score($score, $max),
            'mensaje' => $item['mensaje'],
            'icono' => $item['icon'],
            'color' => $item['color'],
            'puntos' => $puntos,
            'positivo' => $puntos >= 0,
            'encuesta_tipo' => (string)($in['encuesta_tipo'] ?? 'Atención General'),
            'agente_id' => $agenteId,
            'agente_usuario' => $agenteUsuario,
            'agente_nombre' => $agenteNombre,
            'rol' => $agenteRol,
            'ip' => client_ip(),
        ];

        $ratings = Store::read('ratings');
        $ratings[] = $row;
        Store::write('ratings', $ratings);
        Sheets::pushRating($row);

        return ['ok' => true, 'rating' => $row, 'cliente' => $cliente];
    }

    public static function ranking(): array
    {
        $map = [];
        foreach (self::all() as $r) {
            $id = (string)($r['agente_id'] ?? 'na');
            if (!isset($map[$id])) {
                $map[$id] = [
                    'agente_id' => $id,
                    'agente_nombre' => $r['agente_nombre'] ?? '',
                    'agente_usuario' => $r['agente_usuario'] ?? '',
                    'rol' => $r['rol'] ?? '',
                    'total' => 0,
                    'puntos' => 0,
                    'positivos' => 0,
                    'negativos' => 0,
                    'suma_score' => 0,
                ];
            }
            $map[$id]['total']++;
            $map[$id]['puntos'] += (int)$r['puntos'];
            $map[$id]['suma_score'] += (int)$r['score'];
            if (!empty($r['positivo'])) {
                $map[$id]['positivos']++;
            } else {
                $map[$id]['negativos']++;
            }
        }
        $list = array_values($map);
        foreach ($list as &$a) {
            $a['promedio'] = $a['total'] ? round($a['suma_score'] / $a['total'], 2) : 0;
        }
        unset($a);
        usort($list, static fn($x, $y) => $y['puntos'] <=> $x['puntos']);
        return $list;
    }

    public static function stats(?string $anio = null, ?string $mes = null, ?string $encuesta = null, ?string $fecha_inicio = null, ?string $fecha_fin = null): array
    {
        $rows = self::all();
        $filtradas = [];
        foreach ($rows as $r) {
            if ($anio && (string)($r['anio'] ?? '') !== $anio) {
                continue;
            }
            if ($mes && (string)($r['mes'] ?? '') !== $mes) {
                continue;
            }
            if ($encuesta && (string)($r['encuesta_tipo'] ?? 'Atención General') !== $encuesta) {
                continue;
            }
            if ($fecha_inicio || $fecha_fin) {
                $f = substr((string)($r['fecha'] ?? ''), 0, 10);
                if ($fecha_inicio && $f < $fecha_inicio) {
                    continue;
                }
                if ($fecha_fin && $f > $fecha_fin) {
                    continue;
                }
            }
            $filtradas[] = $r;
        }
        $porEtiqueta = [];
        $porMes = [];
        $porAnio = [];
        foreach ($filtradas as $r) {
            $et = (string)($r['etiqueta'] ?? 'N/A');
            $porEtiqueta[$et] = ($porEtiqueta[$et] ?? 0) + 1;
            $m = (string)($r['mes'] ?? '');
            $a = (string)($r['anio'] ?? '');
            if ($m) {
                $porMes[$m] = ($porMes[$m] ?? 0) + 1;
            }
            if ($a) {
                $porAnio[$a] = ($porAnio[$a] ?? 0) + 1;
            }
        }
        ksort($porMes);
        ksort($porAnio);
        return [
            'total' => count($filtradas),
            'por_etiqueta' => $porEtiqueta,
            'por_mes' => $porMes,
            'por_anio' => $porAnio,
            'rows' => $filtradas,
        ];
    }

    public static function importFromSheets(): int
    {
        $remote = Sheets::pullRatings();
        if (!is_array($remote) || empty($remote)) {
            return 0;
        }
        $local = Store::read('ratings');
        $byId = [];
        foreach ($local as $l) {
            $key = ($l['fecha'] ?? '') . '|' . ($l['codigo'] ?? '') . '|' . ($l['score'] ?? '');
            $byId[$key] = $l;
        }

        foreach ($remote as $r) {
            if (empty($r['fecha'])) continue;
            $key = ($r['fecha'] ?? '') . '|' . ($r['codigo'] ?? '') . '|' . ($r['score'] ?? '');
            if (!isset($byId[$key])) {
                $score = (int)($r['score'] ?? 0);
                $max = 5;
                $puntos = puntos_por_score($score, $max);
                $row = [
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
                    'mensaje' => 'Calificación registrada en Google Sheets',
                    'icono' => 'fa-star',
                    'color' => '#F15A22',
                    'puntos' => $puntos,
                    'positivo' => $puntos >= 0,
                    'encuesta_tipo' => (string)($r['encuesta_tipo'] ?? 'Atención General'),
                    'agente_id' => 'u-agente',
                    'agente_usuario' => (string)($r['agente_nombre'] ?? 'Agente'),
                    'agente_nombre' => (string)($r['agente_nombre'] ?? 'Agente'),
                    'rol' => (string)($r['rol'] ?? 'ATC'),
                    'ip' => (string)($r['ip'] ?? '127.0.0.1'),
                ];
                $byId[$key] = $row;
            }
        }

        $all = array_values($byId);
        usort($all, static fn($a, $b) => strcmp((string)($a['fecha'] ?? ''), (string)($b['fecha'] ?? '')));
        Store::write('ratings', $all);
        return count($all);
    }

    private static function merge(array $local, array $remote): array
    {
        $byId = [];
        foreach (array_merge($local, $remote) as $r) {
            $id = (string)($r['id'] ?? json_encode($r));
            $byId[$id] = $r;
        }
        $all = array_values($byId);
        usort($all, static fn($a, $b) => strcmp((string)($a['fecha'] ?? ''), (string)($b['fecha'] ?? '')));
        return $all;
    }

    public static function delete(string $id): array
    {
        $local = Store::read('ratings');
        $keep = [];
        $found = false;
        foreach ($local as $r) {
            if (($r['id'] ?? '') === $id) {
                $found = true;
            } else {
                $keep[] = $r;
            }
        }

        if (!$found) {
            return ['ok' => false, 'error' => 'Registro de calificación no encontrado.'];
        }

        Store::write('ratings', $keep);
        Sheets::request(['action' => 'deleteRating', 'id' => $id]);
        return ['ok' => true];
    }
}

