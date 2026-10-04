<?php
declare(strict_types=1);

final class PanelController
{
    public static function index(): void
    {
        $u = require_login();
        $cfg = Store::read('config');
        $stats = Calificacion::stats();
        $ranking = Calificacion::ranking();
        $mias = 0;
        $puntos = 0;
        foreach ($stats['rows'] as $r) {
            if (($r['agente_id'] ?? '') === ($u['id'] ?? '')) {
                $mias++;
                $puntos += (int)$r['puntos'];
            }
        }
        view('panel', [
            'title' => 'Panel',
            'user' => $u,
            'cfg' => $cfg,
            'stats' => $stats,
            'ranking' => $ranking,
            'mias' => $mias,
            'puntos' => $puntos,
        ]);
    }
}
