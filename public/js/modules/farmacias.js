let farmaciasData = [];
let pedidosData = [];
let editingFarmaciaId = null;
let editingContactoId = null;
let currentFarmaciaId = null;
let currentFarmaciaName = '';
let mapaModal = null;
let markerModal = null;
let mapaGlobal = null;
let marcadoresGlobales = [];

document.addEventListener('DOMContentLoaded', async function () {
    if (!checkAuth()) return;
    if (!hasPermission('Farmacias', 'acceder')) { window.location.href = '../dashboard.html'; return; }
    renderPage();
});

function esEmailValido(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email);
}

function validarEmailInput(input) {
    input.style.borderColor = input.value && !esEmailValido(input.value) ? '#ef4444' : '';
}

function renderPage() {
    document.getElementById('page-content').innerHTML = `
        <section class="ops-hero">
            <div class="ops-hero-main">
                <div class="ops-kicker">Operaciones comerciales</div>
                <h1 class="ops-title">Farmacias activas</h1>
                <p class="ops-subtitle">Busca una farmacia, revisa su actividad de pedidos y crea nuevos pedidos sin cambiar de módulo.</p>
                <div class="ops-actions">
                    ${hasPermission('Farmacias', 'crear') ? '<button class="btn btn-primary" onclick="openFarmaciaModal()"><i class="fas fa-plus"></i> Nueva farmacia</button>' : ''}
                    ${hasPermission('Pedidos', 'crear') ? '<a class="btn btn-success" href="/pages/logistica.html#pedidos"><i class="fas fa-box"></i> Nuevo pedido</a>' : ''}
                </div>
            </div>
        </section>

        <div class="ops-split">
            <div class="ops-split-left">
                <section class="ops-toolbar">
                    <div class="ops-search">
                        <i class="fas fa-search"></i>
                        <input class="form-control" id="farmacias-search" placeholder="Buscar farmacia o dirección" oninput="renderFarmaciaCards()">
                    </div>
                    <div class="flex gap-2">
                        <button class="btn btn-outline" onclick="reloadFarmaciasView()"><i class="fas fa-rotate"></i> Actualizar</button>
                    </div>
                </section>

                <section class="ops-table-container">
                    <table class="table" id="farmacias-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Dirección</th>
                                <th>Ubicación</th>
                                <th style="width:240px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="farmacias-cards">
                            <tr><td colspan="4"><div class="loading"><div class="spinner"></div>Cargando farmacias...</div></td></tr>
                        </tbody>
                    </table>
                </section>
            </div>
            <div class="ops-split-right">
                <div class="ops-panel" style="position:sticky;top:20px;">
                    <div class="card-header"><h3><i class="fas fa-map-marked-alt"></i> Mapa</h3><span id="mapa-contador" class="text-muted"></span></div>
                    <div class="card-body" style="padding:0;">
                        <div id="mapa-farmacias-global" style="height:850px;border-radius:0 0 var(--radius) var(--radius);"></div>
                    </div>
                </div>
            </div>
        </div>

        ${renderFarmaciaModal()}
        ${renderContactosModal()}
        ${renderContactoFormModal()}
    `;

    reloadFarmaciasView();
    setTimeout(initMapaGlobal, 250);
}

function renderFarmaciaModal() {
    return `
        <div id="modal-farmacia" class="modal-overlay">
            <div class="modal">
                <div class="modal-header">
                    <h3 id="farmacia-modal-title">Nueva farmacia</h3>
                    <button class="modal-close" onclick="closeFarmaciaModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="form-farmacia">
                        <div class="form-group">
                            <label>Nombre *</label>
                            <input type="text" id="f-nombre" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Dirección *</label>
                            <input type="text" id="f-direccion" class="form-control" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Latitud *</label>
                                <input type="number" step="any" id="f-latitud" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Longitud *</label>
                                <input type="number" step="any" id="f-longitud" class="form-control" required>
                            </div>
                        </div>
                        <div id="coords-display" class="text-muted text-center" style="font-size:12px;margin-bottom:8px;">Haz clic en el mapa para seleccionar ubicación</div>
                        <div id="mapa-modal" style="height:300px;border-radius:8px;overflow:hidden;border:1px solid var(--border);"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeFarmaciaModal()">Cancelar</button>
                    <button class="btn btn-primary" onclick="saveFarmacia()">Guardar</button>
                </div>
            </div>
        </div>
    `;
}

