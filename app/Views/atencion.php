<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
  <div>
    <h2 class="fw-bold mb-0" style="color:var(--vuela)"><i class="fa-solid fa-headset me-2"></i>Herramientas de Atención Vuela</h2>
    <p class="text-muted mb-0">Calculadora de permanencia (18M y 12M Excepcional), protocolo de soluciones y retención.</p>
  </div>
  <a class="btn btn-vuela" href="<?= e(url('/calificar')) ?>">
    <i class="fa-solid fa-face-smile me-2"></i>Ir a Calificar
  </a>
</div>

<!-- Selector de Tipo de Contrato: 18 Meses vs 12 Meses Excepcional -->
<div class="card card-vuela p-3 mb-4 bg-white border shadow-sm">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div class="d-flex align-items-center gap-2">
      <i class="fa-solid fa-file-contract fs-4 text-vuela"></i>
      <div>
        <h5 class="fw-bold text-dark mb-0">Modalidad de Contrato a Evaluar</h5>
        <small class="text-muted">Seleccione la permanencia estipulada en el contrato suscrito por el cliente.</small>
      </div>
    </div>
    <div class="btn-group rounded-pill p-1 bg-light border" role="group" id="contractTypeGroup">
      <input type="radio" class="btn-check" name="contract_type" id="contract18" value="18" checked autocomplete="off">
      <label class="btn btn-sm btn-vuela rounded-pill px-3 py-2 fw-bold" for="contract18">
        <i class="fa-solid fa-shield-check me-1"></i> Contrato Estándar (18 Meses)
      </label>

      <input type="radio" class="btn-check" name="contract_type" id="contract12" value="12" autocomplete="off">
      <label class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 fw-bold" for="contract12">
        <i class="fa-solid fa-star me-1"></i> Contrato Excepcional (12 Meses)
      </label>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <!-- Calculadora de Penalidad de Permanencia (18M o 12M) -->
  <div class="col-lg-6">
    <div class="card card-vuela p-4 h-100">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
          <i class="fa-solid fa-calculator fs-3 text-warning"></i>
          <h4 class="fw-bold mb-0">Calculadora de Penalidad</h4>
        </div>
        <span class="badge text-bg-warning fs-7 px-3 py-2 fw-bold">Bs. 159.00 / mes</span>
      </div>
      <p class="text-muted small mb-3">Seleccione las fechas de instalación, solicitud de baja y el periodo de bloqueo temporal para calcular automáticamente la multa de rescisión.</p>

      <!-- Fechas de Contrato -->
      <div class="p-3 bg-light border rounded-4 mb-3">
        <h6 class="fw-bold text-dark small mb-2"><i class="fa-solid fa-calendar-days text-primary me-1"></i> 1. Fechas del Contrato</h6>
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label fw-bold fs-8 text-dark mb-1">Mes de Instalación</label>
            <input type="month" class="form-control form-control-sm fw-bold" id="calc-fecha-inicio" value="<?= date('Y-m', strtotime('-6 months')) ?>">
          </div>
          <div class="col-6">
            <label class="form-label fw-bold fs-8 text-dark mb-1">Mes de Baja Solicitada</label>
            <input type="month" class="form-control form-control-sm fw-bold" id="calc-fecha-fin" value="<?= date('Y-m') ?>">
          </div>
        </div>
      </div>

      <!-- Fechas de Bloqueo Temporal Solicitado -->
      <div class="p-3 bg-light border rounded-4 mb-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <h6 class="fw-bold text-dark small mb-0"><i class="fa-solid fa-snowflake text-info me-1"></i> 2. Periodo de Bloqueo Temporal (Si solicitó)</h6>
          <span class="badge text-bg-info text-white fs-8" id="lbl-bloqueo-meses-calc">0 meses de bloqueo</span>
        </div>
        <div class="row g-2 mb-2">
          <div class="col-6">
            <label class="form-label fw-bold fs-8 text-dark mb-1">Inicio de Bloqueo</label>
            <input type="month" class="form-control form-control-sm fw-bold" id="calc-bloqueo-inicio" value="<?= date('Y-m', strtotime('-6 months')) ?>">
          </div>
          <div class="col-6">
            <label class="form-label fw-bold fs-8 text-dark mb-1">Fin de Bloqueo</label>
            <input type="month" class="form-control form-control-sm fw-bold" id="calc-bloqueo-fin" value="<?= date('Y-m', strtotime('-6 months')) ?>">
          </div>
        </div>
        <small class="text-muted fs-8 d-block">* Los meses en bloqueo congelan el servicio y no cuentan como permanencia pagada.</small>
      </div>

      <!-- Ajuste Manual / Slider de Meses Activos -->
      <div class="mb-3">
        <label class="form-label fw-bold d-flex justify-content-between small text-dark mb-1">
          <span>Meses Activos Transcurridos:</span>
          <span class="badge text-bg-primary fs-7" id="calc-meses-val">6 meses</span>
        </label>
        <input type="range" class="form-range" id="calc-meses" min="0" max="18" value="6" step="1">
      </div>

      <!-- Botón para Ejecutar / Recalcular Penalidad -->
      <button type="button" class="btn btn-vuela btn-sm w-100 fw-bold py-2 shadow-sm mb-3" id="btn-calcular-multa">
        <i class="fa-solid fa-calculator me-1"></i> Calcular Penalidad y Meses Restantes
      </button>

      <!-- Caja de Resultados de Penalidad -->
      <div class="stat text-center bg-light border p-3 rounded-4" id="box-resultado-calc">
        <div class="row g-2 mb-2 text-muted small">
          <div class="col-6 border-end">Transcurridos: <strong id="lbl-transcurridos-total" class="text-dark">6 meses</strong></div>
          <div class="col-6">Bloqueo Deducido: <strong id="lbl-bloqueo-total" class="text-info">0 meses</strong></div>
        </div>
        <hr class="my-2">
        <div class="text-muted small fw-semibold">Meses Restantes para Cumplir Permanencia (<span id="lbl-contract-max-months">18M</span>)</div>
        <b id="calc-restantes" class="text-dark display-6 d-block my-1">12 meses</b>
        <div class="text-muted small fw-semibold">Penalidad Estimada por Rescisión Anticipada</div>
        <b id="calc-resultado" style="color:var(--vuela)" class="display-5 d-block">Bs. 1,908.00</b>
      </div>
    </div>
  </div>

  <!-- Checklist de Requisitos y Condiciones Principales del Contrato -->
  <div class="col-lg-6">
    <div class="card card-vuela p-4 h-100">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
          <i class="fa-solid fa-file-contract fs-3 text-success"></i>
          <h4 class="fw-bold mb-0">Condiciones Principales del Contrato</h4>
        </div>
        <button type="button" class="btn btn-sm btn-outline-success rounded-pill fw-bold px-3 py-1" id="btn-marcar-todos">
          <i class="fa-solid fa-check-double me-1"></i> Marcar Todas las Opciones
        </button>
      </div>
      <p class="text-muted small mb-3">Requisitos y condiciones contractuales de Vuela Internet a verificar antes de procesar cualquier baja:</p>

      <div class="list-group list-group-flush mb-4">
        <label class="list-group-item d-flex gap-3 align-items-center bg-transparent py-2 cursor-pointer">
          <input class="form-check-input flex-shrink-0 chk-condicion" type="checkbox" id="chk1" checked>
          <span>
            <strong class="text-dark small">1. Permanencia Obligatoria (<span id="chk-permanencia-lbl">18 Meses</span>):</strong>
            <small class="d-block text-muted fs-8">Todo contrato está sujeto a un periodo de fidelidad activo.</small>
          </span>
        </label>
        <label class="list-group-item d-flex gap-3 align-items-center bg-transparent py-2 cursor-pointer">
          <input class="form-check-input flex-shrink-0 chk-condicion" type="checkbox" id="chk2" checked>
          <span>
            <strong class="text-dark small">2. Penalidad por Rescisión Anticipada (Bs. 159/mes):</strong>
            <small class="d-block text-muted fs-8">Si el cliente solicita la baja antes del plazo, cancela la multa por mes faltante.</small>
          </span>
        </label>
        <label class="list-group-item d-flex gap-3 align-items-center bg-transparent py-2 cursor-pointer">
          <input class="form-check-input flex-shrink-0 chk-condicion" type="checkbox" id="chk3">
          <span>
            <strong class="text-dark small">3. Suspensión / Bloqueo Temporal (Máx. 2 Meses):</strong>
            <small class="d-block text-muted fs-8">Pausar el servicio hasta 2 meses por año sin multa (congelado no suma como permanencia).</small>
          </span>
        </label>
        <label class="list-group-item d-flex gap-3 align-items-center bg-transparent py-2 cursor-pointer">
          <input class="form-check-input flex-shrink-0 chk-condicion" type="checkbox" id="chk4">
          <span>
            <strong class="text-dark small">4. Devolución de Equipos en Comodato (ONU / Módem):</strong>
            <small class="d-block text-muted fs-8">Entregar equipo router/ONU y transformador en perfecto estado en oficina.</small>
          </span>
        </label>
        <label class="list-group-item d-flex gap-3 align-items-center bg-transparent py-2 cursor-pointer">
          <input class="form-check-input flex-shrink-0 chk-condicion" type="checkbox" id="chk5">
          <span>
            <strong class="text-dark small">5. Estado de Cuenta al Día y Titularidad (C.I.):</strong>
            <small class="d-block text-muted fs-8">Cédula del titular y saldo al día (0 Bs. pendientes).</small>
          </span>
        </label>
      </div>

      <div class="p-3 bg-light border rounded-4" id="evaluacion-baja-box">
        <h6 class="fw-bold text-dark small mb-1" id="evaluacion-baja-titulo"><i class="fa-solid fa-circle-info text-warning me-1"></i> Evaluador Operativo de Bajas</h6>
        <p class="small text-muted mb-0" id="evaluacion-baja-desc">Si el cliente cumple los meses de permanencia, la rescisión es gratuita previa devolución de equipos y C.I. del titular.</p>
      </div>
    </div>
  </div>
