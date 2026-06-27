<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\ApiController;
use App\Models\Pedido\Pedido;
use App\Models\Pedido\HistorialEstadoPedido;
use App\Services\Pedido\PedidoPdfExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;

class PedidoController extends ApiController
{
    public function index(Request $request)
    {
        $query = Pedido::with('farmacia', 'usuario', 'estado')
            ->withCount('detalles');

        if ($request->filled('id_estado_pedido')) {
            $query->where('id_estado_pedido', $request->id_estado_pedido);
        }

        if ($request->filled('id_farmacia')) {
            $query->where('id_farmacia', $request->id_farmacia);
        }

        if ($request->filled('fecha_desde')) {
            $query->where('fecha_pedido', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_pedido', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        return $this->safePaginate(
            $query->orderBy('fecha_pedido', 'desc'), $request
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_farmacia' => 'required|integer|exists:farmacias,id_farmacia',
            'id_usuario' => 'required|integer|exists:usuarios,id_usuario',
            'id_estado_pedido' => 'required|integer|exists:estados_pedido,id_estado_pedido',
            'observaciones' => 'nullable|string',
            'subtotal' => 'sometimes|numeric|min:0',
            'descuento' => 'sometimes|numeric|min:0',
            'total' => 'sometimes|numeric|min:0',
        ]);

        $data['fecha_pedido'] = now();

        $pedido = Pedido::create($data);

        HistorialEstadoPedido::create([
            'id_pedido' => $pedido->id_pedido,
            'id_estado_pedido' => $data['id_estado_pedido'],
            'fecha_inicio' => now(),
        ]);

        return $this->jsonResponse(
            $pedido->load('farmacia', 'usuario', 'estado'),
            'Pedido creado exitosamente.',
            201
        );
    }

    public function show($id)
    {
        $pedido = Pedido::with([
            'farmacia',
            'usuario',
            'estado',
            'historiales.estado',
            'despacho',
            'detalles.producto',
            'detalles.estado',
        ])->findOrFail($id);

        return $this->jsonResponse($pedido);
    }

    public function update(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        $data = $request->validate([
            'id_farmacia' => 'sometimes|integer|exists:farmacias,id_farmacia',
            'id_estado_pedido' => 'sometimes|integer|exists:estados_pedido,id_estado_pedido',
            'observaciones' => 'nullable|string',
            'subtotal' => 'sometimes|numeric|min:0',
            'descuento' => 'sometimes|numeric|min:0',
            'total' => 'sometimes|numeric|min:0',
        ]);

        if (isset($data['id_estado_pedido']) && (int) $data['id_estado_pedido'] !== (int) $pedido->id_estado_pedido) {
            $historialActual = HistorialEstadoPedido::where('id_pedido', $id)
                ->whereNull('fecha_fin')
                ->latest('fecha_inicio')
                ->first();

            if ($historialActual) {
                $historialActual->update(['fecha_fin' => now()]);
            }

            HistorialEstadoPedido::create([
                'id_pedido' => $id,
                'id_estado_pedido' => $data['id_estado_pedido'],
                'fecha_inicio' => now(),
            ]);
        }

        $pedido->update($data);

        return $this->jsonResponse(
            $pedido->load('farmacia', 'usuario', 'estado'),
            'Pedido actualizado exitosamente.'
        );
    }

   public function destroy($id)
{
    $pedido = Pedido::findOrFail($id);

    try {
        $pedido->delete();

        return $this->jsonResponse(null, 'Pedido eliminado exitosamente.');
    } catch (QueryException $e) {
        if ($e->getCode() == 23000) {
            return response()->json([
                'success' => false,
                'has_dispatch' => true,
                'message' => 'No se puede eliminar este pedido porque tiene un despacho registrado.'
            ], 409);
        }

        throw $e;
    }
    }

    public function extraerPdf(Request $request, PedidoPdfExtractor $extractor)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf|max:10240',
        ]);

        try {
            $productos = $extractor->extraer($request->file('archivo')->getRealPath());
        } catch (\Throwable $e) {
            return $this->errorResponse('No se pudo procesar el PDF: ' . $e->getMessage(), 422);
        }

        return $this->jsonResponse([
            'productos' => $productos,
        ], count($productos) ? 'Productos detectados.' : 'No se detectaron productos en el PDF.');
    }

    public function subirArchivo(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        $request->validate([
            'archivo' => 'required|file|mimes:pdf|max:10240',
        ]);

        if ($request->hasFile('archivo')) {
            if ($pedido->archivo_pedido && Storage::disk('public')->exists($pedido->archivo_pedido)) {
                Storage::disk('public')->delete($pedido->archivo_pedido);
            }

            $path = $request->file('archivo')->store('pedidos/' . $id, 'public');
            $pedido->update(['archivo_pedido' => $path]);

            return $this->jsonResponse([
                'archivo_pedido' => $path,
                'url' => Storage::disk('public')->url($path),
            ], 'Archivo subido exitosamente.');
        }

        return response()->json(['success' => false, 'message' => 'No se recibió ningún archivo.'], 400);
    }

    public function descargarArchivo($id)
    {
        $pedido = Pedido::findOrFail($id);

        if (!$pedido->archivo_pedido || !Storage::disk('public')->exists($pedido->archivo_pedido)) {
            return response()->json(['success' => false, 'message' => 'El pedido no tiene archivo adjunto.'], 404);
        }

        $path = Storage::disk('public')->path($pedido->archivo_pedido);
        return response()->file($path, ['Content-Type' => 'application/pdf']);
    }

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'id_estado_pedido' => 'required|integer|exists:estados_pedido,id_estado_pedido',
        ]);

        $pedido = Pedido::findOrFail($id);

        $historialActual = HistorialEstadoPedido::where('id_pedido', $id)
            ->whereNull('fecha_fin')
            ->latest('fecha_inicio')
            ->first();

        if ($historialActual) {
            $historialActual->update(['fecha_fin' => now()]);
        }

        HistorialEstadoPedido::create([
            'id_pedido' => $id,
            'id_estado_pedido' => $request->id_estado_pedido,
            'fecha_inicio' => now(),
        ]);

        $pedido->update(['id_estado_pedido' => $request->id_estado_pedido]);

        return $this->jsonResponse(
            $pedido->load('estado', 'historiales.estado'),
            'Estado del pedido actualizado exitosamente.'
        );
    }
}
