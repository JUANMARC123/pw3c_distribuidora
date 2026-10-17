let controlesTable;

document.addEventListener('DOMContentLoaded', async function () {
    if (!checkAuth()) return;
    // GET /hojas-ruta exige Rutas-listar en el backend, así que la página se
    // gatea con ese mismo permiso (Repartidor solo tiene Control Rutas-*, no Rutas-*).
    if (!hasPermission('Rutas', 'listar') && !hasPermission('Rutas', 'acceder')) { window.location.href = '../dashboard.html'; return; }
    renderPage();
});

function renderPage() {
    document.getElementById('page-content').innerHTML = `
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-map-marked-alt"></i> Control de Rutas</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">Las hojas de ruta se planifican desde el módulo de Logística.</p>
                <div class="table-container"><table id="main-table" class="display" style="width:100%"><thead><tr>
                    <th>Ruta</th><th>Fecha</th><th>Repartidor</th><th>Vehículo</th><th>Salida</th><th>Llegada</th><th>Estado</th>
                </tr></thead></table></div>
            </div>
        </div>
    `;
    loadTable();
}

async function loadTable() {
    try {
        const d = await apiFetch('/hojas-ruta');
        const data = Array.isArray(d) ? d : (d.data || []);
        if ($.fn.DataTable.isDataTable('#main-table')) controlesTable.destroy();
        controlesTable = $('#main-table').DataTable({
            data, pageLength: 10, order: [[1, 'desc']],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            columns: [
                { data: null, render: r => `#${r.id_hoja_ruta || r.id || '—'}` },
                { data: null, render: r => formatDateShort(r.fecha) },
                { data: null, render: r => { const u = r.repartidor?.usuario; return u ? `${u.nombre || ''} ${u.apellido || ''}`.trim() : (r.repartidor?.nombre || '—'); } },
                { data: null, render: r => r.vehiculo?.placa || '—' },
                { data: null, render: r => r.hora_salida ? String(r.hora_salida).substring(0, 5) : '—' },
                { data: null, render: r => r.hora_llegada ? String(r.hora_llegada).substring(0, 5) : '<span class="text-muted">Pendiente</span>' },
                { data: null, render: r => getStatusBadge(estadoRutaLabel(r.estado_ruta)) }
            ]
        });
    } catch (e) { Swal.fire('Error', 'Error al cargar hojas de ruta: ' + e.message, 'error'); }
}