</div>

<!-- CUADRO COMPLETO DE SOLUCIONES, ASISTENCIAS, COMPROMISOS Y PROMOCIONES DE RETENCIÓN -->
<div class="card card-vuela p-4 mb-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center gap-2">
      <i class="fa-solid fa-hand-holding-hand fs-3 text-vuela"></i>
      <h4 class="fw-bold mb-0">Cuadro General de Soluciones, Asistencias y Promociones</h4>
    </div>
    <span class="badge text-bg-success fs-7 px-3 py-2 fw-bold"><i class="fa-solid fa-shield-heart me-1"></i>Matriz Integrada de Retención</span>
  </div>
  <p class="text-muted small mb-3">Opciones de soporte técnico, planes, descuentos y compromisos para ofrecer al cliente antes de registrar la baja:</p>

  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <div class="border rounded-4 p-3 bg-light h-100">
        <div class="fw-bold text-dark mb-2"><i class="fa-solid fa-wrench text-danger me-1"></i> 1. Soporte / Visita Técnica</div>
        <p class="fs-8 text-muted mb-2">Visita técnica gratuita, cambio de clave, reubicación de módem o revisión de fibra óptica.</p>
        <div class="form-check form-check-inline fs-8">
          <input class="form-check-input" type="checkbox" id="sol_soporte">
          <label class="form-check-label text-dark fw-bold" for="sol_soporte">Asistencia NOC Aplicada</label>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="border rounded-4 p-3 bg-light h-100">
        <div class="fw-bold text-dark mb-2"><i class="fa-solid fa-handshake text-warning me-1"></i> 2. Compromiso de Pago</div>
        <p class="fs-8 text-muted mb-2">Prórroga de fecha de pago sin corte para clientes con dificultad momentánea de pago.</p>
        <div class="form-check form-check-inline fs-8">
          <input class="form-check-input" type="checkbox" id="sol_compromiso">
          <label class="form-check-label text-dark fw-bold" for="sol_compromiso">Prórroga Registrada</label>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="border rounded-4 p-3 bg-light h-100">
        <div class="fw-bold text-dark mb-2"><i class="fa-solid fa-tags text-success me-1"></i> 3. Descuento / Plan Promocional</div>
        <p class="fs-8 text-muted mb-2">Descuento temporal (20%-50%), cambio a plan de menor tarifa o bonificación de velocidad.</p>
        <div class="form-check form-check-inline fs-8">
          <input class="form-check-input" type="checkbox" id="sol_descuento">
          <label class="form-check-label text-dark fw-bold" for="sol_descuento">Promoción / Descuento</label>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="border rounded-4 p-3 bg-light h-100">
        <div class="fw-bold text-dark mb-2"><i class="fa-solid fa-truck-ramp-box text-primary me-1"></i> 4. Traslado o Congelamiento</div>
        <p class="fs-8 text-muted mb-2">Mudar la instalación conservando la antigüedad o activar Bloqueo Temporal sin multa.</p>
        <div class="form-check form-check-inline fs-8">
          <input class="form-check-input" type="checkbox" id="sol_traslado">
          <label class="form-check-label text-dark fw-bold" for="sol_traslado">Traslado / Pausa 2M</label>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- MÓDULO DE PRE-PAGO, CALENDARIO ANIMADO DE FECHAS Y PLATAFORMA DE PAGO (pagos.vuela.bo) -->
