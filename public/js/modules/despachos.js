let despachosTable, currentDespachoId = null, evidenciasDT = null;

document.addEventListener('DOMContentLoaded', async function () {
    if (!checkAuth()) return;
    renderPage();
});

function renderPage() {
    document.getElementById('page-content').innerHTML = `
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-clipboard-check"></i> Despachos</h3>
            </div>
            <div class="card-body">
                <div class="table-container"><table id="main-table" class="display" style="width:100%"><thead><tr>
                    <th>ID</th><th>Pedido</th><th>Farmacia</th><th>Repartidor</th><th>Estado</th><th>Fecha</th><th>Acciones</th>
                </tr></thead></table></div>
            </div>
        </div>
        <div id="estado-modal" class="modal-overlay"><div class="modal" style="max-width:400px;">
            <div class="modal-header"><h3>Cambiar Estado</h3><button class="modal-close" onclick="closeEstadoModal()">&times;</button></div>
            <div class="modal-body">
                <p class="text-muted mb-4">Nuevo estado para despacho <strong id="estado-ref"></strong></p>
                <div class="form-group"><label>Estado</label><select id="s-estado" class="form-control">${ESTADOS_DESPACHO.map(e => `<option value="${e.clave}">${e.nombre_estado}</option>`).join('')}</select></div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" onclick="closeEstadoModal()">Cancelar</button><button class="btn btn-primary" onclick="changeEstado()">Cambiar</button></div>
        </div></div>

        <div id="evidencias-modal" class="modal-overlay"><div class="modal" style="max-width:700px;">
            <div class="modal-header"><h3>Evidencias - Despacho #<span id="ev-despacho-ref"></span></h3><button class="modal-close" onclick="closeEvidenciasModal()">&times;</button></div>
            <div class="modal-body">
                <div style="margin-bottom:16px;display:flex;justify-content:flex-end;">${hasPermission('Despachos', 'gestionar-evidencias') ? '<button class="btn btn-sm btn-primary" onclick="openEvidenciaForm()"><i class="fas fa-plus"></i> Subir Evidencia</button>' : ''}</div>
                <div class="table-container"><table id="evidencias-table" class="display" style="width:100%"><thead><tr><th>Tipo</th><th>Archivo</th><th>Fecha</th><th>Acciones</th></tr></thead></table></div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" onclick="closeEvidenciasModal()">Cerrar</button></div>
        </div></div>
        <div id="evidencia-form-modal" class="modal-overlay"><div class="modal" style="max-width:500px;">
            <div class="modal-header"><h3>Subir Evidencia</h3><button class="modal-close" onclick="closeEvidenciaForm()">&times;</button></div>
            <div class="modal-body">
                <form id="evidencia-form">
                    <div class="form-group"><label>Tipo de Evidencia *</label><select id="e-tipo" class="form-control" required>${TIPOS_EVIDENCIA.map(t => `<option value="${t.clave}">${t.nombre_tipo}</option>`).join('')}</select></div>
                    <div class="form-group"><label>Archivo *</label><input type="file" id="e-archivo" class="form-control" required style="padding:8px;"></div>
                </form>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" onclick="closeEvidenciaForm()">Cancelar</button><button class="btn btn-primary" onclick="saveEvidencia()">Subir</button></div>
        </div></div>
    `;
    loadTable();
}

async function loadTable() {
    try {
        const d = await apiFetch('/despachos');
        const data = Array.isArray(d) ? d : (d.data || []);
        if ($.fn.DataTable.isDataTable('#main-table')) despachosTable.destroy();
        despachosTable = $('#main-table').DataTable({
            data, pageLength: 10, order: [[0, 'desc']],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            columns: [
                { data: 'id_despacho' },
                { data: null, render: r => `#${r.pedido?.id_pedido || r.id_pedido || '?'}` },
                { data: null, render: r => r.pedido?.farmacia?.nombre || '—' },
                { data: null, render: r => {
                    const u = r.hojaRutaDetalle?.hojaRuta?.repartidor?.usuario;
                    return u ? `${u.nombre || ''} ${u.apellido || ''}`.trim() : '—';
                }},
                { data: null, render: r => getStatusBadge(estadoDespachoLabel(r.estado_despacho)) },
                { data: null, render: r => formatDate(r.fecha_hora_despacho) },
                { data: null, render: r => `<div class="actions">
                    ${hasPermission('Despachos', 'gestionar-evidencias') ? `<button class="btn btn-sm btn-info" onclick="openEvidenciasModal(${r.id_despacho})" title="Evidencias"><i class="fas fa-camera"></i></button>` : ''}
                    ${hasPermission('Despachos', 'cambiar-estado') ? `<button class="btn btn-sm btn-info" onclick="openEstadoModal(${r.id_despacho},'#${r.id_despacho}')" title="Cambiar estado"><i class="fas fa-exchange-alt"></i></button>` : ''}
                    ${hasPermission('Despachos', 'eliminar') ? `<button class="btn btn-sm btn-danger" onclick="del(${r.id_despacho})"><i class="fas fa-trash"></i></button>` : ''}
                </div>`, orderable: false }
            ]
        });
    } catch (e) { Swal.fire('Error', 'Error al cargar despachos: ' + e.message, 'error'); }
}

