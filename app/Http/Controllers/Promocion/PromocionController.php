<?php

namespace App\Http\Controllers\Promocion;

use App\Http\Controllers\ApiController;
use App\Models\Promocion\Promocion;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class PromocionController extends ApiController
{
    public function index(Request $request)
    {
        $query = Promocion::with('tipoPromocion', 'usuario', 'productos.producto');

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo);
        }

        if ($request->filled('id_tipo_promocion')) {
            $query->where('id_tipo_promocion', $request->id_tipo_promocion);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nombre_promocion', 'like', "%{$search}%");
        }

        return $this->paginatedResponse(
            $query->orderBy('fecha_inicio', 'desc')->paginate($request->per_page ?? 15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_promocion' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'id_tipo_promocion' => 'required|integer|exists:tipos_promocion,id_tipo_promocion',
            'descuento' => 'required|numeric|min:0',
            'es_porcentual' => 'required|boolean',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'activo' => 'required|boolean',
            'productos' => 'required|array|min:1',
            'productos.*.id_producto' => 'required|integer|exists:productos,id_producto',
            'productos.*.cantidad_minima' => 'required|numeric|min:0.01',
        ]);

        $productos = $data['productos'];
        unset($data['productos']);

        $data['id_usuario'] = auth()->id();
        $promocion = Promocion::create($data);

        foreach ($productos as $prod) {
            $promocion->productos()->create($prod);
        }

        AuditService::log(auth()->id(), 'crear', 'promociones', $promocion->id_promocion);

        return $this->jsonResponse(
            $promocion->load('tipoPromocion', 'usuario', 'productos.producto'),
            'Promoción creada exitosamente.',
            201
        );
    }

    public function show($id)
    {
        $promocion = Promocion::with([
            'tipoPromocion',
            'usuario',
            'productos.producto',
        ])->findOrFail($id);

        return $this->jsonResponse($promocion);
    }

    public function update(Request $request, $id)
    {
        $promocion = Promocion::findOrFail($id);

        $data = $request->validate([
            'nombre_promocion' => 'sometimes|string|max:200',
            'descripcion' => 'nullable|string',
            'id_tipo_promocion' => 'sometimes|integer|exists:tipos_promocion,id_tipo_promocion',
            'descuento' => 'sometimes|numeric|min:0',
            'es_porcentual' => 'sometimes|boolean',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'activo' => 'sometimes|boolean',
            'productos' => 'sometimes|array|min:1',
            'productos.*.id_producto' => 'required|integer|exists:productos,id_producto',
            'productos.*.cantidad_minima' => 'required|numeric|min:0.01',
        ]);

        $promocion->update($data);

        if ($request->has('productos')) {
            $promocion->productos()->delete();
            foreach ($data['productos'] as $prod) {
                $promocion->productos()->create($prod);
            }
        }

        AuditService::log(auth()->id(), 'editar', 'promociones', $promocion->id_promocion);

        return $this->jsonResponse(
            $promocion->load('tipoPromocion', 'usuario', 'productos.producto'),
            'Promoción actualizada exitosamente.'
        );
    }

    public function destroy($id)
    {
        $promocion = Promocion::findOrFail($id);

        try {
            $promocion->delete();
            AuditService::log(auth()->id(), 'eliminar', 'promociones', $id);
            return $this->jsonResponse(null, 'Promoción eliminada exitosamente.');
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                return $this->errorResponse('No se puede eliminar esta promoción porque tiene registros asociados.', 409);
            }
            throw $e;
        }
    }
}