<div class="card card-vuela p-4 mb-4 border border-success-subtle bg-white shadow-sm">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div class="d-flex align-items-center gap-2">
      <i class="fa-solid fa-calendar-days fs-3 text-success"></i>
      <div>
        <h4 class="fw-bold mb-0 text-dark">Modalidad Pre-Pago y Calendario Animado de Facturación</h4>
        <small class="text-muted">Cronograma oficial de facturación mensual y plataforma directa <code>pagos.vuela.bo</code></small>
      </div>
    </div>
    
    <div class="d-flex gap-2">
      <a href="https://pagos.vuela.bo/" target="_blank" rel="noopener" class="btn btn-success btn-sm fw-bold px-3 py-2 rounded-pill shadow-sm hover-glow-btn">
        <i class="fa-solid fa-globe me-1"></i> Abrir pagos.vuela.bo 🌐
      </a>
      <button type="button" class="btn btn-outline-success btn-sm fw-bold px-3 py-2 rounded-pill" onclick="navigator.clipboard.writeText('https://pagos.vuela.bo/'); alert('¡Enlace https://pagos.vuela.bo/ copiado!');">
        <i class="fa-solid fa-copy me-1"></i> Copiar Enlace
      </button>
    </div>
  </div>

  <!-- CALENDARIO ANIMADO DE LÍNEA DE TIEMPO DEL PRE-PAGO -->
  <div class="p-3 bg-light border rounded-4 mb-4">
    <h6 class="fw-bold text-dark small mb-3"><i class="fa-solid fa-timeline text-vuela me-2"></i> Flujo Mensual de Facturación Pre-Pago (Del 1ro al 16 de cada mes):</h6>
    
    <div class="pago-timeline-wrap">
      <!-- Paso 1: Día 1 se genera la deuda -->
      <div class="pago-step-card">
        <div class="pago-step-icon pago-icon-1">
          <i class="fa-solid fa-calendar-plus"></i>
        </div>
        <span class="badge text-bg-warning fw-bold mb-2">Día 1ro del Mes</span>
        <h6 class="fw-bold text-dark mb-1 fs-7">1. Se Genera la Deuda</h6>
        <p class="text-muted fs-8 mb-0">El sistema calcula la facturación correspondiente al mes en curso.</p>
      </div>

      <!-- Paso 2: Días 1 al 15 Pago Habilitado -->
      <div class="pago-step-card border-success border-2">
        <div class="pago-step-icon pago-icon-15">
          <i class="fa-solid fa-qrcode"></i>
        </div>
        <span class="badge text-bg-success fw-bold mb-2">Días 1 al 15</span>
        <h6 class="fw-bold text-dark mb-1 fs-7">2. Periodo de Pago QR</h6>
        <p class="text-muted fs-8 mb-0">Cancelación habilitada en <strong>pagos.vuela.bo</strong> sin cortes ni recargos.</p>
      </div>

      <!-- Paso 3: Día 16 Cortes Automáticos -->
      <div class="pago-step-card border-danger">
        <div class="pago-step-icon pago-icon-16">
          <i class="fa-solid fa-bolt"></i>
        </div>
        <span class="badge text-bg-danger fw-bold mb-2">Día 16 en Adelante</span>
        <h6 class="fw-bold text-dark mb-1 fs-7">3. Corte Automático</h6>
        <p class="text-muted fs-8 mb-0">El sistema ejecuta cortes administrativos por falta de cancelación.</p>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <!-- Aviso Informativo Pre-Pago -->
    <div class="col-lg-6">
      <div class="p-3 bg-white border border-success-subtle rounded-4 h-100 shadow-sm">
        <h6 class="fw-bold text-success mb-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> Condición Pre-Pago y Evitación de Acumulación</h6>
        <p class="small text-dark mb-2">
          Estimado cliente, te recordamos que nuestro servicio funciona bajo la modalidad de <strong>pre pago del 1 al 15 de cada mes</strong>. Es muy importante realizar tu cancelación dentro de este periodo; de lo contrario, a partir del día 16 el sistema genera cortes administrativos automáticos por falta de pago.
        </p>
        <div class="alert alert-warning border-0 small py-2 px-3 rounded-3 mb-0">
          <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>
          Ten en cuenta que si el servicio queda inactivo o no se utiliza, se pueden acumular deudas mensuales según los periodos transcurridos.
        </div>
      </div>
    </div>

    <!-- Guía Paso a Paso QR Simple -->
    <div class="col-lg-6">
      <div class="p-3 bg-white border border-primary-subtle rounded-4 h-100 shadow-sm">
        <h6 class="fw-bold text-primary mb-2"><i class="fa-solid fa-mobile-screen-button me-1"></i> Paso a Paso para Pago por QR Simple (pagos.vuela.bo)</h6>
        <ol class="small text-dark mb-0 ps-3">
          <li class="mb-1">Ingresa a nuestra plataforma oficial: <code>pagos.vuela.bo</code> 🌐</li>
          <li class="mb-1">Escribe tu código de cliente en el espacio indicado 🔍</li>
          <li class="mb-1">Selecciona tu pago pendiente y haz clic en <strong>Pagar</strong> 💳</li>
          <li class="mb-1">Presiona <strong>Generar QR Simple</strong> (Validez de 10 minutos) ⏱️</li>
          <li>Abre la App de tu banco, escanea el código QR y ¡listo! 🎉</li>
        </ol>
      </div>
    </div>
  </div>

  <!-- Plantilla Completa de Mensaje para Enviar al Cliente -->
  <div class="p-3 bg-white border rounded-4 shadow-sm">
    <label class="form-label fw-bold text-dark small mb-1"><i class="fa-solid fa-comment-dots text-success me-1"></i> Mensaje Oficial para Copiar y Enviar al Cliente:</label>
    <textarea id="tpl-pago-qr" class="form-control form-control-sm text-muted bg-light mb-2 fw-medium" rows="8" readonly style="resize:none; font-size:0.83rem;">Estimado cliente, te recordamos que nuestro servicio funciona bajo la modalidad de pre pago del 1 al 15 de cada mes. Es muy importante realizar tu cancelación dentro de este periodo; de lo contrario, a partir del día 16 el sistema genera cortes administrativos automáticos por falta de pago. Ten en cuenta que si el servicio queda inactivo o no se utiliza, se pueden acumular deudas mensuales según los periodos transcurridos, por lo que te invitamos a mantenerte al día para evitar acumulaciones o la suspensión de tu conexión. ¡Estamos para ayudarte! 😊📱✨

