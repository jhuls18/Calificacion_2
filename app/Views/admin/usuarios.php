<?php if (!empty($ok)): ?><div class="alert alert-success alert-dismissible fade show mb-4"><i class="fa-solid fa-circle-check me-2"></i><?= e($ok) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="alert alert-danger alert-dismissible fade show mb-4"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

<div class="row g-4">
  <!-- Formulario Crear Usuario -->
  <div class="col-lg-4">
    <div class="card card-vuela p-4">
      <div class="d-flex align-items-center gap-2 mb-3">
        <div class="kpi-icon-box bg-orange-subtle text-vuela p-2 rounded-3"><i class="fa-solid fa-user-plus fs-4"></i></div>
        <div>
          <h4 class="fw-bold mb-0">Crear usuario</h4>
          <small class="text-muted">Nuevo acceso al sistema</small>
        </div>
      </div>
      <form method="post" action="<?= e(url('/admin/usuarios')) ?>" data-once>
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label fw-bold">Nombre completo</label>
          <input class="form-control" name="nombre" placeholder="Ej. Juan Pérez" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">Nombre de usuario</label>
          <div class="input-group">
            <span class="input-group-text">@</span>
            <input class="form-control" name="usuario" placeholder="jperez" required>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">Contraseña</label>
          <input class="form-control" type="password" name="password" placeholder="••••••••" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">Rol en el sistema</label>
          <select class="form-select" name="rol" id="create_rol_select" onchange="toggleCustomRolInput('create_rol_select', 'create_custom_rol_wrap')">
            <?php foreach ($roles as $r): ?>
              <option value="<?= e($r) ?>"><?= e($r) ?></option>
            <?php endforeach; ?>
            <option value="OTRO">✨ Otro / Rol Personalizado...</option>
          </select>
        </div>
        <div class="mb-3 d-none" id="create_custom_rol_wrap">
          <label class="form-label fw-bold text-vuela">Especificar Nombre de Rol Personalizado</label>
          <input class="form-control border-vuela" name="custom_rol" placeholder="Ej. Supervisor de Atención, Cajero Principal, etc.">
        </div>
        <div class="mb-4">
          <label class="form-label fw-bold">URL de Foto de Perfil (Opcional)</label>
          <input class="form-control" name="foto" placeholder="https://... o ruta de foto">
          <small class="text-muted" style="font-size:0.75rem;">Ingresa el enlace de una foto o déjalo vacío para usar avatar dinámico.</small>
        </div>
        <button class="btn btn-vuela w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-2">
          <i class="fa-solid fa-plus"></i> Guardar nuevo usuario
        </button>
      </form>
    </div>
  </div>

  <!-- Tabla Lista de Usuarios -->
  <div class="col-lg-8">
    <div class="card card-vuela p-4">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
          <h4 class="fw-bold mb-0">Gestión de Usuarios y Accesos</h4>
          <small class="text-muted">Total: <?= count($users) ?> cuentas registradas</small>
        </div>
        <span class="badge text-bg-primary rounded-pill px-3 py-2"><i class="fa-solid fa-users me-1"></i>Usuarios</span>
      </div>

      <div class="table-responsive">
        <table class="table align-middle table-hover">
          <thead class="table-light">
            <tr>
              <th>Perfil</th>
              <th>Usuario</th>
              <th>Nombre</th>
              <th>Rol</th>
              <th>Estado</th>
              <th>Último login / IP</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($users as $u): ?>
            <?php $isProt = User::isProtected($u); ?>
            <tr>
              <td>
                <?= user_avatar($u, 40) ?>
              </td>
              <td>
                <span class="fw-bold text-dark">@<?= e($u['usuario']) ?></span>
                <?php if ($isProt): ?>
                  <span class="badge text-bg-warning ms-1 small"><i class="fa-solid fa-shield-halved"></i> Principal</span>
                <?php endif; ?>
              </td>
              <td><?= e($u['nombre']) ?></td>
              <td><span class="badge badge-rol"><?= e($u['rol']) ?></span></td>
              <td>
                <?php if (!empty($u['activo'])): ?>
                  <span class="badge text-bg-success"><i class="fa-solid fa-check me-1"></i>Activo</span>
                <?php else: ?>
                  <span class="badge text-bg-secondary"><i class="fa-solid fa-ban me-1"></i>Inactivo</span>
                <?php endif; ?>
              </td>
              <td>
                <small class="d-block text-dark fw-semibold"><?= e($u['last_login'] ?? '—') ?></small>
                <small class="text-muted"><?= e($u['last_ip'] ?? '') ?></small>
              </td>
              <td class="text-end">
                <div class="d-inline-flex gap-1">
                  <!-- Botón Ver Datos -->
                  <button type="button" class="btn btn-sm btn-outline-info" title="Ver datos del usuario"
                          onclick="abrirVerUsuario(<?= e(json_encode($u, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)) ?>)">
                    <i class="fa-solid fa-eye"></i>
                  </button>

                  <!-- Botón Editar Datos -->
                  <button type="button" class="btn btn-sm btn-outline-primary" title="Editar datos del usuario"
                          onclick="abrirEditarUsuario(<?= e(json_encode($u, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)) ?>)">
                    <i class="fa-solid fa-pen-to-square me-1"></i>Editar
                  </button>

                  <!-- Botón Eliminar (Si no es protegido) -->
                  <?php if (!$isProt): ?>
                    <form method="post" action="<?= e(url('/admin/usuarios/eliminar')) ?>" onsubmit="return confirm('¿Está seguro de eliminar al usuario @<?= e($u['usuario']) ?>?')" class="d-inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="id" value="<?= e($u['id']) ?>">
                      <button class="btn btn-sm btn-outline-danger" title="Eliminar usuario">
                        <i class="fa-solid fa-trash-can"></i>
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- MODAL EDITAR DATOS DE USUARIO -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius:20px; overflow:hidden;">
      <div class="modal-header bg-dark text-white p-3 px-4">
        <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-gear text-warning me-2"></i>Editar Datos del Usuario</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" action="<?= e(url('/admin/usuarios/editar')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="id" id="edit_id">

        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-bold">Nombre completo</label>
            <input class="form-control" name="nombre" id="edit_nombre" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Nombre de usuario</label>
            <div class="input-group">
              <span class="input-group-text">@</span>
              <input class="form-control" name="usuario" id="edit_usuario" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Rol en el sistema</label>
            <select class="form-select" name="rol" id="edit_rol" onchange="toggleCustomRolInput('edit_rol', 'edit_custom_rol_wrap')">
              <?php foreach ($roles as $r): ?>
                <option value="<?= e($r) ?>"><?= e($r) ?></option>
              <?php endforeach; ?>
              <option value="OTRO">✨ Otro / Rol Personalizado...</option>
            </select>
          </div>
          <div class="mb-3 d-none" id="edit_custom_rol_wrap">
            <label class="form-label fw-bold text-vuela">Especificar Rol Personalizado</label>
            <input class="form-control border-vuela" name="custom_rol" id="edit_custom_rol" placeholder="Ej. Supervisor de Atención, Cajero Principal, etc.">
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">URL Foto de Perfil</label>
            <input class="form-control" name="foto" id="edit_foto" placeholder="https://... o ruta de foto">
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Cambiar Contraseña</label>
            <input class="form-control" type="password" name="password" placeholder="Dejar en blanco si NO deseas cambiarla">
            <small class="text-muted">Solo escribe una nueva contraseña si deseas reemplazar la actual.</small>
          </div>

          <div class="form-check form-switch mt-3">
            <input class="form-check-input" type="checkbox" name="activo" value="1" id="edit_activo">
            <label class="form-check-label fw-bold" for="edit_activo">Usuario Activo (Permite iniciar sesión)</label>
          </div>
        </div>

        <div class="modal-footer bg-light p-3">
          <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-vuela px-4 fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i> Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL VER DATOS DEL USUARIO -->
