<?php

namespace App\Http\Controllers\Despacho;

use App\Http\Controllers\ApiController;
use App\Models\Despacho;
use Illuminate\Http\Request;

class DespachoController extends ApiController
{
    public function index(Request $request)
    {
        $query = Despacho::with('pedido.farmacia', 'pedido.usuario', 'hojaRutaDetalle.hojaRuta');

        if ($request->filled('estado_despacho')) {
            $query->where('estado_despacho', $request->estado_despacho);
        }

        if ($request->filled('id_pedido')) {
            $query->where('id_pedido', $request->id_pedido);
        }

        return $this->safePaginate($query->orderBy('fecha_hora_despacho', 'desc'), $request);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_pedido' => 'required|integer|unique:despachos,id_pedido|exists:pedidos,id_pedido',
            'id_detalle_hoja_ruta' => 'required|integer|exists:hoja_ruta_detalles,id_detalle',
        ]);

        $validated['estado_despacho'] = 'pendiente';
        $validated['fecha_hora_despacho'] = now();

        $despacho = Despacho::create($validated);

        return $this->jsonResponse(
            $despacho->load('pedido.farmacia', 'hojaRutaDetalle.hojaRuta'),
            'Despacho creado exitosamente.',
            201
        );
    }

    public function show($id)
    {
        $despacho = Despacho::with([
            'pedido.farmacia',
            'pedido.usuario',
            'pedido.detalles.producto',
            'hojaRutaDetalle.hojaRuta.repartidor.usuario',
            'hojaRutaDetalle.hojaRuta.vehiculo',
            'evidencias',
            'incidencias',
        ])->findOrFail($id);

        return $this->jsonResponse($despacho);
    }

    public function update(Request $request, $id)
    {
        $despacho = Despacho::findOrFail($id);

        $validated = $request->validate([
            'estado_despacho' => 'sometimes|in:pendiente,entregado,incidencia',
            'monto_recibido' => 'nullable|numeric|min:0',
        ]);

        $despacho->update($validated);

        return $this->jsonResponse($despacho, 'Despacho actualizado exitosamente.');
    }

    public function destroy($id)
    {
        try {
            $despacho = Despacho::findOrFail($id);
            $despacho->delete();
            return $this->jsonResponse(null, 'Despacho eliminado exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse('No se puede eliminar el despacho.', 409);
        }
    }

    public function cambiarEstado(Request $request, $id)
    {
        $despacho = Despacho::findOrFail($id);

        $validated = $request->validate([
            'estado_despacho' => 'required|in:pendiente,entregado,incidencia',
        ]);

        // ─── REGLA BDD: docs/specs/despachos.md -> Escenario D1 ────────
        // Protegido por: tests/Feature/Despacho/ValidarEvidenciaEntregaTest.php
        if ($validated['estado_despacho'] === 'entregado' && $despacho->evidencias()->count() === 0) {
            return response()->json([
                'error' => 'Debe subir al menos una foto como evidencia antes de finalizar la entrega.'
            ], 422);
        }
        // ───────────────────────────────────────────────────────────────

        $despacho->update(['estado_despacho' => $validated['estado_despacho']]);

        return $this->jsonResponse($despacho, 'Estado del despacho actualizado.');
    }
}
