(function () {
  window.addEventListener('pageshow', function (ev) {
    if (ev.persisted) {
      window.location.reload();
    }
  });

  window.resetFormSubmit = function (form) {
    if (!form) return;
    delete form.dataset.sent;
    var btn = form.querySelector('[type=submit]');
    if (btn) {
      btn.disabled = false;
      if (btn.dataset.origHtml) {
        btn.innerHTML = btn.dataset.origHtml;
      }
    }
  };

  document.querySelectorAll('form[data-once]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (event.defaultPrevented) {
        window.resetFormSubmit(form);
        return;
      }
      if (form.dataset.sent === '1') {
        event.preventDefault();
        return;
      }
      var btn = form.querySelector('[type=submit]');
      if (btn) {
        if (!btn.dataset.origHtml) {
          btn.dataset.origHtml = btn.innerHTML;
        }
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Verificando...';
      }
      form.dataset.sent = '1';
    });
  });

  // Password visibility toggle
  document.querySelectorAll('.btn-pass-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var targetId = btn.getAttribute('data-target');
      var input = document.getElementById(targetId);
      if (!input) return;
      var icon = btn.querySelector('i');
      if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
          icon.classList.remove('fa-eye');
          icon.classList.add('fa-eye-slash');
        }
      } else {
        input.type = 'password';
        if (icon) {
          icon.classList.remove('fa-eye-slash');
          icon.classList.add('fa-eye');
        }
      }
    });
  });

  // Secret Admin Panel Toggle
  var btnAdminSecret = document.getElementById('btnAdminSecret');
  var mainFormBox = document.getElementById('mainLoginFormBox');
  var adminFormBox = document.getElementById('adminLoginFormBox');
  var btnBackToUser = document.getElementById('btnBackToUserLogin');

  function toggleAdminMode(showAdmin) {
    if (!mainFormBox || !adminFormBox) return;
    if (showAdmin) {
      mainFormBox.classList.remove('d-flex');
      mainFormBox.classList.add('d-none');
      adminFormBox.classList.remove('d-none');
      adminFormBox.classList.add('d-flex', 'bounce-in');
      if (btnAdminSecret) btnAdminSecret.classList.add('active');
    } else {
      adminFormBox.classList.remove('d-flex', 'bounce-in');
      adminFormBox.classList.add('d-none');
      mainFormBox.classList.remove('d-none');
      mainFormBox.classList.add('d-flex', 'bounce-in');
      if (btnAdminSecret) btnAdminSecret.classList.remove('active');
    }
  }

  if (btnAdminSecret) {
    btnAdminSecret.addEventListener('click', function () {
      var isAdminVisible = adminFormBox && !adminFormBox.classList.contains('d-none');
      toggleAdminMode(!isAdminVisible);
    });
  }

  if (btnBackToUser) {
    btnBackToUser.addEventListener('click', function () {
      toggleAdminMode(false);
    });
  }

  // Assistant Guide Modal Tab-Switching Logic
  var assistantGuideModal = document.getElementById('assistantGuideModal');
  if (assistantGuideModal) {
    var assistantTabs = assistantGuideModal.querySelectorAll('.btn-assistant-tab');
    var tabCards = assistantGuideModal.querySelectorAll('.assistant-tab-card');

    function switchAssistantTab(tabKey) {
      if (!tabKey) return;

      assistantTabs.forEach(function (tab) {
        if (tab.getAttribute('data-tab') === tabKey) {
          tab.classList.add('active');
        } else {
          tab.classList.remove('active');
        }
      });

      tabCards.forEach(function (card) {
        if (card.id === 'tabCard_' + tabKey) {
          card.classList.remove('d-none');
          card.classList.add('d-block', 'bounce-in');
        } else {
          card.classList.remove('d-block', 'bounce-in');
          card.classList.add('d-none');
        }
      });
    }

    assistantTabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        var tabKey = tab.getAttribute('data-tab');
        switchAssistantTab(tabKey);
      });
    });

    // Auto-select the active page tab and show "Pestaña Actual" badge when modal opens
    assistantGuideModal.addEventListener('show.bs.modal', function () {
      var currentPage = window.VUELA_CURRENT_PAGE || 'panel';
      switchAssistantTab(currentPage);

      document.querySelectorAll('[id^="currentBadge_"]').forEach(function (badge) {
        badge.classList.add('d-none');
      });
      var activeBadge = document.getElementById('currentBadge_' + currentPage);
      if (activeBadge) {
        activeBadge.classList.remove('d-none');
      }
    });
  }

  // Rating Carita Click Interactivity
  var faceBtns = document.querySelectorAll('.face-card-btn');
  var scoreInput = document.getElementById('score');
  var feedbackBox = document.getElementById('feedback-dinamico');

  faceBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {
      faceBtns.forEach(function(b) { b.classList.remove('selected'); });
      btn.classList.add('selected');

      var score = btn.getAttribute('data-score');
      var msg = btn.getAttribute('data-msg');
      var label = btn.getAttribute('data-label');
      var icon = btn.getAttribute('data-icon');

      if (scoreInput) scoreInput.value = score;

      if (feedbackBox) {
        feedbackBox.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');
        if (parseInt(score, 10) >= 4) {
          feedbackBox.classList.add('alert-success');
          launchConfetti();
        } else if (parseInt(score, 10) === 3) {
          feedbackBox.classList.add('alert-warning');
        } else {
          feedbackBox.classList.add('alert-danger');
        }
        feedbackBox.innerHTML = '<i class="fa-solid ' + (icon || 'fa-star') + ' me-2"></i>' + (msg || label);
        feedbackBox.classList.add('bounce-in');
      }
    });
  });

  // Full Screen Kiosk Mode Toggle
  var btnToggleKiosk = document.getElementById('btnToggleKiosk');
  var btnExitKiosk = document.getElementById('btnExitKiosk');

  function enableKioskMode(enable) {
    if (enable) {
      document.body.classList.add('kiosk-mode');
      try { localStorage.setItem('vuela_kiosk_active', '1'); } catch (e) {}
      if (btnExitKiosk) btnExitKiosk.classList.remove('d-none');
      if (document.documentElement.requestFullscreen) {
        document.documentElement.requestFullscreen().catch(function() {});
      }
    } else {
      document.body.classList.remove('kiosk-mode');
      try { localStorage.setItem('vuela_kiosk_active', '0'); } catch (e) {}
      if (btnExitKiosk) btnExitKiosk.classList.add('d-none');
      if (document.exitFullscreen && document.fullscreenElement) {
        document.exitFullscreen().catch(function() {});
      }
    }
  }

  // Auto-restore Kiosk mode if it was enabled
  try {
    if (localStorage.getItem('vuela_kiosk_active') === '1') {
      document.body.classList.add('kiosk-mode');
      if (btnExitKiosk) btnExitKiosk.classList.remove('d-none');
    }
  } catch (e) {}

  if (btnToggleKiosk) {
    btnToggleKiosk.addEventListener('click', function() {
      enableKioskMode(true);
    });
  }

  if (btnExitKiosk) {
    btnExitKiosk.addEventListener('click', function() {
      enableKioskMode(false);
    });
  }

  var ok = document.getElementById('ok-flag');
  if (ok) {
    if (ok.getAttribute('data-good') === '1') launchConfetti();
    else launchSad();
  }

  // --- CONTROL DE SEGURIDAD POR INACTIVIDAD GENERAL (2h 30m) Y SALIDA DE PESTAÑA/BUSCADOR/PANTALLA APAGADA ---
  var lastActivity = Date.now();
  var GENERAL_INACTIVITY_MS = 150 * 60 * 1000; // 2 horas y 30 minutos de inactividad dentro de la página (9,000,000 ms)
  var AWAY_TAB_LIMIT_MS = 30 * 60 * 1000; // 30 minutos máximo fuera de la pestaña / pantalla apagada / buscador
  var tabHiddenTime = 0;

  function resetActivityTimer() {
    lastActivity = Date.now();
  }

  ['mousemove', 'keydown', 'mousedown', 'touchstart', 'scroll', 'click'].forEach(function (evtName) {
    window.addEventListener(evtName, resetActivityTimer, { passive: true });
  });

  // Verificación periódica de inactividad en segundo plano
  setInterval(function () {
    var idleTime = Date.now() - lastActivity;
    if (idleTime >= GENERAL_INACTIVITY_MS) {
      alert('Su sesión ha caducado por inactividad (2 horas y 30 minutos sin uso) por motivos de seguridad.');
      window.location.href = window.VUELA_LOGOUT_URL || '/logout';
    }
  }, 15000);

  // Detectar cuando el usuario cambia de pestaña, minimiza, apaga pantalla o sale del buscador
  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'hidden') {
      tabHiddenTime = Date.now();
    } else if (document.visibilityState === 'visible' && tabHiddenTime > 0) {
      var timeAway = Date.now() - tabHiddenTime;
      tabHiddenTime = 0;
      // Si estuvo fuera de la pestaña, buscador o pantalla apagada más de 30 minutos, cerrar sesión por seguridad
      if (timeAway >= AWAY_TAB_LIMIT_MS) {
        alert('Estuvo fuera de la pestaña o con la pantalla apagada por más de 30 minutos. Su sesión ha sido cerrada por seguridad.');
        window.location.href = window.VUELA_LOGOUT_URL || '/logout';
      }
    }
  });

  // Registro de sesión activa de pestaña en sessionStorage
  try {
    if (window.sessionStorage) {
      sessionStorage.setItem('vuela_active_tab', '1');
    }
  } catch (e) {}
})();