async function del(id) {
    const r = await Swal.fire({ title: '¿Eliminar despacho?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar', confirmButtonColor: '#ef4444' });
    if (!r.isConfirmed) return;
    try { await apiFetch(`/despachos/${id}`, { method: 'DELETE' }); Swal.fire('Eliminado', 'Despacho eliminado', 'success'); if (despachosTable) await reloadDataTable(despachosTable, '/despachos'); }
    catch (e) { Swal.fire('Error', e.message || 'Error al eliminar', 'error'); }
}

// Cambiar estado (el backend solo acepta el ENUM texto: pendiente|entregado|incidencia)
let estadoDespId = null;
function openEstadoModal(id, ref) { estadoDespId = id; document.getElementById('estado-ref').textContent = ref; document.getElementById('s-estado').value = 'pendiente'; document.getElementById('estado-modal').classList.add('active'); }
function closeEstadoModal() { document.getElementById('estado-modal').classList.remove('active'); estadoDespId = null; }
async function changeEstado() {
    const estado_despacho = document.getElementById('s-estado').value;
    if (!estado_despacho) return Swal.fire('Validación', 'Seleccione un estado', 'warning');
    try {
        await apiFetch(`/despachos/${estadoDespId}/cambiar-estado`, { method: 'POST', body: { estado_despacho } });
        Swal.fire('Actualizado', 'Estado cambiado', 'success');
        closeEstadoModal();
        if (despachosTable) await reloadDataTable(despachosTable, '/despachos');
    } catch (e) { Swal.fire('Error', e.message || 'Error al cambiar estado', 'error'); }
}

// Evidencias nested (el backend espera `tipo_evidencia`: firma|foto|sello)
async function openEvidenciasModal(id) {
    currentDespachoId = id; document.getElementById('ev-despacho-ref').textContent = id;
    document.getElementById('evidencias-modal').classList.add('active');
    await loadEvidencias();
}
function closeEvidenciasModal() { document.getElementById('evidencias-modal').classList.remove('active'); currentDespachoId = null; }

async function loadEvidencias() {
    try {
        const d = await apiFetch(`/despachos/${currentDespachoId}/evidencias`);
        const data = Array.isArray(d) ? d : (d.data || []);
        if ($.fn.DataTable.isDataTable('#evidencias-table')) evidenciasDT.destroy();
        evidenciasDT = $('#evidencias-table').DataTable({
            data, pageLength: 5, pagingType: 'simple', order: [[2, 'desc']], searching: false,
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            columns: [
                { data: null, render: r => tipoEvidenciaLabel(r.tipo_evidencia) },
                { data: null, render: r => {
                    if (!r.archivo) return '—';
                    const url = r.archivo.startsWith('http') ? r.archivo : `${STORAGE_URL}${r.archivo}`;
                    return `<a href="${url}" target="_blank" class="btn btn-sm btn-outline"><i class="fas fa-eye"></i> Ver</a>`;
                }},
                { data: null, render: r => formatDate(r.created_at) },
                { data: null, render: r => `<div class="actions">
                    ${hasPermission('Despachos', 'gestionar-evidencias') ? `<button class="btn btn-sm btn-danger" onclick="delEvidencia(${r.id_evidencia})"><i class="fas fa-trash"></i></button>` : ''}
                </div>`, orderable: false }
            ]
        });
    } catch (e) { console.error('Error loading evidencias:', e); }
}

function openEvidenciaForm() {
    document.getElementById('evidencia-form').reset();
    document.getElementById('evidencia-form-modal').classList.add('active');
}
function closeEvidenciaForm() { document.getElementById('evidencia-form-modal').classList.remove('active'); }

async function saveEvidencia() {
    const tipo_evidencia = document.getElementById('e-tipo').value;
    const archivo = document.getElementById('e-archivo').files[0];
    if (!tipo_evidencia || !archivo) return Swal.fire('Validación', 'Tipo y archivo son obligatorios', 'warning');

    const formData = new FormData();
    formData.append('tipo_evidencia', tipo_evidencia);
    formData.append('archivo', archivo);

    try {
        await apiFetch(`/despachos/${currentDespachoId}/evidencias`, {
            method: 'POST',
            body: formData,
        });
        Swal.fire('Subida', 'Evidencia subida correctamente', 'success');
        closeEvidenciaForm(); await loadEvidencias();
    } catch (e) { Swal.fire('Error', e.message || 'Error al subir evidencia', 'error'); }
}

async function delEvidencia(id) {
    const r = await Swal.fire({ title: '¿Eliminar evidencia?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar', confirmButtonColor: '#ef4444' });
    if (!r.isConfirmed) return;
    try { await apiFetch(`/despachos/${currentDespachoId}/evidencias/${id}`, { method: 'DELETE' }); Swal.fire('Eliminada', 'Evidencia eliminada', 'success'); await loadEvidencias(); }
    catch (e) { Swal.fire('Error', e.message || 'Error al eliminar', 'error'); }
}
