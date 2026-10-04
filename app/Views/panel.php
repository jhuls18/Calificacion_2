<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
  <div>
    <h2 class="fw-extrabold mb-0" style="color:var(--ink)">Dashboard de Control · <?= e($user['rol']) ?></h2>
    <p class="text-muted mb-0"><i class="fa-solid fa-clock-rotate-left me-1"></i>Último acceso: <span class="fw-semibold text-dark"><?= e($user['last_login'] ?? 'Hoy') ?></span> · IP <?= e($user['last_ip'] ?? '127.0.0.1') ?></p>
  </div>
  <a class="btn btn-vuela btn-lg px-4 py-2 d-flex align-items-center gap-2" href="<?= e(url('/calificar')) ?>">
    <i class="fa-solid fa-star fs-5"></i>
    <span>Calificar Atención</span>
  </a>
</div>

<!-- Tarjetas de KPIs Animadas -->
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="stat card-kpi-item p-3 border rounded-4 bg-white shadow-sm h-100 d-flex align-items-center gap-3">
      <div class="kpi-icon-box bg-orange-subtle text-vuela"><i class="fa-solid fa-user-check fs-3"></i></div>
      <div>
        <div class="text-muted small fw-semibold">Mis Atenciones</div>
        <b class="fs-2 text-dark"><?= (int)$mias ?></b>
      </div>
    </div>
  </div>
  
  <div class="col-6 col-lg-3">
    <div class="stat card-kpi-item p-3 border rounded-4 bg-white shadow-sm h-100 d-flex align-items-center gap-3">
      <div class="kpi-icon-box bg-warning-subtle text-warning"><i class="fa-solid fa-trophy fs-3"></i></div>
      <div>
        <div class="text-muted small fw-semibold">Mis Puntos</div>
        <b class="fs-2 text-dark"><?= (int)$puntos ?></b>
      </div>
    </div>
  </div>

  <div class="col-6 col-lg-3">
    <div class="stat card-kpi-item p-3 border rounded-4 bg-white shadow-sm h-100 d-flex align-items-center gap-3">
      <div class="kpi-icon-box bg-primary-subtle text-primary"><i class="fa-solid fa-chart-simple fs-3"></i></div>
      <div>
        <div class="text-muted small fw-semibold">Total Sistema</div>
        <b class="fs-2 text-dark"><?= (int)$stats['total'] ?></b>
      </div>
    </div>
  </div>

  <div class="col-6 col-lg-3">
    <div class="stat card-kpi-item p-3 border rounded-4 bg-white shadow-sm h-100 d-flex align-items-center gap-3">
      <div class="kpi-icon-box bg-success-subtle text-success"><i class="fa-solid fa-face-smile fs-3"></i></div>
      <div>
        <div class="text-muted small fw-semibold">Escala Caritas</div>
        <b class="fs-2 text-dark"><?= (int)($cfg['niveles_caritas'] ?? 5) ?></b>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <!-- Gráfica de Dona (Doughnut Chart) -->
  <div class="col-lg-6">
    <div class="card card-vuela p-4 h-100">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-chart-pie me-2 text-vuela"></i>Distribución de Respuestas</h5>
        <span class="badge text-bg-light border fw-bold"><?= (int)$stats['total'] ?> registros</span>
      </div>
      <div style="position: relative; height:280px; width:100%">
        <canvas id="piePanel"></canvas>
      </div>
    </div>
  </div>

  <!-- Termómetro de Satisfacción Animado -->
  <div class="col-lg-6">
    <div class="card card-vuela p-4 h-100">
      <h5 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-temperature-half me-2 text-danger"></i>Termómetro de Satisfacción</h5>
      
      <?php
      $totalVotos = (int)($stats['total'] ?? 0);
      $totalPuntos = 0;
      $maxScore = (int)($cfg['niveles_caritas'] ?? 5);
      foreach ($stats['rows'] as $r) {
        $totalPuntos += (int)($r['score'] ?? 0);
      }
      $satisfactionPercent = $totalVotos > 0 ? min(100, max(0, round(($totalPuntos / ($totalVotos * $maxScore)) * 100))) : 85;
      ?>

      <div class="d-flex align-items-center justify-content-around h-100 py-2">
        <!-- Termómetro Visual -->
        <div class="thermometer-container">
          <div class="thermometer-fill" style="height: <?= $satisfactionPercent ?>%;"></div>
          <div class="thermometer-bulb">
            <?php if ($satisfactionPercent >= 80): ?>
              <i class="fa-solid fa-face-grin-stars text-success"></i>
            <?php elseif ($satisfactionPercent >= 60): ?>
              <i class="fa-solid fa-face-smile text-info"></i>
            <?php elseif ($satisfactionPercent >= 40): ?>
              <i class="fa-solid fa-face-meh text-warning"></i>
            <?php else: ?>
              <i class="fa-solid fa-face-frown text-danger"></i>
            <?php endif; ?>
          </div>
        </div>

        <div class="text-center text-md-start">
          <span class="text-muted fw-semibold d-block text-uppercase tracking-wider small">Índice de Satisfacción</span>
          <h1 class="display-3 fw-extrabold my-1 text-vuela"><?= $satisfactionPercent ?>%</h1>
          <p class="text-muted small mb-3">Calculado dinámicamente con los votos acumulados del sistema.</p>

          <ul class="kpi-list">
            <li>
              <div class="kpi-number">1</div>
              <div><strong class="d-block text-dark fs-7">Satisfacción General</strong><small class="text-muted"><?= $satisfactionPercent ?>% clientes satisfechos</small></div>
            </li>
            <li>
              <div class="kpi-number bg-success">2</div>
              <div><strong class="d-block text-dark fs-7">Atenciones Totales</strong><small class="text-muted"><?= $totalVotos ?> atenciones registradas</small></div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Tabla de Ranking de Agentes -->