function renderContactosModal() {
    return `
        <div id="modal-contactos" class="modal-overlay">
            <div class="modal" style="max-width:760px;">
                <div class="modal-header">
                    <h3 id="contactos-modal-title">Contactos</h3>
                    <button class="modal-close" onclick="closeContactosModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <div style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;gap:12px;">
                        <span id="farmacia-name-ref" style="font-weight:600;"></span>
                        ${hasPermission('Farmacias', 'gestionar-contactos') ? '<button class="btn btn-sm btn-primary" onclick="openContactoModal()"><i class="fas fa-plus"></i> Agregar contacto</button>' : ''}
                    </div>
                    <div id="contactos-list"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeContactosModal()">Cerrar</button>
                </div>
            </div>
        </div>
    `;
}

function renderContactoFormModal() {
    return `
        <div id="modal-contacto" class="modal-overlay">
            <div class="modal" style="max-width:500px;">
                <div class="modal-header">
                    <h3 id="contacto-modal-title">Nuevo contacto</h3>
                    <button class="modal-close" onclick="closeContactoFormModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="form-contacto">
                        <div class="form-group"><label>Nombre del contacto *</label><input type="text" id="c-nombre" class="form-control" required></div>
                        <div class="form-group"><label>Cargo *</label><input type="text" id="c-cargo" class="form-control" required placeholder="Ej: Gerente, Encargado..."></div>
                        <div class="form-row">
                            <div class="form-group"><label>Teléfono</label><input type="text" id="c-telefono" class="form-control" placeholder="8 dígitos" maxlength="8" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,8)"></div>
                            <div class="form-group"><label>Email</label><input type="text" id="c-email" class="form-control" placeholder="ejemplo@correo.com" oninput="validarEmailInput(this)"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeContactoFormModal()">Cancelar</button>
                    <button class="btn btn-primary" onclick="saveContacto()">Guardar</button>
                </div>
            </div>
        </div>
    `;
}

async function reloadFarmaciasView() {
    try {
        const [farmaciasResp, pedidosResp] = await Promise.all([
            apiFetch('/farmacias?per_page=-1'),
            apiFetch('/pedidos?per_page=-1').catch(() => []),
        ]);
        farmaciasData = Array.isArray(farmaciasResp) ? farmaciasResp : (farmaciasResp.data || []);
        pedidosData = Array.isArray(pedidosResp) ? pedidosResp : (pedidosResp.data || []);
        renderFarmaciaCards();
        actualizarMarcadoresGlobales(farmaciasData);
    } catch (e) {
        Swal.fire('Error', 'No se pudieron cargar las farmacias: ' + (e.message || ''), 'error');
    }
}

