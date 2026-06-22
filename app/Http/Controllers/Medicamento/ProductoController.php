<?php

namespace App\Http\Controllers\Medicamento;

use App\Http\Controllers\ApiController;
use App\Models\Medicamento\Producto;
use App\Models\Medicamento\Lote;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class ProductoController extends ApiController
{
    public function index(Request $request)
    {
        $query = Producto::with('categoria', 'laboratorio', 'presentacion', 'unidadMedida');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre_producto', 'like', "%{$search}%")
                  ->orWhere('codigo_producto', 'like', "%{$search}%");
            });
        }

        if ($request->filled('id_categoria')) {
            $query->where('id_categoria', $request->id_categoria);
        }

        if ($request->filled('id_laboratorio')) {
            $query->where('id_laboratorio', $request->id_laboratorio);
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo);
        }

        return $this->paginatedResponse(
            $query->orderBy('nombre_producto')->paginate($request->per_page ?? 15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo_producto' => 'required|string|max:50|unique:productos,codigo_producto',
            'nombre_producto' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'id_categoria' => 'required|integer|exists:categorias,id_categoria',
            'id_laboratorio' => 'required|integer|exists:laboratorios,id_laboratorio',
            'id_presentacion' => 'required|integer|exists:presentaciones,id_presentacion',
            'id_unidad_medida' => 'required|integer|exists:unidades_medida,id_unidad_medida',
            'concentracion' => 'nullable|string|max:100',
            'precio_unitario' => 'required|numeric|min:0',
            'requiere_receta' => 'boolean',
            'activo' => 'boolean',
        ]);

        $producto = Producto::create($data);

        AuditService::log(auth()->id(), 'crear', 'productos', $producto->id_producto);

        return $this->jsonResponse(
            $producto->load('categoria', 'laboratorio', 'presentacion', 'unidadMedida'),
            'Producto creado exitosamente.',
            201
        );
    }

    public function show($id)
    {
        $producto = Producto::with([
            'categoria',
            'laboratorio',
            'presentacion',
            'unidadMedida',
            'lotes',
        ])->findOrFail($id);

        return $this->jsonResponse($producto);
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $data = $request->validate([
            'codigo_producto' => 'sometimes|string|max:50|unique:productos,codigo_producto,' . $id . ',id_producto',
            'nombre_producto' => 'sometimes|string|max:200',
            'descripcion' => 'nullable|string',
            'id_categoria' => 'sometimes|integer|exists:categorias,id_categoria',
            'id_laboratorio' => 'sometimes|integer|exists:laboratorios,id_laboratorio',
            'id_presentacion' => 'sometimes|integer|exists:presentaciones,id_presentacion',
            'id_unidad_medida' => 'sometimes|integer|exists:unidades_medida,id_unidad_medida',
            'concentracion' => 'nullable|string|max:100',
            'precio_unitario' => 'sometimes|numeric|min:0',
            'requiere_receta' => 'boolean',
            'activo' => 'boolean',
        ]);

        $producto->update($data);

        AuditService::log(auth()->id(), 'editar', 'productos', $producto->id_producto);

        return $this->jsonResponse(
            $producto->load('categoria', 'laboratorio', 'presentacion', 'unidadMedida'),
            'Producto actualizado exitosamente.'
        );
    }

    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);

        try {
            $producto->delete();

            AuditService::log(auth()->id(), 'eliminar', 'productos', $id);

            return $this->jsonResponse(null, 'Producto eliminado exitosamente.');
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el producto porque tiene lotes registrados.'
                ], 409);
            }
            throw $e;
        }
    }

    public function lotes($id)
    {
        $producto = Producto::findOrFail($id);

        return $this->jsonResponse(
            Lote::where('id_producto', $id)->orderBy('fecha_vencimiento')->get()
        );
    }

    public function storeLote(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $data = $request->validate([
            'codigo_lote' => 'required|string|max:50|unique:lotes,codigo_lote',
            'fecha_fabricacion' => 'required|date',
            'fecha_vencimiento' => 'required|date|after:fecha_fabricacion',
            'precio_compra' => 'nullable|numeric|min:0',
        ]);

        $data['id_producto'] = $producto->id_producto;

        $lote = Lote::create($data);

        AuditService::log(auth()->id(), 'crear', 'lotes', $lote->id_lote);

        return $this->jsonResponse($lote, 'Lote registrado exitosamente.', 201);
    }

    public function updateLote(Request $request, $id, $loteId)
    {
        $producto = Producto::findOrFail($id);
        $lote = Lote::where('id_producto', $id)->findOrFail($loteId);

        $data = $request->validate([
            'codigo_lote' => 'sometimes|string|max:50|unique:lotes,codigo_lote,' . $loteId . ',id_lote',
            'fecha_fabricacion' => 'sometimes|date',
            'fecha_vencimiento' => 'sometimes|date|after:fecha_fabricacion',
            'precio_compra' => 'nullable|numeric|min:0',
        ]);

        $lote->update($data);

        AuditService::log(auth()->id(), 'editar', 'lotes', $lote->id_lote);

        return $this->jsonResponse($lote, 'Lote actualizado exitosamente.');
    }

    public function destroyLote($id, $loteId)
    {
        $producto = Producto::findOrFail($id);
        $lote = Lote::where('id_producto', $id)->findOrFail($loteId);

        $lote->delete();

        AuditService::log(auth()->id(), 'eliminar', 'lotes', $loteId);

        return $this->jsonResponse(null, 'Lote eliminado exitosamente.');
    }
}