¡Esperamos su pago y que tenga una buena noche!

📱✨ Para que puedas realizar tu pago de forma rápida, segura y sin confusiones desde tu celular o computadora, aquí te detallo el paso a paso:

Paso 1: Ingresa a nuestra plataforma oficial escribiendo en tu navegador web: pagos.vuela.bo 🌐
Paso 2: Escribe tu código de cliente en el espacio indicado para buscar tu cuenta 🔍
Paso 3: Selecciona tu pago pendiente y haz clic en Pagar 💳
Paso 4: Presiona el botón Generar QR Simple (el código QR generado tendrá una validez de 10 minutos) ⏱️
Paso 5: Entra a la aplicación de tu banco preferido, escanea el código QR y listo, ¡tu pago quedará registrado con éxito! 🎉</textarea>
    <div class="text-end">
      <button class="btn btn-success btn-sm fw-bold px-4 py-2" onclick="copyTpl('tpl-pago-qr', this)">
        <i class="fa-solid fa-copy me-1"></i> Copiar Mensaje Completo de Pago
      </button>
    </div>
  </div>
</div>

<!-- MÓDULO ILUSTRADO Y ANIMADO: COBERTURA ROUTER (8m), 2.4G/5G Y EQUIPOS MESH -->
<div class="card card-vuela p-4 mb-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center gap-2">
      <i class="fa-solid fa-wifi fs-3 text-vuela"></i>
      <h4 class="fw-bold mb-0">Cobertura del Router (8m), Wi-Fi 2.4G / 5G y Equipos Mesh</h4>
    </div>
    <span class="badge text-bg-warning fs-7 px-3 py-2 fw-bold"><i class="fa-solid fa-tower-cell me-1"></i>Alcance y Solución Mesh</span>
  </div>

  <div class="row g-4 align-items-center mb-3">
    <div class="col-lg-6">
      <div class="p-3 bg-light border rounded-4">
        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-ruler-horizontal text-vuela me-1"></i> Alcance Máximo del Router (8 Metros a la Redonda)</h6>
        <p class="small text-muted mb-2">
          La cobertura estándar de un módem/ONU Wi-Fi abarca aproximadamente <strong>8 metros a la redonda libre de obstáculos</strong>.
        </p>
        <ul class="small text-dark mb-3 ps-3">
          <li><strong>Señal 5 GHz:</strong> Altísima velocidad, ideal para juegos y streaming. Menor alcance tras muros gruesos.</li>
          <li><strong>Señal 2.4 GHz:</strong> Mayor penetración en paredes y cuartos lejanos. Velocidad moderada.</li>
          <li><strong>Paredes y Obstáculos:</strong> Muros de hormigón, vidrio o cerámica reducen la potencia Wi-Fi hasta un 60%.</li>
        </ul>
        <div class="alert alert-info border-0 small py-2 px-3 rounded-3 mb-0">
          <i class="fa-solid fa-cubes text-info me-1"></i> <strong>Solución Equipos Mesh:</strong> Repetidores inteligentes Mesh extienden la misma red Wi-Fi por toda la casa sin caídas de señal.
        </div>
      </div>
    </div>

    <!-- Animación CSS Ilustrada de Ondas Router y MESH -->
    <div class="col-lg-6">
      <div class="p-4 bg-dark text-white rounded-4 text-center position-relative overflow-hidden" style="min-height:220px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
        <div class="mb-2 text-warning fw-bold small"><i class="fa-solid fa-satellite-dish me-1"></i> Visualización de Ondas Wi-Fi y Nodos Mesh</div>
        
        <div class="d-flex justify-content-around align-items-center my-3 position-relative z-2">
          <!-- Router Principal con diseño de imagen -->
          <div class="text-center">
            <div class="wifi-router-animated-box mx-auto mb-2" style="width:70px;height:70px;background:rgba(241,90,34,0.12);">
              <!-- SVG Router Design -->
              <svg width="42" height="42" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="8" y="34" width="48" height="18" rx="6" fill="#F15A22"/>
                <rect x="14" y="38" width="8" height="10" rx="2" fill="#fff" opacity="0.3"/>
                <circle cx="36" cy="43" r="3" fill="#fff"/>
                <circle cx="46" cy="43" r="3" fill="#4ade80"/>
                <line x1="24" y1="34" x2="20" y2="14" stroke="#F15A22" stroke-width="3" stroke-linecap="round"/>
                <line x1="40" y1="34" x2="44" y2="14" stroke="#F15A22" stroke-width="3" stroke-linecap="round"/>
                <path d="M12 22 Q32 8 52 22" stroke="#F15A22" stroke-width="2.5" stroke-linecap="round" fill="none" opacity="0.5"/>
                <path d="M18 28 Q32 18 46 28" stroke="#F15A22" stroke-width="2.5" stroke-linecap="round" fill="none" opacity="0.8"/>
              </svg>
              <div class="pulse-wave-ring wave-1"></div>
              <div class="pulse-wave-ring wave-2"></div>
            </div>
            <span class="badge text-bg-warning fw-bold fs-8">Router Principal (8m)</span>
          </div>

          <i class="fa-solid fa-bolt text-warning fs-4 animate-pulse"></i>

          <!-- Nodo MESH -->
          <div class="text-center">
            <div class="wifi-router-animated-box mx-auto mb-2 bg-success-subtle">
              <i class="fa-solid fa-cubes fs-2 text-success"></i>
              <div class="pulse-wave-ring wave-mesh"></div>
            </div>
            <span class="badge text-bg-success fw-bold fs-8">Nodo MESH Extensor</span>
          </div>
        </div>
        <small class="text-white-50 fs-8 d-block">Los Equipos MESH amplían la cobertura de 8m eliminando zonas muertas en habitaciones alejadas.</small>
      </div>
    </div>
  </div>