function hideSplash(id, delay) {
  window.addEventListener('load', function () {
    setTimeout(function () {
      var el = document.getElementById(id || 'splash');
      if (!el) return;
      el.classList.add('hide');
      setTimeout(function () { el.style.display = 'none'; }, 500);
    }, delay || 1600);
  });
}

function launchConfetti() {
  var box = document.createElement('div');
  box.className = 'confetti';
  document.body.appendChild(box);
  var colors = ['#F15A22', '#ffc107', '#28a745', '#fff', '#ff8a4a'];
  for (var i = 0; i < 70; i++) {
    var p = document.createElement('i');
    p.style.left = Math.random() * 100 + '%';
    p.style.background = colors[i % colors.length];
    p.style.animationDelay = (Math.random() * 0.6) + 's';
    box.appendChild(p);
  }
  setTimeout(function () { box.remove(); }, 2200);
}

function launchSad() {
  var box = document.createElement('div');
  box.className = 'sad-rain';
  document.body.appendChild(box);
  for (var i = 0; i < 28; i++) {
    var d = document.createElement('span');
    d.textContent = '💧';
    d.style.left = Math.random() * 100 + '%';
    d.style.animationDelay = (Math.random() * 0.5) + 's';
    box.appendChild(d);
  }
  setTimeout(function () { box.remove(); }, 2000);
}
