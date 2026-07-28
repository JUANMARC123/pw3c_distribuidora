let repartidoresTable, editingId = null;
let usuariosDisponibles = [], vehiculosList = [];
let firebaseUsuarios = {};
let firebaseAppInited = false;

const ESTADOS_REPARTIDOR = ['activo', 'inactivo', 'en_ruta'];
const CATEGORIAS_LICENCIA = ['A', 'B', 'C'];

function estaConectado(firebaseUser) {
    if (!firebaseUser?.ubicacion) return false;
    if (firebaseUser.ubicacion.latitud == null) return false;
    var horaStr = firebaseUser.ubicacion.hora;
    if (!horaStr) return true;
    var partes = horaStr.split(':');
    if (partes.length !== 3) return true;
    var ahora = new Date();
    var fechaUbicacion = new Date();
    fechaUbicacion.setHours(parseInt(partes[0]), parseInt(partes[1]), parseInt(partes[2]), 0);
    var diffMs = ahora - fechaUbicacion;
    if (diffMs < 0) diffMs += 24 * 60 * 60 * 1000;
    return Math.floor(diffMs / 60000) < 2;
}

const REPARTIDORES_FIREBASE_CONFIG = {
    apiKey: "AIzaSyDLpt_G8VOvmePuAYEQyffhU1Qb9braKSw",
    authDomain: "mapboxfirebase-a79b5.firebaseapp.com",
    databaseURL: "https://mapboxfirebase-a79b5-default-rtdb.firebaseio.com",
    projectId: "mapboxfirebase-a79b5",
    storageBucket: "mapboxfirebase-a79b5.firebasestorage.app",
    messagingSenderId: "133519102970",
    appId: "1:133519102970:web:1bf07af71eda04b772ca43",
    measurementId: "G-SMVVH5FL53"
};

document.addEventListener('DOMContentLoaded', async function () {
    if (!checkAuth()) return;
    if (!hasPermission('Repartidores', 'acceder')) { window.location.href = '../dashboard.html'; return; }
    await loadCatalogos();
    renderPage();
    initFirebaseRepartidores();
});

async function loadCatalogos() {
    try { const d = await apiFetch('/usuarios'); usuariosDisponibles = Array.isArray(d) ? d : (d.data || []); } catch (e) { usuariosDisponibles = []; }
    try { const d = await apiFetch('/vehiculos?per_page=-1'); vehiculosList = Array.isArray(d) ? d : (d.data || []); } catch (e) { vehiculosList = []; }
}

function initFirebaseRepartidores() {
    try {
        if (!firebase.apps.length) firebase.initializeApp(REPARTIDORES_FIREBASE_CONFIG);
        firebaseAppInited = true;
        var db = firebase.database();
        var usuariosRef = db.ref('usuarios');
        usuariosRef.on('value', function (snapshot) {
            var data = snapshot.val();
            firebaseUsuarios = data || {};
            actualizarSelectFirebase();
            refreshFirebaseColumn();
        });
    } catch (e) {
        console.warn('Firebase init warning:', e);
    }
}

function refreshFirebaseColumn() {
    if (!document.getElementById('main-table')) return;
    if (!repartidoresTable) {
        setTimeout(refreshFirebaseColumn, 200);
        return;
    }
    repartidoresTable.rows().invalidate().draw(false);
}

function actualizarSelectFirebase() {
    var sel = document.getElementById('f-firebase-uid');
    if (!sel) return;
    var actual = sel.value;
    sel.innerHTML = '<option value="">— No vincular —</option>';
    Object.keys(firebaseUsuarios).forEach(function (id) {
        var u = firebaseUsuarios[id];
        var nombre = (u.info && u.info.nombre) ? u.info.nombre : id;
        var estado = estaConectado(u) ? '🟢' : '🔴';
        var opt = document.createElement('option');
        opt.value = id;
        opt.textContent = estado + ' ' + nombre + ' (' + id.substring(0, 8) + '...)';
        sel.appendChild(opt);
    });
    if (actual) sel.value = actual;
}

