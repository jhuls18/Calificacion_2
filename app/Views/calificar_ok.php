<div class="card card-vuela p-5 text-center bounce-in">
  <?php $buena = !empty($rating['positivo']); ?>
  <div class="display-1 mb-3" style="color:<?= e($rating['color'] ?? '#F15A22') ?>">
    <i class="fa-solid <?= e($rating['icono'] ?? 'fa-face-smile') ?>"></i>
  </div>
  <h2 class="fw-bold"><?= $buena ? '¡Gracias por volar con nosotros!' : 'Registramos su malestar' ?></h2>
  <p class="fs-5"><?= e($rating['mensaje'] ?? '') ?></p>
  <p class="text-muted">
    <?= e($rating['etiqueta'] ?? '') ?> · <?= (int)($rating['score'] ?? 0) ?>/<?= (int)($rating['score_max'] ?? 0) ?>
    · Puntos agente: <b><?= (int)($rating['puntos'] ?? 0) ?></b>
  </p>
  <?php if (!empty($cliente)): ?>
    <p>Código <b><?= e($cliente['codigo'] ?? $rating['codigo'] ?? '') ?></b>
      · Visita n.º <b><?= (int)($cliente['visitas'] ?? $rating['visita_nro'] ?? 1) ?></b>
      · <?= e($rating['tipo'] ?? '') ?></p>
  <?php endif; ?>
  <div class="alert alert-light border mt-3 mb-3 d-inline-block px-4 py-2 rounded-pill shadow-sm">
    <i class="fa-solid fa-clock-rotate-left text-vuela me-2"></i>
    <span>Regresando a la pantalla de calificación en <b id="countdown-sec">4</b>s...</span>
  </div>
  <div class="mt-2">
    <a class="btn btn-vuela btn-lg" href="<?= e(url('/calificar')) ?>"><i class="fa-solid fa-rotate-left me-1"></i> Nueva calificación ahora</a>
    <a class="btn btn-ghost btn-lg ms-2" href="<?= e(url('/panel')) ?>"><i class="fa-solid fa-house me-1"></i> Volver al panel</a>
  </div>
</div>
<div id="ok-flag" data-good="<?= $buena ? '1' : '0' ?>"></div>
<script>
  history.replaceState(null, '', <?= json_encode(url('/calificar/ok')) ?>);
  (function() {
    var sec = 4;
    var el = document.getElementById('countdown-sec');
    var timer = setInterval(function() {
      sec--;
      if (el) el.textContent = sec;
      if (sec <= 0) {
        clearInterval(timer);
        window.location.href = <?= json_encode(url('/calificar')) ?>;
      }
    }, 1000);
  })();
</script>
