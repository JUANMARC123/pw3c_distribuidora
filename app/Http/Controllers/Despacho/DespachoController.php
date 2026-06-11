<?php

namespace App\Http\Controllers\Despacho;

use App\Http\Controllers\ApiController;
use App\Models\Despacho\Despacho;
use App\Models\Despacho\HistorialEstadoDespacho;
use Illuminate\Http\Request;

class DespachoController extends ApiController
{
    public function index(Request $request)
    {
        $query = Despacho::with('pedido.farmacia', 'estado', 'controlRuta.ruta');

        if ($request->filled('id_estado_despacho')) {
            $query->where('id_estado_despacho', $request->id_estado_despacho);
        }

        if ($request->filled('id_pedido')) {
            $query->where('id_pedido', $request->id_pedido);
        }

        if ($request->filled('id_control_ruta')) {
            $query->where('id_control_ruta', $request->id_control_ruta);
        }

        if ($request->filled('fecha_desde')) {
            $query->where('fecha_hora_despacho', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_hora_despacho', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        return $this->paginatedResponse(
            $query->orderBy('fecha_hora_despacho', 'desc')->paginate($request->per_page ?? 15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_pedido' => 'required|integer|exists:pedidos,id_pedido|unique:despachos,id_pedido',
            'id_parada' => 'required|integer|exists:ruta_paradas,id_parada',
            'id_control_ruta' => 'required|integer|exists:control_rutas,id_control_ruta',
            'id_estado_despacho' => 'required|integer|exists:estados_despacho,id_estado_despacho',
        ]);

        $data['fecha_hora_despacho'] = now();

        $despacho = Despacho::create($data);

        HistorialEstadoDespacho::create([
            'id_despacho' => $despacho->id_despacho,
            'id_estado_despacho' => $data['id_estado_despacho'],
            'fecha_inicio' => now(),
        ]);

        return $this->jsonResponse(
            $despacho->load('pedido.farmacia', 'estado', 'controlRuta.ruta', 'parada.farmacia'),
            'Despacho creado exitosamente.',
            201
        );
    }

    public function show($id)
    {
        $despacho = Despacho::with([
            'pedido.farmacia',
            'pedido.usuario',
            'parada.farmacia',
            'controlRuta.ruta',
            'controlRuta.repartidor.usuario',
            'controlRuta.vehiculo',
            'estado',
            'historiales.estado',
            'incidencias.tipoIncidencia',
            'evidencias.tipoEvidencia',
        ])->findOrFail($id);

        return $this->jsonResponse($despacho);
    }

    public function update(Request $request, $id)
    {
        $despacho = Despacho::findOrFail($id);

        $data = $request->validate([
            'id_parada' => 'sometimes|integer|exists:ruta_paradas,id_parada',
            'id_control_ruta' => 'sometimes|integer|exists:control_rutas,id_control_ruta',
        ]);

        $despacho->update($data);

        return $this->jsonResponse(
            $despacho->load('pedido.farmacia', 'estado'),
            'Despacho actualizado exitosamente.'
        );
    }

    public function destroy($id)
    {
        $despacho = Despacho::findOrFail($id);
        $despacho->delete();

        return $this->jsonResponse(null, 'Despacho eliminado exitosamente.');
    }

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'id_estado_despacho' => 'required|integer|exists:estados_despacho,id_estado_despacho',
        ]);

        $despacho = Despacho::findOrFail($id);

        $historialActual = HistorialEstadoDespacho::where('id_despacho', $id)
            ->whereNull('fecha_fin')
            ->latest('fecha_inicio')
            ->first();

        if ($historialActual) {
            $historialActual->update(['fecha_fin' => now()]);
        }

        HistorialEstadoDespacho::create([
            'id_despacho' => $id,
            'id_estado_despacho' => $request->id_estado_despacho,
            'fecha_inicio' => now(),
        ]);

        $despacho->update(['id_estado_despacho' => $request->id_estado_despacho]);

        return $this->jsonResponse(
            $despacho->load('estado', 'historiales.estado'),
            'Estado del despacho actualizado exitosamente.'
        );
    }
}
