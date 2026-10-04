<?php
declare(strict_types=1);

final class ReporteController
{
    public static function excel(): void
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

        $filename = 'calificaciones_vuela_' . date('Ymd_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store');
        echo "\xEF\xBB\xBF";
        echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel"><head><meta charset="UTF-8"></head><body>';
        echo '<h2>Vuela Internet — Reporte de calificaciones</h2>';
        echo '<p>Generado: ' . e(now()) . ' | Total: ' . (int)$stats['total'] . '</p>';
        echo '<table border="1" cellpadding="4" cellspacing="0">';
        echo '<tr style="background:#F15A22;color:#fff">';
        $cols = ['Fecha','Código','Tipo','Visita N°','Clasificación / Cuestionario','Score','Máx','Etiqueta','Puntos','Agente','Usuario','Rol','IP','Mensaje'];
        foreach ($cols as $c) {
            echo '<th>' . e($c) . '</th>';
        }
        echo '</tr>';
        foreach ($stats['rows'] as $r) {
            echo '<tr>';
            foreach ([
                $r['fecha'] ?? '', $r['codigo'] ?? '', $r['tipo'] ?? '', $r['visita_nro'] ?? '',
                $r['encuesta_tipo'] ?? 'Atención General',
                $r['score'] ?? '', $r['score_max'] ?? '', $r['etiqueta'] ?? '', $r['puntos'] ?? '',
                $r['agente_nombre'] ?? '', $r['agente_usuario'] ?? '', $r['rol'] ?? '', $r['ip'] ?? '',
                $r['mensaje'] ?? '',
            ] as $v) {
                echo '<td>' . e((string)$v) . '</td>';
            }
            echo '</tr>';
        }
        echo '</table></body></html>';
        exit;
    }

    public static function pdf(): void
    {
        require_admin();
        self::htmlPrint('pdf', 'Reporte de calificaciones');
    }

    public static function formularioPdf(): void
    {
        require_admin();
        self::htmlPrint('formulario', 'Formulario oficial de reporte');
    }

    private static function htmlPrint(string $tipo, string $titulo): void
    {
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

        $ranking = Calificacion::ranking();
        header('Cache-Control: no-store');
        view_plain('print/reporte', [
            'titulo' => $titulo,
            'tipo' => $tipo,
            'stats' => $stats,
            'ranking' => $ranking,
            'anio' => $anio,
            'mes' => $mes,
            'encuesta' => $encuesta,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
        ]);
        exit;
    }
}
