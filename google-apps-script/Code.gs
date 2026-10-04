/**
 * Vuela Internet — puente Google Sheets (Sincronización Completa)
 * 1. Cree un Google Sheet con pestañas: Calificaciones, Usuarios, Clientes, Logins, Config
 * 2. Extensiones > Apps Script > pegue este archivo
 * 3. Implementar > Aplicación web
 *    - Ejecutar como: Yo
 *    - Quién tiene acceso: Cualquiera
 * 4. Copie la URL /exec en Configuración del sistema
 */

function doGet(e) {
  try {
    var action = (e.parameter && e.parameter.action) ? e.parameter.action : '';
    var ss = SpreadsheetApp.getActiveSpreadsheet();

    // Soportar GET con action y data para máxima compatibilidad con redirecciones 302
    if (action === 'getRatings') {
      return json_({ ok: true, ratings: readObjects_(ss, 'Calificaciones') });
    }
    if (action === 'getUsers') {
      return json_({ ok: true, users: readObjects_(ss, 'Usuarios') });
    }
    if (action === 'getConfig') {
      return json_({ ok: true, config: readConfig_(ss) });
    }
    if (action === 'getClients') {
      return json_({ ok: true, clients: readObjects_(ss, 'Clientes') });
    }
    // Acciones de escritura via GET (cuando POST falla por redirección 302)
    if (action && e.parameter && e.parameter.data) {
      var body = JSON.parse(e.parameter.data);
      body.action = action;
      return processAction_(ss, body);
    }

    return json_({ ok: true, app: 'vuela-sis-calif' });
  } catch (err) {
    return json_({ ok: false, error: String(err) });
  }
}

function doPost(e) {
  try {
    var body = {};
    if (e.postData && e.postData.contents) {
      body = JSON.parse(e.postData.contents);
    }
    var ss = SpreadsheetApp.getActiveSpreadsheet();
    return processAction_(ss, body);
  } catch (err) {
    return json_({ ok: false, error: String(err) });
  }
}

function processAction_(ss, body) {
  var action = body.action || 'saveRating';

  // ===== CALIFICACIONES =====
  if (action === 'saveRating') {
    append_(ss, 'Calificaciones', body.row || {});
    return json_({ ok: true });
  }
  if (action === 'getRatings') {
    return json_({ ok: true, ratings: readObjects_(ss, 'Calificaciones') });
  }
  if (action === 'deleteRating') {
    return json_({ ok: true, note: 'Eliminación local solamente.' });
  }

  // ===== USUARIOS =====
  if (action === 'saveUser') {
    upsert_(ss, 'Usuarios', body.row || {}, 'id');
    return json_({ ok: true });
  }
  if (action === 'saveAllUsers') {
    replaceAll_(ss, 'Usuarios', body.rows || []);
    return json_({ ok: true });
  }
  if (action === 'getUsers') {
    return json_({ ok: true, users: readObjects_(ss, 'Usuarios') });
  }
  if (action === 'deleteUser') {
    deleteRow_(ss, 'Usuarios', 'id', body.id || '');
    return json_({ ok: true });
  }

  // ===== CONFIGURACIÓN =====
  if (action === 'saveConfig') {
    var sh = sheet_(ss, 'Config');
    sh.clear();
    sh.appendRow(['clave', 'valor']);
    var cfg = body.config || {};
    Object.keys(cfg).forEach(function (k) {
      sh.appendRow([k, typeof cfg[k] === 'object' ? JSON.stringify(cfg[k]) : cfg[k]]);
    });
    return json_({ ok: true });
  }
  if (action === 'getConfig') {
    return json_({ ok: true, config: readConfig_(ss) });
  }

  // ===== CLIENTES =====
  if (action === 'saveClient') {
    upsert_(ss, 'Clientes', body.row || {}, 'codigo');
    return json_({ ok: true });
  }
  if (action === 'saveAllClients') {
    replaceAll_(ss, 'Clientes', body.rows || []);
    return json_({ ok: true });
  }
  if (action === 'getClients') {
    return json_({ ok: true, clients: readObjects_(ss, 'Clientes') });
  }

  // ===== LOGINS =====
  if (action === 'loginLog') {
    append_(ss, 'Logins', {
      fecha: body.fecha, usuario: body.usuario, nombre: body.nombre,
      rol: body.rol, ip: body.ip
    });
    return json_({ ok: true });
  }

  // ===== SYNC COMPLETO (Todo de golpe) =====
  if (action === 'fullSync') {
    var result = {};
    if (body.users) { replaceAll_(ss, 'Usuarios', body.users); result.users = true; }
    if (body.config) {
      var shCfg = sheet_(ss, 'Config');
      shCfg.clear();
      shCfg.appendRow(['clave', 'valor']);
      Object.keys(body.config).forEach(function (k) {
        shCfg.appendRow([k, typeof body.config[k] === 'object' ? JSON.stringify(body.config[k]) : body.config[k]]);
      });
      result.config = true;
    }
    if (body.clients) { replaceAll_(ss, 'Clientes', body.clients); result.clients = true; }
    return json_({ ok: true, synced: result });
  }

  // ===== RESTAURACIÓN COMPLETA =====
  if (action === 'fullRestore') {
    return json_({
      ok: true,
      users: readObjects_(ss, 'Usuarios'),
      config: readConfig_(ss),
      clients: readObjects_(ss, 'Clientes'),
      ratings: readObjects_(ss, 'Calificaciones')
    });
  }

  return json_({ ok: false, error: 'Acción desconocida: ' + action });
}