function renderFarmaciaCards() {
    const term = (document.getElementById('farmacias-search')?.value || '').trim().toLowerCase();
    const filtered = farmaciasData.filter(f => {
        const text = `${f.nombre || ''} ${f.direccion || ''}`.toLowerCase();
        return !term || text.includes(term);
    });

    const container = document.getElementById('farmacias-cards');
    if (!filtered.length) {
        container.innerHTML = '<tr><td colspan="4"><div class="empty-state"><i class="fas fa-hospital"></i><h4>Sin farmacias</h4><p>No se encontraron farmacias con ese criterio.</p></div></td></tr>';
        return;
    }

    container.innerHTML = filtered.map(f => {
        const hasCoords = f.latitud && f.longitud;
        return `
            <tr data-farmacia-id="${f.id_farmacia}" style="cursor:pointer;">
                <td><strong>${f.nombre || 'Farmacia sin nombre'}</strong></td>
                <td>${f.direccion || 'Sin dirección'}</td>
                <td><span class="badge ${hasCoords ? 'badge-success' : 'badge-warning'}">${hasCoords ? 'Con mapa' : 'Sin mapa'}</span></td>
                <td>
                    <div class="actions">
                        ${hasPermission('Farmacias', 'gestionar-contactos') ? `<button class="btn btn-sm btn-info" onclick="openContactosModal(${f.id_farmacia}, '${escapeAttr(f.nombre || '')}')"><i class="fas fa-address-book"></i></button>` : ''}
                        ${hasPermission('Farmacias', 'editar') ? `<button class="btn btn-sm btn-warning" onclick="editFarmacia(${f.id_farmacia})"><i class="fas fa-edit"></i></button>` : ''}
                        ${hasPermission('Farmacias', 'eliminar') ? `<button class="btn btn-sm btn-danger" onclick="deleteFarmacia(${f.id_farmacia}, '${escapeAttr(f.nombre || '')}')"><i class="fas fa-trash"></i></button>` : ''}
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    Array.from(container.querySelectorAll('tr[data-farmacia-id]')).forEach(tr => {
        tr.addEventListener('mouseenter', function () {
            highlightMapMarker(this.dataset.farmaciaId);
        });
        tr.addEventListener('mouseleave', function () {
            unhighlightMapMarker(this.dataset.farmaciaId);
        });
    });
}

function escapeAttr(value) {
    return String(value).replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

function openFarmaciaModal() {
    editingFarmaciaId = null;
    document.getElementById('farmacia-modal-title').textContent = 'Nueva farmacia';
    document.getElementById('form-farmacia').reset();
    document.getElementById('modal-farmacia').classList.add('active');
    document.getElementById('coords-display').textContent = 'Haz clic en el mapa para seleccionar ubicación';
    setTimeout(initMapaModal, 200);
}

function closeFarmaciaModal() {
    document.getElementById('modal-farmacia').classList.remove('active');
    editingFarmaciaId = null;
    if (markerModal && mapaModal) {
        mapaModal.removeLayer(markerModal);
        markerModal = null;
    }
}

async function editFarmacia(id) {
    try {
        const data = await apiFetch(`/farmacias/${id}`);
        const farmacia = data.farmacia || data.data || data;
        editingFarmaciaId = id;
        document.getElementById('farmacia-modal-title').textContent = 'Editar farmacia';
        document.getElementById('f-nombre').value = farmacia.nombre || '';
        document.getElementById('f-direccion').value = farmacia.direccion || '';
        document.getElementById('f-latitud').value = farmacia.latitud || '';
        document.getElementById('f-longitud').value = farmacia.longitud || '';
        document.getElementById('modal-farmacia').classList.add('active');
        document.getElementById('coords-display').textContent = farmacia.latitud
            ? `Lat: ${parseFloat(farmacia.latitud).toFixed(6)} | Lng: ${parseFloat(farmacia.longitud).toFixed(6)}`
            : 'Haz clic en el mapa para seleccionar ubicación';
        setTimeout(function () {
            initMapaModal();
            if (farmacia.latitud && farmacia.longitud) {
                const lat = parseFloat(farmacia.latitud);
                const lng = parseFloat(farmacia.longitud);
                mapaModal.setView([lat, lng], 15);
                setMarker(lat, lng);
            }
        }, 200);
    } catch (e) {
        Swal.fire('Error', 'No se pudo cargar la farmacia', 'error');
    }
}

async function saveFarmacia() {
    const nombre = document.getElementById('f-nombre').value.trim();
    const direccion = document.getElementById('f-direccion').value.trim();
    const latitud = document.getElementById('f-latitud').value;
    const longitud = document.getElementById('f-longitud').value;

    if (!nombre || !direccion) return Swal.fire('Validación', 'Nombre y dirección son obligatorios', 'warning');

    const latNum = parseFloat(latitud);
    const lngNum = parseFloat(longitud);
    if (isNaN(latNum) || latNum < -90 || latNum > 90) return Swal.fire('Validación', 'La latitud es obligatoria y debe estar entre -90 y 90', 'warning');
    if (isNaN(lngNum) || lngNum < -180 || lngNum > 180) return Swal.fire('Validación', 'La longitud es obligatoria y debe estar entre -180 y 180', 'warning');

    const body = { nombre, direccion, latitud: latNum, longitud: lngNum };
    try {
        if (editingFarmaciaId) {
            await apiFetch(`/farmacias/${editingFarmaciaId}`, { method: 'PUT', body });
            Swal.fire('Actualizado', 'Farmacia actualizada correctamente', 'success');
        } else {
            await apiFetch('/farmacias', { method: 'POST', body });
            Swal.fire('Creado', 'Farmacia creada correctamente', 'success');
        }
        closeFarmaciaModal();
        await reloadFarmaciasView();
    } catch (e) {
        Swal.fire('Error', e.message || 'Error al guardar', 'error');
    }
}

async function deleteFarmacia(id, nombre) {
    const result = await Swal.fire({
        title: '¿Eliminar farmacia?',
        text: `Se eliminará "${nombre}" y todos sus datos asociados.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444',
    });
    if (!result.isConfirmed) return;
    try {
        await apiFetch(`/farmacias/${id}`, { method: 'DELETE' });
        Swal.fire('Eliminado', 'Farmacia eliminada correctamente', 'success');
        await reloadFarmaciasView();
    } catch (e) {
        Swal.fire('No se puede eliminar', 'Esta farmacia tiene pedidos u otros registros asociados.', 'warning');
    }
}

