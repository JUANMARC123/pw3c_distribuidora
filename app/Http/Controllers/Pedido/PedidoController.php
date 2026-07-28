<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\ApiController;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Services\Pedido\PedidoPdfExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PedidoController extends ApiController
{
    public function index(Request $request)
    {
        $query = Pedido::with('farmacia', 'usuario', 'detalles.producto');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
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

        return $this->safePaginate($query->orderBy('fecha_pedido', 'desc'), $request);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_farmacia' => 'required|integer|exists:farmacias,id_farmacia',
            'observaciones' => 'nullable|string',
            'subtotal' => 'nullable|numeric|min:0',
            'descuento' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'archivo_pedido' => 'nullable|string',
        ]);

        $validated['id_usuario'] = $request->user()->id_usuario;
        $validated['estado'] = 'pendiente';
        $validated['fecha_pedido'] = now();

        $pedido = Pedido::create($validated);

        return $this->jsonResponse($pedido->load('farmacia', 'usuario'), 'Pedido creado exitosamente.', 201);
    }

    public function show($id)
    {
        $pedido = Pedido::with([
            'farmacia',
            'usuario',
            'detalles.producto',
            'despacho.hojaRutaDetalle.hojaRuta',
        ])->findOrFail($id);

        return $this->jsonResponse($pedido);
    }

    public function update(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        $validated = $request->validate([
            'id_farmacia' => 'sometimes|integer|exists:farmacias,id_farmacia',
            'observaciones' => 'nullable|string',
            'estado' => 'sometimes|in:pendiente,aprobado,preparacion,en_ruta,entregado,cancelado',
            'metodo_pago' => 'nullable|in:efectivo,qr,transferencia,credito',
            'subtotal' => 'nullable|numeric|min:0',
            'descuento' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'archivo_pedido' => 'nullable|string',
        ]);

        $pedido->update($validated);

        return $this->jsonResponse($pedido->load('farmacia', 'usuario'), 'Pedido actualizado exitosamente.');
    }

    public function destroy($id)
    {
        try {
            $pedido = Pedido::findOrFail($id);
            $pedido->detalles()->delete();
            $pedido->delete();
            return $this->jsonResponse(null, 'Pedido eliminado exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse('No se puede eliminar el pedido.', 409);
        }
    }

    public function cambiarEstado(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        $validated = $request->validate([
            'estado' => 'required|in:pendiente,aprobado,preparacion,en_ruta,entregado,cancelado',
        ]);

        $pedido->update(['estado' => $validated['estado']]);

        return $this->jsonResponse($pedido, 'Estado del pedido actualizado.');
    }

    public function extraerPdf(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf|max:10240',
        ]);

        try {
            $extractor = new PedidoPdfExtractor();
            $resultado = $extractor->extraer($request->file('archivo'));

            return $this->jsonResponse($resultado, 'PDF procesado exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al procesar el PDF: ' . $e->getMessage(), 500);
        }
    }

    public function subirArchivo(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        $request->validate([
            'archivo' => 'required|file|mimes:pdf|max:10240',
        ]);

        $archivo = $request->file('archivo');
        $nombre = "pedido_{$id}_" . time() . ".pdf";
        $ruta = $archivo->storeAs('pedidos', $nombre, 'public');

        $pedido->update(['archivo_pedido' => $ruta]);

        return $this->jsonResponse(['archivo_pedido' => $ruta], 'Archivo subido exitosamente.');
    }

    public function descargarArchivo($id)
    {
        $pedido = Pedido::findOrFail($id);

        if (!$pedido->archivo_pedido || !Storage::disk('public')->exists($pedido->archivo_pedido)) {
            return $this->errorResponse('Archivo no encontrado.', 404);
        }

        return Storage::disk('public')->download($pedido->archivo_pedido);
    }
}