// ===== HELPERS =====

function sheet_(ss, name) {
  var sh = ss.getSheetByName(name);
  if (!sh) sh = ss.insertSheet(name);
  return sh;
}

function append_(ss, name, row) {
  var sh = sheet_(ss, name);
  var keys = Object.keys(row);
  if (sh.getLastRow() === 0) {
    sh.appendRow(keys);
  } else {
    var header = sh.getRange(1, 1, 1, sh.getLastColumn()).getValues()[0];
    keys.forEach(function (k) {
      if (header.indexOf(k) === -1) {
        sh.getRange(1, header.length + 1).setValue(k);
        header.push(k);
      }
    });
  }
  var header2 = sh.getRange(1, 1, 1, sh.getLastColumn()).getValues()[0];
  var line = header2.map(function (k) { return row[k] != null ? row[k] : ''; });
  sh.appendRow(line);
}

/** Insertar o actualizar fila por campo clave */
function upsert_(ss, name, row, keyField) {
  var sh = sheet_(ss, name);
  if (sh.getLastRow() === 0) {
    append_(ss, name, row);
    return;
  }
  var header = sh.getRange(1, 1, 1, sh.getLastColumn()).getValues()[0];
  var keyCol = header.indexOf(keyField);
  if (keyCol === -1) {
    append_(ss, name, row);
    return;
  }
  var data = sh.getDataRange().getValues();
  for (var i = 1; i < data.length; i++) {
    if (String(data[i][keyCol]) === String(row[keyField])) {
      // Actualizar fila existente
      var line = header.map(function (k) { return row[k] != null ? row[k] : data[i][header.indexOf(k)] || ''; });
      sh.getRange(i + 1, 1, 1, line.length).setValues([line]);
      return;
    }
  }
  append_(ss, name, row);
}

/** Reemplazar toda la hoja con nuevos datos */
function replaceAll_(ss, name, rows) {
  var sh = sheet_(ss, name);
  sh.clear();
  if (!rows || rows.length === 0) return;
  // Recopilar todas las claves
  var allKeys = {};
  rows.forEach(function (r) { Object.keys(r).forEach(function (k) { allKeys[k] = true; }); });
  var header = Object.keys(allKeys);
  sh.appendRow(header);
  rows.forEach(function (r) {
    sh.appendRow(header.map(function (k) { return r[k] != null ? (typeof r[k] === 'object' ? JSON.stringify(r[k]) : r[k]) : ''; }));
  });
}

/** Eliminar fila por campo clave */
function deleteRow_(ss, name, keyField, keyValue) {
  var sh = ss.getSheetByName(name);
  if (!sh || sh.getLastRow() < 2) return;
  var header = sh.getRange(1, 1, 1, sh.getLastColumn()).getValues()[0];
  var keyCol = header.indexOf(keyField);
  if (keyCol === -1) return;
  var data = sh.getDataRange().getValues();
  for (var i = data.length - 1; i >= 1; i--) {
    if (String(data[i][keyCol]) === String(keyValue)) {
      sh.deleteRow(i + 1);
    }
  }
}

function readObjects_(ss, name) {
  var sh = ss.getSheetByName(name);
  if (!sh) return [];
  var values = sh.getDataRange().getValues();
  if (values.length < 2) return [];
  var header = values[0];
  var out = [];
  for (var i = 1; i < values.length; i++) {
    var o = {};
    for (var c = 0; c < header.length; c++) o[header[c]] = values[i][c];
    out.push(o);
  }
  return out;
}

function readConfig_(ss) {
  var sh = ss.getSheetByName('Config');
  if (!sh) return {};
  var values = sh.getDataRange().getValues();
  if (values.length < 2) return {};
  var cfg = {};
  for (var i = 1; i < values.length; i++) {
    var key = values[i][0];
    var val = values[i][1];
    // Intentar parsear JSON para arrays/objetos
    if (typeof val === 'string' && (val.charAt(0) === '[' || val.charAt(0) === '{')) {
      try { val = JSON.parse(val); } catch (e) {}
    }
    cfg[key] = val;
  }
  return cfg;
}

function json_(obj) {
  return ContentService.createTextOutput(JSON.stringify(obj))
    .setMimeType(ContentService.MimeType.JSON);
}
