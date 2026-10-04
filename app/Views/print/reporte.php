<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= e($titulo) ?></title>
  <style>
    body { font-family: Arial, Helvetica, sans-serif; color: #333; margin: 24px; }
    h1,h2 { color: #F15A22; margin-bottom: 4px; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th, td { border: 1px solid #ddd; padding: 6px 8px; font-size: 12px; }
    th { background: #F15A22; color: #fff; }
    .box { background: #FFF3E0; padding: 10px; border-radius: 6px; margin: 12px 0; }
    @media print { .noprint { display: none; } }
  </style>
</head>
<body>
  <div class="noprint"><button onclick="window.print()">Imprimir / Guardar PDF</button></div>
  <h1>VUELA INTERNET</h1>
  <p><?= e($titulo) ?> · Generado <?= e(now()) ?></p>
  <?php if ($anio || $mes): ?><p>Filtro: <?= e($anio ?: 'todos los años') ?> <?= e($mes) ?></p><?php endif; ?>
  <div class="box"><strong>Total:</strong> <?= (int)$stats['total'] ?></div>
  <h2>Pastel (conteo)</h2>
  <ul>
    <?php foreach ($stats['por_etiqueta'] as $k => $v): ?>
      <li><?= e($k) ?>: <?= (int)$v ?></li>
    <?php endforeach; ?>
  </ul>
  <h2>Ranking</h2>
  <table>
    <tr><th>#</th><th>Agente</th><th>Puntos</th><th>+</th><th>−</th><th>Total</th></tr>
    <?php foreach ($ranking as $i => $a): ?>
      <tr>
        <td><?= $i + 1 ?></td>
        <td><?= e($a['agente_nombre']) ?></td>
        <td><?= (int)$a['puntos'] ?></td>
        <td><?= (int)$a['positivos'] ?></td>
        <td><?= (int)$a['negativos'] ?></td>
        <td><?= (int)$a['total'] ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <h2>Registros</h2>
  <table>
    <tr><th>Fecha</th><th>Código</th><th>Tipo</th><th>Visita</th><th>Score</th><th>Etiqueta</th><th>Agente</th></tr>
    <?php foreach ($stats['rows'] as $r): ?>
      <tr>
        <td><?= e($r['fecha'] ?? '') ?></td>
        <td><?= e($r['codigo'] ?? '') ?></td>
        <td><?= e($r['tipo'] ?? '') ?></td>
        <td><?= e((string)($r['visita_nro'] ?? '')) ?></td>
        <td><?= e(($r['score'] ?? '') . '/' . ($r['score_max'] ?? '')) ?></td>
        <td><?= e($r['etiqueta'] ?? '') ?></td>
        <td><?= e($r['agente_nombre'] ?? '') ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <script>window.addEventListener('load', function(){ setTimeout(function(){ window.print(); }, 400); });</script>
</body>
</html>