<div class="card card-vuela p-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center gap-2">
      <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-ranking-star me-2 text-warning"></i>Ranking de Agentes en Vivo</h5>
      <span class="badge text-bg-danger rounded-pill px-2 py-1 small"><i class="fa-solid fa-circle text-white me-1 pulse"></i>EN VIVO</span>
    </div>
    <div class="d-flex gap-2">
      <?php if (is_admin($user ?? null)): ?>
        <a class="btn btn-sm btn-vuela rounded-pill px-3" href="<?= e(url('/admin/ranking')) ?>">
          <i class="fa-solid fa-trophy me-1"></i> Ranking Completo & Mi Ficha
        </a>
      <?php endif; ?>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table align-middle table-hover">
      <thead>
        <tr>
          <th>#</th>
          <th>Agente</th>
          <th>Rol</th>
          <th>Puntos</th>
          <th class="text-success">+ Positivos</th>
          <th class="text-danger">− Negativos</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($ranking as $i => $a): ?>
        <tr>
          <td>
            <?php if ($i === 0): ?>
              <span class="badge text-bg-warning rounded-circle p-2"><i class="fa-solid fa-crown"></i></span>
            <?php else: ?>
              <span class="fw-bold text-muted">#<?= $i + 1 ?></span>
            <?php endif; ?>
          </td>
          <td>
            <strong class="text-dark"><?= e($a['agente_nombre']) ?></strong>
            <small class="d-block text-muted">@<?= e($a['agente_usuario']) ?></small>
          </td>
          <td><span class="badge badge-rol"><?= e($a['rol']) ?></span></td>
          <td class="fw-extrabold text-vuela fs-5"><?= (int)$a['puntos'] ?></td>
          <td class="text-success fw-bold">+<?= (int)$a['positivos'] ?></td>
          <td class="text-danger fw-bold">−<?= (int)$a['negativos'] ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$ranking): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">Aún no hay calificaciones registradas.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
new Chart(document.getElementById('piePanel'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode(array_keys($stats['por_etiqueta']), JSON_UNESCAPED_UNICODE) ?>,
    datasets: [{
      data: <?= json_encode(array_values($stats['por_etiqueta'])) ?>,
      backgroundColor: ['#26a69a', '#66bb6a', '#ffca28', '#ef5350', '#F15A22', '#3b82f6'],
      borderWidth: 0,
      hoverOffset: 12
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: 'right', labels: { font: { family: 'Plus Jakarta Sans', size: 13 }, usePointStyle: true } }
    },
    cutout: '68%',
    animation: { animateScale: true, animateRotate: true }
  }
});
</script>