async function openContactosModal(farmaciaId, farmaciaName) {
    currentFarmaciaId = farmaciaId;
    currentFarmaciaName = farmaciaName;
    document.getElementById('farmacia-name-ref').textContent = `Contactos de: ${farmaciaName}`;
    document.getElementById('modal-contactos').classList.add('active');
    await loadContactos();
}

function closeContactosModal() {
    document.getElementById('modal-contactos').classList.remove('active');
    currentFarmaciaId = null;
}

async function loadContactos() {
    const container = document.getElementById('contactos-list');
    try {
        const data = await apiFetch(`/farmacias/${currentFarmaciaId}/contactos`);
        const contactos = Array.isArray(data) ? data : (data.data || []);
        if (!contactos.length) {
            container.innerHTML = '<div class="empty-state" style="padding:24px;"><i class="fas fa-address-book"></i><h4>Sin contactos</h4><p>Agrega el primer contacto de esta farmacia.</p></div>';
            return;
        }
        container.innerHTML = `
            <table class="table" style="width:100%;">
                <thead><tr><th>Nombre</th><th>Cargo</th><th>Teléfono</th><th>Email</th><th>Acciones</th></tr></thead>
                <tbody>
                    ${contactos.map(c => {
                        const cid = c.id_contacto;
                        return `<tr>
                            <td>${c.nombre_contacto || '—'}</td>
                            <td>${c.cargo || '—'}</td>
                            <td>${c.telefono || '—'}</td>
                            <td>${c.email || '—'}</td>
                            <td>
                                <div class="actions">
                                    ${hasPermission('Farmacias', 'gestionar-contactos') ? `<button class="btn btn-sm btn-warning" onclick="editContacto(${cid})"><i class="fas fa-edit"></i></button>` : ''}
                                    ${hasPermission('Farmacias', 'gestionar-contactos') ? `<button class="btn btn-sm btn-danger" onclick="deleteContacto(${cid}, '${escapeAttr(c.nombre_contacto || '')}')"><i class="fas fa-trash"></i></button>` : ''}
                                </div>
                            </td>
                        </tr>`;
                    }).join('')}
                </tbody>
            </table>
        `;
    } catch (e) {
        container.innerHTML = '<p class="text-muted">No se pudieron cargar los contactos.</p>';
    }
}

function openContactoModal() {
    editingContactoId = null;
    document.getElementById('contacto-modal-title').textContent = 'Nuevo contacto';
    document.getElementById('form-contacto').reset();
    document.getElementById('modal-contacto').classList.add('active');
}

function closeContactoFormModal() {
    document.getElementById('modal-contacto').classList.remove('active');
    editingContactoId = null;
}

async function editContacto(id) {
    try {
        const data = await apiFetch(`/farmacias/${currentFarmaciaId}/contactos/${id}`);
        const contacto = data.contacto || data.data || data;
        editingContactoId = id;
        document.getElementById('contacto-modal-title').textContent = 'Editar contacto';
        document.getElementById('c-nombre').value = contacto.nombre_contacto || '';
        document.getElementById('c-cargo').value = contacto.cargo || '';
        document.getElementById('c-telefono').value = contacto.telefono || '';
        document.getElementById('c-email').value = contacto.email || '';
        document.getElementById('modal-contacto').classList.add('active');
    } catch (e) {
        Swal.fire('Error', 'No se pudo cargar el contacto', 'error');
    }
}

async function saveContacto() {
    const nombre = document.getElementById('c-nombre').value.trim();
    const cargo = document.getElementById('c-cargo').value.trim();
    const telefono = document.getElementById('c-telefono').value.trim();
    const email = document.getElementById('c-email').value.trim();
    if (!nombre || !cargo) return Swal.fire('Validación', 'Nombre y cargo son obligatorios', 'warning');
    if (telefono && telefono.length !== 8) return Swal.fire('Validación', 'El teléfono debe tener exactamente 8 dígitos', 'warning');
    if (email && !esEmailValido(email)) return Swal.fire('Validación', 'El email ingresado no es válido', 'warning');
    const body = { nombre_contacto: nombre, cargo, telefono, email };
    try {
        if (editingContactoId) {
            await apiFetch(`/farmacias/${currentFarmaciaId}/contactos/${editingContactoId}`, { method: 'PUT', body });
            Swal.fire('Actualizado', 'Contacto actualizado correctamente', 'success');
        } else {
            await apiFetch(`/farmacias/${currentFarmaciaId}/contactos`, { method: 'POST', body });
            Swal.fire('Creado', 'Contacto creado correctamente', 'success');
        }
        closeContactoFormModal();
        await loadContactos();
        await reloadFarmaciasView();
    } catch (e) {
        Swal.fire('Error', e.message || 'Error al guardar el contacto', 'error');
    }
}

