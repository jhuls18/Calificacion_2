(function () {
  var form = document.getElementById('form-calificar');
  var codigo = document.getElementById('codigo_cliente');
  var tipo = document.getElementById('tipo');
  var score = document.getElementById('score');
  var encuestaTipo = document.getElementById('encuesta_tipo');
  var info = document.getElementById('cliente-info');
  var feedback = document.getElementById('feedback-dinamico');
  var btnVis = document.getElementById('btn-visitante');
  var lblVisBtn = document.getElementById('lbl-visitante-btn');
  var spectrumFill = document.getElementById('spectrum-fill');
  var preguntaTitulo = document.getElementById('pregunta-encuesta-titulo');
  var caritasContainer = document.getElementById('caritas-container');
  var currentMaxScale = window.VUELA_MAX_CURRENT || 5;
  var t = null;

  function lookup() {
    var c = (codigo.value || '').trim();
    if (!c) {
      info.classList.add('d-none');
      return;
    }
    fetch((window.VUELA_API_CLIENTE || '/api/cliente') + '?codigo=' + encodeURIComponent(c), { cache: 'no-store' })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        info.classList.remove('d-none');
        if (data.existe && data.cliente) {
          info.className = 'alert alert-warning';
          info.innerHTML = '<i class="fa-solid fa-rotate me-1"></i> Cliente <b>' +
            data.cliente.codigo + '</b> ya existe. Visitas previas: <b>' +
            data.cliente.visitas + '</b>. Esta atención sumará la visita <b>' +
            (parseInt(data.cliente.visitas, 10) + 1) + '</b>. El código no se duplica.';
        } else {
          info.className = 'alert alert-success';
          info.innerHTML = '<i class="fa-solid fa-user-plus me-1"></i> Código nuevo. Se registrará como primera visita.';
        }
      })
      .catch(function () {});
  }

  // Desactivar estado del botón visitante cuando se escribe un código o se desmarca
  function resetVisitanteState() {
    if (btnVis) {
      btnVis.classList.remove('btn-visitante-active');
      if (lblVisBtn) lblVisBtn.textContent = 'Visitante / Directo';
    }
  }

  if (codigo) {
    codigo.addEventListener('input', function () {
      tipo.value = 'cliente';
      resetVisitanteState();
      clearTimeout(t);
      t = setTimeout(lookup, 280);
    });
  }

  // TOGGLE (Marcar y Desmarcar) Botón Visitante
  if (btnVis) {
    btnVis.addEventListener('click', function () {
      var isCurrentlyActive = (tipo.value === 'visitante') || btnVis.classList.contains('btn-visitante-active');

      if (isCurrentlyActive) {
        // DESMARCAR / DESACTIVAR
        tipo.value = 'cliente';
        resetVisitanteState();
        if (info) info.classList.add('d-none');
      } else {
        // MARCAR / ACTIVAR
        if (codigo) codigo.value = '';
        tipo.value = 'visitante';
        btnVis.classList.add('btn-visitante-active');
        if (lblVisBtn) lblVisBtn.innerHTML = '<i class="fa-solid fa-circle-check text-white me-1"></i> Modo Visitante Activo';

        if (info) {
          info.className = 'alert alert-info border-0 shadow-sm bounce-in';
          info.classList.remove('d-none');
          info.innerHTML = '<i class="fa-solid fa-person-walking me-1"></i> <strong>Modo Visitante / Directo Activo:</strong> Se registrará la calificación sin requerir código de cliente. <em>(Haz clic de nuevo en el botón para desmarcar)</em>.';
        }
        if (codigo) codigo.required = false;
      }
    });
  }

  function getShortLabel(n, max) {
    var labels11 = [
      'Muy Malo', 'Malo', 'Deficiente', 'Regular', 'Aceptable',
      'Bueno', 'Muy Bueno', 'Destacado', 'Excelente', 'Sobresaliente', 'Wooow!'
    ];
    if (max === 3) {
      if (n === 1) return 'Malo';
      if (n === 2) return 'Regular';
      if (n === 3) return 'Excelente';
    } else if (max === 4) {
      if (n === 1) return 'Malo';
      if (n === 2) return 'Regular';
      if (n === 3) return 'Bueno';
      if (n === 4) return 'Excelente';
    } else if (max === 5) {
      if (n === 1) return 'Malo';
      if (n === 2) return 'Regular';
      if (n === 3) return 'Aceptable';
      if (n === 4) return 'Bueno';
      if (n === 5) return 'Excelente';
    }
    var idx = labels11.length > 1 ? Math.round((n - 1) * (labels11.length - 1) / Math.max(1, max - 1)) : 0;
    return labels11[idx] || ('Opción ' + n);
  }

  // Bind Face Button Event Listeners
  function attachFaceListeners() {
    document.querySelectorAll('.face-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.querySelectorAll('.face-btn').forEach(function (b) {
          b.classList.remove('selected', 'anim-sad', 'anim-happy');
        });
        btn.classList.add('selected');

        var n = parseInt(btn.getAttribute('data-score'), 10);
        var max = parseInt(btn.getAttribute('data-max'), 10) || currentMaxScale;
        var msg = btn.getAttribute('data-msg');
        var icon = btn.getAttribute('data-icon');
        var trel = (n - 1) / Math.max(1, max - 1);
        score.value = n;

        // Llenar Barra de Espectro Dinámica
        if (spectrumFill) {
          var pct = Math.min(100, Math.max(8, Math.round((n / max) * 100)));
          spectrumFill.style.width = pct + '%';

          if (trel <= 0.35) {
            spectrumFill.style.background = 'linear-gradient(90deg, #ef5350 0%, #d32f2f 100%)';
          } else if (trel <= 0.65) {
            spectrumFill.style.background = 'linear-gradient(90deg, #f59e0b 0%, #d97706 100%)';
          } else {
            spectrumFill.style.background = 'linear-gradient(90deg, #10b981 0%, #059669 100%)';
          }
        }

        if (feedback) {
          feedback.classList.remove('d-none');
          feedback.className = 'alert text-center mt-4 bounce-in ' + (trel < 0.45 ? 'alert-danger' : trel < 0.7 ? 'alert-warning' : 'alert-success');
          feedback.innerHTML = '<div class="fs-1 mb-2"><i class="fa-solid ' + (icon || 'fa-star') + '"></i></div><strong>' + msg + '</strong>';
        }

        // Disparar animaciones de reacción
        if (trel >= 0.7) {
          btn.classList.add('anim-happy');
          if (typeof launchConfetti === 'function') launchConfetti();
        } else if (trel <= 0.35) {
          btn.classList.add('anim-sad');
          if (typeof launchSad === 'function') launchSad();
        }
      });
    });
  }

  // Re-render Caritas Container Dynamically according to Questionnaire Scale Size
  function renderCaritasForScale(targetScale) {
    if (!caritasContainer) return;
    targetScale = Math.max(3, Math.min(11, parseInt(targetScale, 10) || 5));
    currentMaxScale = targetScale;
    score.value = '';
    var scoreMaxEl = document.getElementById('score_max');
    if (scoreMaxEl) scoreMaxEl.value = targetScale;
    if (spectrumFill) spectrumFill.style.width = '0%';
    if (feedback) feedback.classList.add('d-none');

    var allNiveles = window.VUELA_NIVELES_ALL || [];
    var html = '';

    for (var i = 1; i <= targetScale; i++) {
      var idx = allNiveles.length > 1 ? Math.round((i - 1) * (allNiveles.length - 1) / (targetScale - 1)) : 0;
      var nDef = allNiveles[idx] || null;

      var color = nDef ? nDef.color : '#f59e0b';
      var icon = nDef ? nDef.icon : 'fa-smile';
      var msg = nDef ? nDef.mensaje : ('Calificación ' + i);
      var label = getShortLabel(i, targetScale);

      html += '<div class="face-wrap">' +
        '<button type="button" class="face-btn face-card-btn" style="background:' + color + '" ' +
        'data-score="' + i + '" data-max="' + targetScale + '" ' +
        'data-msg="' + msg + '" data-icon="' + icon + '" data-label="' + label + '">' +
        '<i class="fa-solid ' + icon + '"></i>' +
        '</button>' +
        '<div class="face-label-sub mt-2">' +
        '<span class="badge rounded-pill text-bg-light border fw-bold px-2 py-1">' + i + '</span>' +
        '<small class="d-block text-dark fw-bold mt-1 fs-7">' + label + '</small>' +
        '</div>' +
        '</div>';
    }

    caritasContainer.innerHTML = html;
    caritasContainer.classList.remove('bounce-in');
    void caritasContainer.offsetWidth; // reflow
    caritasContainer.classList.add('bounce-in');

    attachFaceListeners();
  }

  // Selector de Cuestionario / Tipo de Encuesta
  document.querySelectorAll('.btn-survey-type').forEach(function (tab) {
    tab.addEventListener('click', function () {
      document.querySelectorAll('.btn-survey-type').forEach(function (b) {
        b.classList.remove('btn-vuela', 'active');
        b.classList.add('btn-outline-secondary');
      });
      tab.classList.remove('btn-outline-secondary');
      tab.classList.add('btn-vuela', 'active');

      var tipoVal = tab.getAttribute('data-tipo');
      var pregVal = tab.getAttribute('data-pregunta');
      var escalaVal = parseInt(tab.getAttribute('data-escala'), 10) || currentMaxScale;

      if (encuestaTipo) encuestaTipo.value = tipoVal;
      if (preguntaTitulo && pregVal) {
        preguntaTitulo.classList.remove('bounce-in');
        void preguntaTitulo.offsetWidth; // reflow
        preguntaTitulo.textContent = pregVal;
        preguntaTitulo.classList.add('bounce-in');
      }

      // Re-render Caritas if scale differs!
      renderCaritasForScale(escalaVal);
    });
  });

  attachFaceListeners();

  // DOM Init: Auto-render scale matching the active questionnaire tab on page load
  var activeTab = document.querySelector('.btn-survey-type.active');
  if (activeTab) {
    var initEscala = parseInt(activeTab.getAttribute('data-escala'), 10) || currentMaxScale;
    var initPreg = activeTab.getAttribute('data-pregunta');
    if (preguntaTitulo && initPreg) preguntaTitulo.textContent = initPreg;
    renderCaritasForScale(initEscala);
  }

  function showKioskSuccessOverlay() {
    var overlay = document.getElementById('kiosk-success-overlay');
    if (!overlay) {
      overlay = document.createElement('div');
      overlay.id = 'kiosk-success-overlay';
      overlay.style.cssText = 'position:fixed; inset:0; z-index:999999; background:rgba(255,255,255,0.96); backdrop-filter:blur(15px); display:flex; align-items:center; justify-content:center; text-align:center; padding:2rem;';
      document.body.appendChild(overlay);
    }

    var sec = 4;
    overlay.innerHTML = '<div class="card card-vuela p-5 border-0 shadow-lg bounce-in" style="max-width:500px; width:100%;">' +
      '<div class="display-1 text-vuela mb-3"><i class="fa-solid fa-face-grin-stars"></i></div>' +
      '<h2 class="fw-extrabold text-dark mb-2">¡Gracias por volar con nosotros!</h2>' +
      '<p class="text-muted fs-6 mb-4">Tu calificación fue registrada exitosamente en el sistema.</p>' +
      '<div class="alert alert-light border rounded-pill py-2 px-4 shadow-sm d-inline-block small mb-0">' +
      '<i class="fa-solid fa-clock-rotate-left text-vuela me-2"></i>' +
      'Siguiente calificación en <b id="kiosk-sec-num">4</b>s...' +
      '</div>' +
      '</div>';
    
    if (typeof launchConfetti === 'function') launchConfetti();

    var numEl = document.getElementById('kiosk-sec-num');
    var timer = setInterval(function() {
      sec--;
      if (numEl) numEl.textContent = sec;
      if (sec <= 0) {
        clearInterval(timer);
        overlay.remove();
        if (form) {
          form.reset();
          score.value = '';
          tipo.value = 'cliente';
          if (spectrumFill) spectrumFill.style.width = '0%';
          if (feedback) feedback.classList.add('d-none');
          if (typeof window.resetFormSubmit === 'function') window.resetFormSubmit(form);
        }
      }
    }, 1000);
  }

  if (form) {
    form.addEventListener('submit', function (ev) {
      if (!score.value) {
        ev.preventDefault();
        if (typeof window.resetFormSubmit === 'function') window.resetFormSubmit(form);
        alert('Por favor, seleccione una carita para calificar la atención.');
        return;
      }
      if (tipo.value !== 'visitante' && !(codigo.value || '').trim()) {
        ev.preventDefault();
        if (typeof window.resetFormSubmit === 'function') window.resetFormSubmit(form);
        alert('Ingrese el código de cliente o presione el botón "Visitante / Directo".');
        return;
      }

      var isKiosk = document.body.classList.contains('kiosk-mode') || !!document.fullscreenElement;
      if (isKiosk) {
        ev.preventDefault();
        var submitBtn = form.querySelector('[type=submit]');
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Registrando...';
        }
        var formData = new FormData(form);
        fetch(form.action, {
          method: 'POST',
          body: formData,
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function() { showKioskSuccessOverlay(); })
        .catch(function() { showKioskSuccessOverlay(); });
      }
    });
  }

  // Botón "No Calificó / Cliente se retiró"
  var btnOmitir = document.getElementById('btn-omitir-calificacion');
  if (btnOmitir) {
    btnOmitir.addEventListener('click', function() {
      if (confirm('¿Registrar que el cliente se retiró sin emitir calificación?')) {
        tipo.value = 'omitido';
        score.value = '0';
        if (form) form.submit();
      }
    });
  }

  // Toggle Modo Cliente Limpio
  var btnClean = document.getElementById('btnToggleCleanMode');
  var lblClean = document.getElementById('lblCleanMode');
  if (btnClean) {
    btnClean.addEventListener('click', function() {
      document.body.classList.toggle('clean-kiosk-mode');
      var isClean = document.body.classList.contains('clean-kiosk-mode');
      if (lblClean) {
        lblClean.textContent = isClean ? 'Mostrar Datos Internos' : 'Modo Cliente Limpio (Ocultar Datos Internos)';
      }
      btnClean.className = isClean ? 'btn btn-primary btn-sm rounded-pill px-3 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm' : 'btn btn-outline-primary btn-sm rounded-pill px-3 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm bg-white';
    });
  }
})();