<div class="modal fade" id="modalVerUsuario" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius:20px; overflow:hidden;">
      <div class="modal-header bg-vuela text-white p-3 px-4">
        <h5 class="modal-title fw-bold"><i class="fa-solid fa-address-card me-2"></i>Información del Usuario</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="text-center mb-4">
          <div class="mb-2" id="ver_avatar_box"></div>
          <h4 class="fw-bold mb-0" id="ver_nombre">—</h4>
          <span class="badge text-bg-dark rounded-pill fs-6 px-3 py-1 mt-1" id="ver_usuario">@—</span>
        </div>

        <div class="list-group list-group-flush rounded-3 border">
          <div class="list-group-item d-flex justify-content-between">
            <span class="text-muted">ID de Cuenta:</span>
            <code class="fw-bold" id="ver_id">—</code>
          </div>
          <div class="list-group-item d-flex justify-content-between">
            <span class="text-muted">Rol:</span>
            <span class="fw-bold text-dark" id="ver_rol">—</span>
          </div>
          <div class="list-group-item d-flex justify-content-between">
            <span class="text-muted">Estado:</span>
            <span id="ver_activo">—</span>
          </div>
          <div class="list-group-item d-flex justify-content-between">
            <span class="text-muted">Fecha de Creación:</span>
            <span class="fw-semibold text-dark" id="ver_creado">—</span>
          </div>
          <div class="list-group-item d-flex justify-content-between">
            <span class="text-muted">Último Inicio de Sesión:</span>
            <span class="fw-semibold text-dark" id="ver_last_login">—</span>
          </div>
          <div class="list-group-item d-flex justify-content-between">
            <span class="text-muted">Última Dirección IP:</span>
            <span class="fw-semibold text-dark" id="ver_last_ip">—</span>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light p-3">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