</div>

<!-- MÓDULO ILUSTRADO: SERVICIO DE TRASLADO EXTERNO CON MAPA ANIMADO -->
<div class="card card-vuela p-4 mb-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center gap-2">
      <i class="fa-solid fa-route fs-3 text-primary"></i>
      <h4 class="fw-bold mb-0">Servicio de Traslado Externo de Instalación</h4>
    </div>
    <span class="badge text-bg-primary fs-7 px-3 py-2 fw-bold"><i class="fa-solid fa-map-location-dot me-1"></i>Mudanzas y Reubicación</span>
  </div>

  <div class="row g-4 align-items-center">
    <div class="col-lg-6">
      <div class="p-3 bg-light border rounded-4">
        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-truck-ramp-box text-primary me-1"></i> Requisitos e Indicaciones del Traslado</h6>
        <ul class="small text-dark mb-3 ps-3">
          <li class="mb-1"><strong>Costo de Traslado:</strong> El servicio de mudanza externa tiene costo técnico por tendido de nueva fibra.</li>
          <li class="mb-1"><strong>Equipos en Custodia:</strong> El cliente debe trasladar sus equipos (ONU / Módem + Transformador) a su nuevo domicilio.</li>
          <li class="mb-1"><strong>Recomendación de Zona:</strong> Se aconseja que el nuevo domicilio esté dentro o cerca del área de cobertura de la zona actual.</li>
        </ul>
        <div class="alert alert-success border-0 small py-2 px-3 rounded-3 mb-0">
          <i class="fa-solid fa-shield-check text-success me-1"></i> Conserva tu antigüedad de contrato al solicitar el traslado de línea.
        </div>
      </div>
    </div>

    <!-- Mapa Animado de Traslado CSS -->
    <div class="col-lg-6">
      <div class="p-3 bg-white border rounded-4 text-center">
        <h6 class="fw-bold text-dark fs-8 mb-2"><i class="fa-solid fa-location-dot text-danger me-1"></i> Mapa Ejemplo de Traslado de Domicilio en Zona</h6>
        
        <div class="animated-map-container rounded-3 p-3 position-relative overflow-hidden" style="background:#e2e8f0; height:150px; border:2px dashed #94a3b8;">
          <div class="map-point point-origin position-absolute" style="top:30px; left:40px;">
            <i class="fa-solid fa-house-chimney text-danger fs-3"></i>
            <small class="d-block fw-bold fs-8 text-dark">Domicilio Anterior</small>
          </div>

          <div class="map-route-line position-absolute" style="top:45px; left:90px; right:90px; height:4px; background:linear-gradient(90deg, #ef4444, #3b82f6); border-radius:2px;">
            <i class="fa-solid fa-truck-moving text-primary position-absolute route-van-anim" style="top:-12px;"></i>
          </div>

          <div class="map-point point-destination position-absolute" style="top:30px; right:40px;">
            <i class="fa-solid fa-house-circle-check text-success fs-3"></i>
            <small class="d-block fw-bold fs-8 text-dark">Nuevo Domicilio</small>
          </div>
        </div>
        <small class="text-muted fs-8 d-block mt-2">Ejemplo ilustrativo: El cliente lleva sus equipos y Vuela conecta la fibra en la nueva dirección.</small>
      </div>
    </div>
  </div>