function renderPage() {
    document.getElementById('page-content').innerHTML = `
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-truck"></i> Repartidores</h3>
                ${hasPermission('Repartidores', 'crear') ? '<button class="btn btn-primary" onclick="openCreateModal()"><i class="fas fa-plus"></i> Nuevo Repartidor</button>' : ''}
            </div>
            <div class="card-body">
                <div class="table-container"><table id="main-table" class="display" style="width:100%"><thead><tr>
                    <th>Nombre</th><th>CI</th><th>Licencia</th><th>Estado</th><th>Vehiculo</th><th>Firebase</th><th>Acciones</th>
                </tr></thead></table></div>
            </div>
        </div>
        <div id="main-modal" class="modal-overlay"><div class="modal">
            <div class="modal-header"><h3 id="modal-title">Nuevo Repartidor</h3><button class="modal-close" onclick="closeModal()">&times;</button></div>
            <div class="modal-body">
                <form id="main-form">
                    <div class="form-group"><label>Usuario *</label><select id="f-usuario" class="form-control" required><option value="">Seleccione un usuario...</option>${usuariosDisponibles.map(u => `<option value="${u.id_usuario}">${u.nombre || ''} ${u.apellido || ''} (${u.email})</option>`).join('')}</select></div>
                    <div class="form-group"><label>CI *</label><input type="text" id="f-ci" class="form-control" required></div>
                    <div class="form-row">
                        <div class="form-group"><label>Categoría Licencia</label><select id="f-licencia" class="form-control"><option value="">Seleccione...</option>${CATEGORIAS_LICENCIA.map(l => `<option value="${l}">${l}</option>`).join('')}</select></div>
                        <div class="form-group"><label>Estado</label><select id="f-estado" class="form-control">${ESTADOS_REPARTIDOR.map(e => `<option value="${e}">${e.charAt(0).toUpperCase() + e.slice(1)}</option>`).join('')}</select></div>
                    </div>
                    <div class="form-group"><label>Vincular usuario de Firebase</label><select id="f-firebase-uid" class="form-control"><option value="">— No vincular —</option></select></div>
                </form>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal()">Cancelar</button><button class="btn btn-primary" onclick="save()">Guardar</button></div>
        </div></div>
        <div id="estado-modal" class="modal-overlay"><div class="modal" style="max-width:400px;">
            <div class="modal-header"><h3>Cambiar Estado</h3><button class="modal-close" onclick="closeEstadoModal()">&times;</button></div>
            <div class="modal-body">
                <p class="text-muted mb-4">Seleccione el nuevo estado para el repartidor <strong id="estado-ref"></strong></p>
                <div class="form-group"><label>Nuevo Estado</label><select id="s-estado" class="form-control">${ESTADOS_REPARTIDOR.map(e => `<option value="${e}">${e.charAt(0).toUpperCase() + e.slice(1)}</option>`).join('')}</select></div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" onclick="closeEstadoModal()">Cancelar</button><button class="btn btn-primary" onclick="changeEstado()">Cambiar</button></div>
        </div></div>
        <div id="vehiculo-modal" class="modal-overlay"><div class="modal" style="max-width:640px;">
            <div class="modal-header"><h3>Asignar vehículo</h3><button class="modal-close" onclick="closeAsignarModal()">&times;</button></div>
            <div class="modal-body">
                <p class="text-muted mb-4">Repartidor: <strong id="asignar-rep-name"></strong></p>
                <div id="asignar-actual" class="mb-4" style="display:none;">
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:10px;">
                        <small class="text-muted">Vehículo actual</small>
                        <div style="font-weight:600;" id="asignar-vehiculo-actual"></div>
                    </div>
                </div>
                <div id="asignar-historial" class="mb-4" style="display:none;">
                    <h4 style="font-size:14px;margin:0 0 8px;">Historial de vehículos</h4>
                    <div id="asignar-historial-table"></div>
                </div>
                <div class="form-group">
                    <label>Seleccionar vehículo</label>
                    <select id="asignar-vehiculo-select" class="form-control">
                        <option value="">— Seleccione un vehículo —</option>
                        ${vehiculosList.filter(v => v.estado !== 'taller').map(v => `<option value="${v.id_vehiculo}">${v.placa} — ${v.marca} ${v.modelo} (${v.estado})</option>`).join('')}
                    </select>
                </div>
            </div>
            <div class="modal-footer" style="justify-content:space-between;">
                <div>
                    <button class="btn btn-danger" id="btn-liberar" onclick="liberarVehiculo()" style="display:none;"><i class="fas fa-times"></i> Liberar vehículo</button>
                </div>
                <div>
                    <button class="btn btn-secondary" onclick="closeAsignarModal()">Cancelar</button>
                    <button class="btn btn-primary" onclick="asignarVehiculo()"><i class="fas fa-check"></i> Asignar</button>
                </div>
            </div>
        </div></div>
    `;
    loadTable();
}

