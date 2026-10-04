<?php
$splashId = $splashId ?? 'splash';
$splashText = $splashText ?? 'vuela internet';
?>
<div id="<?= e($splashId) ?>" class="splash-screen">
  <div class="splash-bg-glow"></div>
  <div class="splash-content text-center">
    <div class="splash-logo-wrap mb-4">
      <img src="<?= e(asset('img/logo-vuela.png')) ?>" alt="Vuela" class="splash-logo">
      <div class="logo-pulse"></div>
    </div>
    
    <div id="wifi-loader" class="mx-auto my-4">
      <svg class="circle-outer" viewBox="0 0 86 86">
        <circle class="back" cx="43" cy="43" r="40"></circle>
        <circle class="front" cx="43" cy="43" r="40"></circle>
      </svg>
      <svg class="circle-middle" viewBox="0 0 60 60">
        <circle class="back" cx="30" cy="30" r="27"></circle>
        <circle class="front" cx="30" cy="30" r="27"></circle>
      </svg>
      <svg class="circle-inner" viewBox="0 0 34 34">
        <circle class="back" cx="17" cy="17" r="14"></circle>
        <circle class="front" cx="17" cy="17" r="14"></circle>
      </svg>
    </div>
    
    <div class="splash-status mt-3">
      <div class="splash-bar mb-2"><div class="splash-bar-fill"></div></div>
      <p class="splash-text mb-0">Cargando Portal de Atención...</p>
    </div>
  </div>
</div>
