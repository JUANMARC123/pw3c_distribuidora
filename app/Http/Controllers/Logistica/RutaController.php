<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\ApiController;
use App\Models\Logistica\Ruta;
use App\Models\Logistica\RutaParada;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class RutaController extends ApiController
{
    public function index(Request $request)
    {
        $query = Ruta::withCount('paradas');

        return $this->paginatedResponse(
            $query->orderBy('nombre_ruta')->paginate($request->per_page ?? 15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_ruta' => 'required|string|max:100|unique:rutas,nombre_ruta',
        ]);

        $ruta = Ruta::create($data);

        return $this->jsonResponse($ruta, 'Ruta creada exitosamente.', 201);
    }

    public function show($id)
    {
        $ruta = Ruta::with('paradas.farmacia', 'controles')->findOrFail($id);
        return $this->jsonResponse($ruta);
    }

    public function update(Request $request, $id)
    {
        $ruta = Ruta::findOrFail($id);

        $data = $request->validate([
            'nombre_ruta' => 'required|string|max:100|unique:rutas,nombre_ruta,' . $id . ',id_ruta',
        ]);

        $ruta->update($data);

        return $this->jsonResponse($ruta, 'Ruta actualizada exitosamente.');
    }

    public function destroy($id)
{
    $ruta = Ruta::findOrFail($id);

    try {
        $ruta->delete();

        return $this->jsonResponse(null, 'Ruta eliminada exitosamente.');
    } catch (QueryException $e) {
        if ($e->getCode() == 23000) {
            return response()->json([
                'success' => false,
                'has_controls' => true,
                'message' => 'No se puede eliminar esta ruta porque está asociada a controles de ruta registrados.'
            ], 409);
        }

        throw $e;
    }
}
    public function paradas($id)
    {
        $ruta = Ruta::findOrFail($id);
        return $this->jsonResponse(
            $ruta->paradas()->with('farmacia')->orderBy('orden_parada')->get()
        );
    }

    public function storeParada(Request $request, $id)
    {
        $ruta = Ruta::findOrFail($id);

        $data = $request->validate([
            'id_farmacia' => 'required|integer|exists:farmacias,id_farmacia',
            'orden_parada' => 'required|integer|min:1|unique:ruta_paradas,orden_parada,NULL,id_parada,id_ruta,' . $id,
            'hora_estimada' => 'required|date_format:H:i:s',
        ]);

        $data['id_ruta'] = $ruta->id_ruta;
        $parada = RutaParada::create($data);

        return $this->jsonResponse(
            $parada->load('farmacia'),
            'Parada agregada exitosamente.',
            201
        );
    }

    public function showParada($id, $paradaId)
    {
        $ruta = Ruta::findOrFail($id);
        $parada = RutaParada::where('id_ruta', $ruta->id_ruta)->with('farmacia')->findOrFail($paradaId);

        return $this->jsonResponse($parada);
    }

    public function updateParada(Request $request, $id, $paradaId)
    {
        $ruta = Ruta::findOrFail($id);
        $parada = RutaParada::where('id_ruta', $ruta->id_ruta)->findOrFail($paradaId);

        $data = $request->validate([
            'id_farmacia' => 'sometimes|integer|exists:farmacias,id_farmacia',
            'orden_parada' => 'sometimes|integer|min:1|unique:ruta_paradas,orden_parada,' . $paradaId . ',id_parada,id_ruta,' . $id,
            'hora_estimada' => 'sometimes|date_format:H:i:s',
        ]);

        $parada->update($data);

        return $this->jsonResponse(
            $parada->load('farmacia'),
            'Parada actualizada exitosamente.'
        );
    }

    public function destroyParada($id, $paradaId)
    {
        $ruta = Ruta::findOrFail($id);
        $parada = RutaParada::where('id_ruta', $ruta->id_ruta)->findOrFail($paradaId);
        $parada->delete();

        return $this->jsonResponse(null, 'Parada eliminada exitosamente.');
    }
}