async function loadTable() {
    try {
        const d = await apiFetch('/repartidores');
        const data = Array.isArray(d) ? d : (d.data || []);
        if ($.fn.DataTable.isDataTable('#main-table')) repartidoresTable.destroy();
        repartidoresTable = $('#main-table').DataTable({
            data, pageLength: 10, order: [[0, 'asc']],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            columns: [
                { data: null, render: r => { const u = r.usuario; return u ? `${u.nombre || ''} ${u.apellido || ''}`.trim() : (r.nombre_usuario || '—'); } },
                { data: null, render: r => `${r.ci || '—'}` },
                { data: null, render: r => r.categoria_licencia || '—' },
                { data: null, render: r => getStatusBadge(r.estado || '—') },
                { data: null, render: r => {
                    const asignacion = r.asignacion_activa || null;
                    const v = asignacion?.vehiculo || null;
                    return v ? `${v.placa || ''} (${v.marca || ''} ${v.modelo || ''})`.trim() : '<span class="text-muted">Sin vehiculo</span>';
                } },
                { data: null, render: r => {
                    var fb = r.firebase_uid;
                    if (!fb) return '<span class="badge badge-secondary" style="background:#64748b">Sin vincular</span>';
                    var fbUser = firebaseUsuarios[fb];
                    if (estaConectado(fbUser)) return '<span class="badge badge-success" style="background:#10b981">🟢 Conectado</span>';
                    return '<span class="badge badge-warning" style="background:#f59e0b">🔴 Desconectado</span>';
                } },
                { data: null, render: r => `<div class="actions">
                    ${hasPermission('Repartidores', 'editar') ? `<button class="btn btn-sm btn-outline" onclick="openAsignarModal(${r.id_repartidor},'${escapeAttr(r.usuario?.nombre || r.nombre || '')} ${escapeAttr(r.usuario?.apellido || '')}')" title="Asignar vehículo"><i class="fas fa-truck"></i></button>` : ''}
                    ${hasPermission('Repartidores', 'cambiar-estado') ? `<button class="btn btn-sm btn-info" onclick="openEstadoModal(${r.id_repartidor},'${(r.usuario?.nombre || r.nombre || '').replace(/'/g, "\\'")}')"><i class="fas fa-exchange-alt"></i></button>` : ''}
                    ${hasPermission('Repartidores', 'editar') ? `<button class="btn btn-sm btn-warning" onclick="edit(${r.id_repartidor})"><i class="fas fa-edit"></i></button>` : ''}
                    ${hasPermission('Repartidores', 'eliminar') ? `<button class="btn btn-sm btn-danger" onclick="del(${r.id_repartidor})"><i class="fas fa-trash"></i></button>` : ''}
                </div>`, orderable: false }
            ]
        });
        if (Object.keys(firebaseUsuarios).length > 0) refreshFirebaseColumn();
    } catch (e) { Swal.fire('Error', 'Error al cargar repartidores: ' + e.message, 'error'); }
}

function openCreateModal() {
    editingId = null; document.getElementById('modal-title').textContent = 'Nuevo Repartidor';
    document.getElementById('main-form').reset(); document.getElementById('main-modal').classList.add('active');
}
function closeModal() { document.getElementById('main-modal').classList.remove('active'); editingId = null; }

async function edit(id) {
    try {
        const d = await apiFetch(`/repartidores/${id}`);
        const r = d.repartidor || d.data || d;
        editingId = id;
        document.getElementById('modal-title').textContent = 'Editar Repartidor';
        document.getElementById('f-usuario').value = r.id_usuario || '';
        document.getElementById('f-ci').value = r.ci || '';
        document.getElementById('f-licencia').value = r.categoria_licencia || '';
        document.getElementById('f-estado').value = r.estado || 'activo';
        document.getElementById('f-firebase-uid').value = r.firebase_uid || '';
        document.getElementById('main-modal').classList.add('active');
    } catch (e) { Swal.fire('Error', 'No se pudo cargar el repartidor', 'error'); }
}

async function save() {
    const body = {
        id_usuario: parseInt(document.getElementById('f-usuario').value),
        ci: document.getElementById('f-ci').value.trim(),
        categoria_licencia: document.getElementById('f-licencia').value || null,
        estado: document.getElementById('f-estado').value,
        firebase_uid: document.getElementById('f-firebase-uid').value || null,
    };
    if (!body.id_usuario || !body.ci) return Swal.fire('Validación', 'Usuario y CI son obligatorios', 'warning');
    try {
        if (editingId) { await apiFetch(`/repartidores/${editingId}`, { method: 'PUT', body }); Swal.fire('Actualizado', 'Repartidor actualizado', 'success'); }
        else { await apiFetch('/repartidores', { method: 'POST', body }); Swal.fire('Creado', 'Repartidor creado', 'success'); }
        closeModal(); if (repartidoresTable) await reloadDataTable(repartidoresTable, '/repartidores');
    } catch (e) { Swal.fire('Error', e.message || 'Error al guardar', 'error'); }
}

async function del(id) {
    const r = await Swal.fire({ title: '¿Eliminar repartidor?', text: 'Esta acción no se puede deshacer.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar', confirmButtonColor: '#ef4444' });
    if (!r.isConfirmed) return;
    try { await apiFetch(`/repartidores/${id}`, { method: 'DELETE' }); Swal.fire('Eliminado', 'Repartidor eliminado', 'success'); if (repartidoresTable) await reloadDataTable(repartidoresTable, '/repartidores'); }
    catch (e) { Swal.fire('Error', e.message || 'Error al eliminar', 'error'); }
}

let estadoRepId = null;
function openEstadoModal(id, name) { estadoRepId = id; document.getElementById('estado-ref').textContent = name; document.getElementById('s-estado').value = ''; document.getElementById('estado-modal').classList.add('active'); }
function closeEstadoModal() { document.getElementById('estado-modal').classList.remove('active'); estadoRepId = null; }
async function changeEstado() {
    const estado = document.getElementById('s-estado').value;
    if (!estado) return Swal.fire('Validación', 'Seleccione un estado', 'warning');
    try { await apiFetch(`/repartidores/${estadoRepId}/cambiar-estado`, { method: 'POST', body: { estado } }); Swal.fire('Actualizado', 'Estado cambiado', 'success'); closeEstadoModal(); if (repartidoresTable) await reloadDataTable(repartidoresTable, '/repartidores'); }
    catch (e) { Swal.fire('Error', e.message || 'Error al cambiar estado', 'error'); }
}

let asignarRepId = null;

function escapeAttr(value) {
    return String(value).replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

function formatFecha(fecha) {
    if (!fecha) return '—';
    const d = new Date(fecha);
    return d.toLocaleDateString('es-BO', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

async function openAsignarModal(id, nombre) {
    asignarRepId = id;
    document.getElementById('asignar-rep-name').textContent = nombre.trim() || 'Repartidor #' + id;
    document.getElementById('asignar-vehiculo-select').value = '';

    try {
        const d = await apiFetch(`/repartidores/${id}`);
        const rep = d.repartidor || d.data || d;
        const asignaciones = rep.asignaciones_vehiculo || [];

        const asignacion = rep.asignacion_activa || null;
        const vehiculoActual = asignacion?.vehiculo || null;

        const actualDiv = document.getElementById('asignar-actual');
        const actualInfo = document.getElementById('asignar-vehiculo-actual');
        const btnLiberar = document.getElementById('btn-liberar');

        if (vehiculoActual) {
            actualDiv.style.display = 'block';
            actualInfo.textContent = `${vehiculoActual.placa} — ${vehiculoActual.marca} ${vehiculoActual.modelo}`;
            btnLiberar.style.display = 'inline-flex';
        } else {
            actualDiv.style.display = 'none';
            actualInfo.textContent = '';
            btnLiberar.style.display = 'none';
        }

        const historialDiv = document.getElementById('asignar-historial');
        const historialTable = document.getElementById('asignar-historial-table');

        if (asignaciones.length) {
            historialDiv.style.display = 'block';
            historialTable.innerHTML = `
                <table class="table" style="width:100%;font-size:13px;">
                    <thead>
                        <tr>
                            <th>Vehículo</th>
                            <th>Asignación</th>
                            <th>Devolución</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${asignaciones.slice().reverse().map(a => {
                            const v = a.vehiculo;
                            const activa = !a.fecha_devolucion;
                            return `<tr>
                                <td><strong>${v ? `${v.placa} — ${v.marca} ${v.modelo}` : 'Vehículo #' + a.id_vehiculo}</strong></td>
                                <td>${formatFecha(a.fecha_asignacion)}</td>
                                <td>${activa ? '<span class="badge badge-success" style="background:#10b981">Activo</span>' : formatFecha(a.fecha_devolucion)}</td>
                            </tr>`;
                        }).join('')}
                    </tbody>
                </table>
            `;
        } else {
            historialDiv.style.display = 'none';
            historialTable.innerHTML = '';
        }
    } catch (e) {
        document.getElementById('asignar-actual').style.display = 'none';
        document.getElementById('btn-liberar').style.display = 'none';
        document.getElementById('asignar-historial').style.display = 'none';
    }

    document.getElementById('vehiculo-modal').classList.add('active');
}

function closeAsignarModal() {
    document.getElementById('vehiculo-modal').classList.remove('active');
    asignarRepId = null;
}

async function asignarVehiculo() {
    const idVehiculo = parseInt(document.getElementById('asignar-vehiculo-select').value);
    if (!idVehiculo) return Swal.fire('Validación', 'Seleccione un vehículo para asignar', 'warning');
    try {
        await apiFetch(`/repartidores/${asignarRepId}/asignacion-vehiculo`, {
            method: 'POST',
            body: { id_vehiculo: idVehiculo }
        });
        Swal.fire('Asignado', 'Vehículo asignado correctamente', 'success');
        closeAsignarModal();
        if (repartidoresTable) await reloadDataTable(repartidoresTable, '/repartidores');
    } catch (e) {
        Swal.fire('Error', e.message || 'Error al asignar vehículo', 'error');
    }
}

async function liberarVehiculo() {
    const confirm = await Swal.fire({
        title: '¿Liberar vehículo?',
        text: 'El vehículo será desasignado de este repartidor.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, liberar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444'
    });
    if (!confirm.isConfirmed) return;
    try {
        await apiFetch(`/repartidores/${asignarRepId}/asignacion-vehiculo`, { method: 'DELETE' });
        Swal.fire('Liberado', 'Vehículo liberado correctamente', 'success');
        closeAsignarModal();
        if (repartidoresTable) await reloadDataTable(repartidoresTable, '/repartidores');
    } catch (e) {
        Swal.fire('Error', e.message || 'Error al liberar vehículo', 'error');
    }
}