function toggleCustomRolInput(selectId, wrapId) {
  var sel = document.getElementById(selectId);
  var wrap = document.getElementById(wrapId);
  if (!sel || !wrap) return;
  if (sel.value === 'OTRO') {
    wrap.classList.remove('d-none');
    wrap.classList.add('bounce-in');
  } else {
    wrap.classList.add('d-none');
  }
}

function abrirEditarUsuario(u) {
  document.getElementById('edit_id').value = u.id || '';
  document.getElementById('edit_nombre').value = u.nombre || '';
  document.getElementById('edit_usuario').value = u.usuario || '';
  document.getElementById('edit_foto').value = u.foto || '';
  document.getElementById('edit_activo').checked = !!u.activo;
  
  var sel = document.getElementById('edit_rol');
  if (sel) {
    var exists = false;
    for (var i = 0; i < sel.options.length; i++) {
      if (sel.options[i].value === u.rol) {
        sel.selectedIndex = i;
        exists = true;
        break;
      }
    }
    if (!exists) {
      sel.value = 'OTRO';
      var customIn = document.getElementById('edit_custom_rol');
      if (customIn) customIn.value = u.rol || '';
    }
    toggleCustomRolInput('edit_rol', 'edit_custom_rol_wrap');
  }
  
  var modal = new bootstrap.Modal(document.getElementById('modalEditarUsuario'));
  modal.show();
}

function abrirVerUsuario(u) {
  document.getElementById('ver_nombre').textContent = u.nombre || '—';
  document.getElementById('ver_usuario').textContent = '@' + (u.usuario || '—');
  document.getElementById('ver_id').textContent = u.id || '—';
  document.getElementById('ver_rol').textContent = u.rol || '—';
  document.getElementById('ver_activo').innerHTML = u.activo 
    ? '<span class="badge text-bg-success">Activo</span>' 
    : '<span class="badge text-bg-secondary">Inactivo</span>';
  document.getElementById('ver_creado').textContent = u.creado || '—';
  document.getElementById('ver_last_login').textContent = u.last_login || 'No registrado aún';
  document.getElementById('ver_last_ip').textContent = u.last_ip || '—';

  var avatarBox = document.getElementById('ver_avatar_box');
  if (u.foto) {
    avatarBox.innerHTML = '<img src="' + escapeHtmlAttr(u.foto) + '" width="70" height="70" class="rounded-circle object-fit-cover shadow" alt="Foto">';
  } else {
    var initial = (u.nombre || u.usuario || 'U').charAt(0).toUpperCase();
    avatarBox.innerHTML = '<div class="rounded-circle bg-dark text-white d-inline-flex align-items-center justify-content-center fw-bold display-6 shadow" style="width:70px; height:70px;">' + initial + '</div>';
  }
  
  var modal = new bootstrap.Modal(document.getElementById('modalVerUsuario'));
  modal.show();
}

function escapeHtmlAttr(str) {
  if (!str) return '';
  return String(str).replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}
</script>