</div>

<!-- Plantillas de Respuesta Rápida (Protocolo CALMA) -->
<div class="card card-vuela p-4 mb-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center gap-2">
      <i class="fa-solid fa-comments fs-3" style="color:var(--vuela)"></i>
      <h4 class="fw-bold mb-0">Plantillas de Respuesta Rápida (Protocolo CALMA)</h4>
    </div>
    <span class="badge bg-light text-muted border px-3 py-2 rounded-pill small">Modelos de atención rápida</span>
  </div>
  <p class="text-muted small mb-3">Respuestas preconcebidas optimizadas para copiar y pegar directamente al chat con el usuario:</p>

  <div class="row g-3">
    <div class="col-md-4">
      <div class="border rounded-4 p-3 bg-light h-100 d-flex flex-column justify-content-between">
        <div>
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-house-laptop me-1 text-primary"></i> 1. Mudanza / Viaje</h6>
            <span class="badge text-bg-primary fs-8">Baja</span>
          </div>
          <textarea id="tpl1" class="form-control form-control-sm text-muted bg-white mb-3" rows="4" readonly style="resize:none; font-size:0.82rem;">Estimado cliente, para procesar la baja por mudanza o viaje, se requiere apersonarse a nuestras oficinas con C.I. del titular y los equipos en comodato (módem y fuente). ¡Quedamos a su servicio!</textarea>
        </div>
        <button class="btn btn-vuela btn-sm w-100 fw-bold py-2" onclick="copyTpl('tpl1', this)">
          <i class="fa-solid fa-copy me-1"></i>Copiar Mensaje
        </button>
      </div>
    </div>

    <div class="col-md-4">
      <div class="border rounded-4 p-3 bg-light h-100 d-flex flex-column justify-content-between">
        <div>
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-wrench me-1 text-danger"></i> 2. Derivación NOC</h6>
            <span class="badge text-bg-danger fs-8">Soporte</span>
          </div>
          <textarea id="tpl2" class="form-control form-control-sm text-muted bg-white mb-3" rows="4" readonly style="resize:none; font-size:0.82rem;">Estimado usuario, derivamos su caso al área técnica (NOC) para revisión prioritaria. Por favor mantenga el módem encendido durante la verificación remota. En breve le informaremos.</textarea>
        </div>
        <button class="btn btn-vuela btn-sm w-100 fw-bold py-2" onclick="copyTpl('tpl2', this)">
          <i class="fa-solid fa-copy me-1"></i>Copiar Mensaje
        </button>
      </div>
    </div>

    <div class="col-md-4">
      <div class="border rounded-4 p-3 bg-light h-100 d-flex flex-column justify-content-between">
        <div>
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-snowflake me-1 text-info"></i> 3. Bloqueo Temporal</h6>
            <span class="badge text-bg-info text-white fs-8">Alternativa</span>
          </div>
          <textarea id="tpl3" class="form-control form-control-sm text-muted bg-white mb-3" rows="4" readonly style="resize:none; font-size:0.82rem;">Estimado cliente, antes de cancelar su servicio y aplicar la multa por rescisión anticipada, le ofrecemos activar el Bloqueo Temporal sin costo de hasta 2 meses para conservar su contrato. ¿Desea aplicar este beneficio?</textarea>
        </div>
        <button class="btn btn-vuela btn-sm w-100 fw-bold py-2" onclick="copyTpl('tpl3', this)">
          <i class="fa-solid fa-copy me-1"></i>Copiar Mensaje
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  var targetContractMonths = 18;

  function calculateMonthDiff(d1, d2) {
    if (!d1 || !d2) return 0;
    var parts1 = d1.split('-');
    var parts2 = d2.split('-');
    var y1 = parseInt(parts1[0], 10);
    var m1 = parseInt(parts1[1], 10);
    var y2 = parseInt(parts2[0], 10);
    var m2 = parseInt(parts2[1], 10);
    var diff = (y2 - y1) * 12 + (m2 - m1);
    return Math.max(0, diff);
  }

  function areAllConditionsChecked() {
    var c1 = document.getElementById('chk1') ? document.getElementById('chk1').checked : false;
    var c2 = document.getElementById('chk2') ? document.getElementById('chk2').checked : false;
    var c3 = document.getElementById('chk3') ? document.getElementById('chk3').checked : false;
    var c4 = document.getElementById('chk4') ? document.getElementById('chk4').checked : false;
    var c5 = document.getElementById('chk5') ? document.getElementById('chk5').checked : false;
    return c1 && c2 && c4 && c5; // c3 is optional temporal block
  }

  function updateCalc(fromDates) {
    var c18 = document.getElementById('contract18');
    targetContractMonths = (c18 && c18.checked) ? 18 : 12;

    var lblMaxM = document.getElementById('lbl-contract-max-months');
    if (lblMaxM) lblMaxM.textContent = targetContractMonths + 'M';

    var chkPermLbl = document.getElementById('chk-permanencia-lbl');
    if (chkPermLbl) chkPermLbl.textContent = targetContractMonths + ' Meses';

    var slider = document.getElementById('calc-meses');
    if (slider) slider.max = targetContractMonths;

    var fInicio = document.getElementById('calc-fecha-inicio').value;
    var fFin = document.getElementById('calc-fecha-fin').value;
    
    var bInicio = document.getElementById('calc-bloqueo-inicio').value;
    var bFin = document.getElementById('calc-bloqueo-fin').value;

    var totalTranscurridos = calculateMonthDiff(fInicio, fFin);
    var bloqueoMeses = 0;
    
    if (bInicio && bFin) {
      bloqueoMeses = calculateMonthDiff(bInicio, bFin);
    }

    if (fromDates && slider) {
      var activosCalculados = Math.max(0, totalTranscurridos - bloqueoMeses);
      slider.value = Math.min(targetContractMonths, activosCalculados);
    }

    var mesesActivos = slider ? (parseInt(slider.value, 10) || 0) : 0;
    var restantes = Math.max(0, targetContractMonths - mesesActivos);
    var penalidad = restantes * 159;

    document.getElementById('lbl-bloqueo-meses-calc').textContent = bloqueoMeses + ' mes(es) de bloqueo';
    document.getElementById('calc-meses-val').textContent = mesesActivos + ' meses';
    document.getElementById('lbl-transcurridos-total').textContent = totalTranscurridos + ' meses';
    document.getElementById('lbl-bloqueo-total').textContent = bloqueoMeses + ' meses';
    document.getElementById('calc-restantes').textContent = restantes + ' meses';
    document.getElementById('calc-resultado').textContent = 'Bs. ' + penalidad.toLocaleString('es-BO', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    var evalBox = document.getElementById('evaluacion-baja-box');
    var evalTitulo = document.getElementById('evaluacion-baja-titulo');
    var evalDesc = document.getElementById('evaluacion-baja-desc');
    var allChecked = areAllConditionsChecked();

    if (evalBox && evalTitulo && evalDesc) {
      if (restantes <= 0) {
        if (allChecked) {
          evalBox.className = 'p-3 bg-success-subtle border border-success rounded-4 bounce-in';
          evalTitulo.innerHTML = '<i class="fa-solid fa-circle-check text-success me-1"></i> Rescisión Gratuita Procedente (0 Bs.)';
          evalDesc.innerHTML = 'El cliente cumplió los <b>' + targetContractMonths + ' meses</b> de permanencia obligatoria y se verificaron todos los requisitos del contrato (Equipos + C.I. + Saldo al Día).';
        } else {
          evalBox.className = 'p-3 bg-info-subtle border border-info rounded-4 bounce-in';
          evalTitulo.innerHTML = '<i class="fa-solid fa-circle-info text-info me-1"></i> Permanencia Cumplida (Faltan Requisitos)';
          evalDesc.innerHTML = 'Fidelidad de <b>' + targetContractMonths + 'M</b> cumplida. Falta marcar devolución de equipos o C.I. del titular para autorizar la baja.';
        }
      } else {
        evalBox.className = 'p-3 bg-warning-subtle border border-warning rounded-4 bounce-in';
        evalTitulo.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> Rescisión Anticipada Sujeta a Penalidad';
        evalDesc.innerHTML = 'Faltan <b>' + restantes + ' meses</b> para cumplir el contrato de ' + targetContractMonths + 'M. Multa a cobrar por rescisión anticipada: <strong class="text-danger">Bs. ' + penalidad.toFixed(2) + '</strong>.';
      }
    }
  }

  // Botón Marcar Todas las Opciones
  var btnMarcarTodos = document.getElementById('btn-marcar-todos');
  if (btnMarcarTodos) {
    btnMarcarTodos.addEventListener('click', function() {
      document.querySelectorAll('.chk-condicion').forEach(function(chk) {
        chk.checked = true;
      });
      updateCalc(false);
    });
  }

  document.querySelectorAll('.chk-condicion').forEach(function(chk) {
    chk.addEventListener('change', function() { updateCalc(false); });
  });

  document.querySelectorAll('input[name="contract_type"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
      var c18Label = document.querySelector('label[for="contract18"]');
      var c12Label = document.querySelector('label[for="contract12"]');
      if (document.getElementById('contract18').checked) {
        c18Label.className = 'btn btn-sm btn-vuela rounded-pill px-3 py-2 fw-bold';
        c12Label.className = 'btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 fw-bold';
      } else {
        c12Label.className = 'btn btn-sm btn-vuela rounded-pill px-3 py-2 fw-bold';
        c18Label.className = 'btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 fw-bold';
      }
      updateCalc(true);
    });
  });

  document.getElementById('calc-fecha-inicio').addEventListener('change', function() { updateCalc(true); });
  document.getElementById('calc-fecha-fin').addEventListener('change', function() { updateCalc(true); });
  document.getElementById('calc-bloqueo-inicio').addEventListener('change', function() { updateCalc(true); });
  document.getElementById('calc-bloqueo-fin').addEventListener('change', function() { updateCalc(true); });
  if (document.getElementById('calc-meses')) {
    document.getElementById('calc-meses').addEventListener('input', function() { updateCalc(false); });
  }
  
  var btnCalc = document.getElementById('btn-calcular-multa');
  if (btnCalc) {
    btnCalc.addEventListener('click', function() { updateCalc(true); });
  }

  updateCalc(true);

  function copyTpl(id, btn) {
    var txt = document.getElementById(id);
    txt.select();
    navigator.clipboard.writeText(txt.value).then(function() {
      var orig = btn.innerHTML;
      btn.innerHTML = '<i class="fa-solid fa-check me-1"></i>¡Copiado!';
      btn.classList.remove('btn-vuela');
      btn.classList.add('btn-success');
      setTimeout(function() {
        btn.innerHTML = orig;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-vuela');
      }, 1800);
    });
  }
</script>