async function deleteContacto(id, nombre) {
    const result = await Swal.fire({
        title: '¿Eliminar contacto?',
        text: `Se eliminará "${nombre}".`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444',
    });
    if (!result.isConfirmed) return;
    try {
        await apiFetch(`/farmacias/${currentFarmaciaId}/contactos/${id}`, { method: 'DELETE' });
        Swal.fire('Eliminado', 'Contacto eliminado correctamente', 'success');
        await loadContactos();
        await reloadFarmaciasView();
    } catch (e) {
        Swal.fire('Error', e.message || 'Error al eliminar', 'error');
    }
}

function highlightMapMarker(id) {
    const entry = marcadoresGlobales.find(m => String(m.id) === String(id));
    if (!entry) return;
    const marker = entry.marker;
    marker.setStyle({
        radius: 12,
        fillColor: '#f59e0b',
        color: '#fff',
        weight: 3,
        fillOpacity: 1
    });
    if (marker._popup) marker.openPopup();
    marker.bringToFront();
}

function unhighlightMapMarker(id) {
    const entry = marcadoresGlobales.find(m => String(m.id) === String(id));
    if (!entry) return;
    const marker = entry.marker;
    marker.setStyle({
        radius: 7,
        fillColor: '#3b82f6',
        color: '#fff',
        weight: 2,
        fillOpacity: 0.85
    });
    marker.closePopup();
}

function initMapaModal() {
    const container = document.getElementById('mapa-modal');
    if (!container) return;
    if (mapaModal) {
        mapaModal.invalidateSize();
        return;
    }
    mapaModal = L.map('mapa-modal').setView([-16.4897, -67.8217], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(mapaModal);
    mapaModal.on('click', function (e) {
        setMarker(e.latlng.lat, e.latlng.lng);
        reverseGeocode(e.latlng.lat, e.latlng.lng);
    });
}

function setMarker(lat, lng) {
    if (markerModal) mapaModal.removeLayer(markerModal);
    markerModal = L.marker([lat, lng], { draggable: true }).addTo(mapaModal);
    markerModal.on('dragend', function (e) {
        const pos = e.target.getLatLng();
        document.getElementById('f-latitud').value = pos.lat.toFixed(8);
        document.getElementById('f-longitud').value = pos.lng.toFixed(8);
        document.getElementById('coords-display').textContent = `Lat: ${pos.lat.toFixed(6)} | Lng: ${pos.lng.toFixed(6)}`;
        reverseGeocode(pos.lat, pos.lng);
    });
    document.getElementById('f-latitud').value = lat.toFixed(8);
    document.getElementById('f-longitud').value = lng.toFixed(8);
    document.getElementById('coords-display').textContent = `Lat: ${lat.toFixed(6)} | Lng: ${lng.toFixed(6)}`;
}

function reverseGeocode(lat, lng) {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
        .then(r => r.json())
        .then(data => {
            if (data.display_name) document.getElementById('f-direccion').value = data.display_name;
        })
        .catch(() => {});
}

function initMapaGlobal() {
    const container = document.getElementById('mapa-farmacias-global');
    if (!container) return;
    if (mapaGlobal) {
        mapaGlobal.invalidateSize();
        return;
    }
    mapaGlobal = L.map('mapa-farmacias-global').setView([-16.4897, -67.8217], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(mapaGlobal);
    actualizarMarcadoresGlobales(farmaciasData);
}

function actualizarMarcadoresGlobales(farmacias) {
    if (!mapaGlobal) return;
    marcadoresGlobales.forEach(m => mapaGlobal.removeLayer(m.marker));
    marcadoresGlobales = [];
    let count = 0;
    farmacias.forEach(f => {
        const lat = parseFloat(f.latitud);
        const lng = parseFloat(f.longitud);
        if (isNaN(lat) || isNaN(lng)) return;
        count++;
        const marker = L.circleMarker([lat, lng], {
            radius: 7,
            fillColor: '#3b82f6',
            color: '#fff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.85
        }).addTo(mapaGlobal);
        marker.bindPopup(`<b>${f.nombre}</b><br>${f.direccion || 'Sin dirección'}`);
        marcadoresGlobales.push({ id: f.id_farmacia, marker, data: f });
    });
    const counter = document.getElementById('mapa-contador');
    if (counter) counter.textContent = `${count} en mapa`;
}
