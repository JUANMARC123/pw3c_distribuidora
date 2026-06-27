<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\ApiController;
use App\Models\Logistica\ControlRuta;
use App\Models\Logistica\HistorialEstadoRuta;
use Illuminate\Http\Request;

class ControlRutaController extends ApiController
{
    public function index(Request $request)
    {
        $query = ControlRuta::with('ruta', 'repartidor.usuario', 'vehiculo');

        if ($request->filled('id_ruta')) {
            $query->where('id_ruta', $request->id_ruta);
        }

        if ($request->filled('fecha_ruta')) {
            $query->where('fecha_ruta', $request->fecha_ruta);
        }

        if ($request->filled('id_repartidor')) {
            $query->where('id_repartidor', $request->id_repartidor);
        }

        if ($request->filled('id_vehiculo')) {
            $query->where('id_vehiculo', $request->id_vehiculo);
        }

        if ($request->filled('fecha_desde')) {
            $query->where('fecha_ruta', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_ruta', '<=', $request->fecha_hasta);
        }

        return $this->safePaginate(
            $query->orderBy('fecha_ruta', 'desc')->orderBy('hora_salida', 'desc'), $request
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_ruta' => 'required|integer|exists:rutas,id_ruta',
            'fecha_ruta' => 'required|date',
            'hora_salida' => 'required|date_format:H:i:s',
            'id_repartidor' => 'required|integer|exists:repartidores,id_repartidor',
            'id_vehiculo' => 'required|integer|exists:vehiculos,id_vehiculo',
            'id_estado_ruta' => 'sometimes|integer|exists:estados_ruta,id_estado_ruta',
        ]);

        $data['id_estado_ruta'] = $data['id_estado_ruta'] ?? 1;

        $exists = ControlRuta::where('id_ruta', $data['id_ruta'])
            ->where('fecha_ruta', $data['fecha_ruta'])
            ->exists();

        if ($exists) {
            return $this->errorResponse('Ya existe un control para esta ruta en la fecha indicada.', 422);
        }

        $control = ControlRuta::create($data);

        HistorialEstadoRuta::create([
            'id_control_ruta' => $control->id_control_ruta,
            'id_estado_ruta' => $data['id_estado_ruta'],
            'fecha_inicio' => now(),
        ]);

        return $this->jsonResponse(
            $control->load('ruta', 'repartidor.usuario', 'vehiculo'),
            'Control de ruta creado exitosamente.',
            201
        );
    }

    public function show($id)
    {
        $control = ControlRuta::with([
            'ruta.paradas.farmacia',
            'repartidor.usuario',
            'vehiculo.modelo.marca',
            'despachos',
        ])->findOrFail($id);

        return $this->jsonResponse($control);
    }

    public function update(Request $request, $id)
    {
        $control = ControlRuta::findOrFail($id);

        $data = $request->validate([
            'hora_salida' => 'sometimes|date_format:H:i:s',
            'id_repartidor' => 'sometimes|integer|exists:repartidores,id_repartidor',
            'id_vehiculo' => 'sometimes|integer|exists:vehiculos,id_vehiculo',
            'id_estado_ruta' => 'sometimes|integer|exists:estados_ruta,id_estado_ruta',
        ]);

        if (isset($data['id_estado_ruta']) && (int) $data['id_estado_ruta'] !== (int) $control->id_estado_ruta) {
            $this->registrarHistorialEstado($control->id_control_ruta, (int) $data['id_estado_ruta']);
        }

        $control->update($data);

        return $this->jsonResponse(
            $control->load('ruta', 'repartidor.usuario', 'vehiculo'),
            'Control de ruta actualizado exitosamente.'
        );
    }

    public function destroy($id)
    {
        $control = ControlRuta::findOrFail($id);
        $control->delete();

        return $this->jsonResponse(null, 'Control de ruta eliminado exitosamente.');
    }

    public function registrarLlegada(Request $request, $id)
    {
        $request->validate([
            'hora_llegada_real' => 'nullable|date_format:H:i:s',
        ]);

        $control = ControlRuta::findOrFail($id);
        $control->update([
            'hora_llegada_real' => $request->hora_llegada_real ?? now()->format('H:i:s'),
            'id_estado_ruta' => 4,
        ]);

        $this->registrarHistorialEstado($control->id_control_ruta, 4);

        return $this->jsonResponse(
            $control->load('ruta', 'repartidor.usuario', 'vehiculo'),
            'Hora de llegada registrada exitosamente.'
        );
    }

    private function registrarHistorialEstado(int $idControlRuta, int $idEstadoRuta): void
    {
        $historialActual = HistorialEstadoRuta::where('id_control_ruta', $idControlRuta)
            ->whereNull('fecha_fin')
            ->latest('fecha_inicio')
            ->first();

        if ($historialActual) {
            $historialActual->update(['fecha_fin' => now()]);
        }

        HistorialEstadoRuta::create([
            'id_control_ruta' => $idControlRuta,
            'id_estado_ruta' => $idEstadoRuta,
            'fecha_inicio' => now(),
        ]);
    }
}
