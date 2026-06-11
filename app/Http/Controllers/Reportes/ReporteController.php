<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\ApiController;
use App\Models\Seguridad\Usuario;
use App\Models\Farmacia\Farmacia;
use App\Models\Pedido\Pedido;
use App\Models\Repartidor\Repartidor;
use App\Models\Vehiculo\Vehiculo;
use App\Models\Logistica\Ruta;
use App\Models\Despacho\Despacho;
use App\Models\Evidencia\Incidencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends ApiController
{
    public function resumen()
    {
        return $this->jsonResponse([
            'total_usuarios' => Usuario::count(),
            'total_farmacias' => Farmacia::count(),
            'total_pedidos' => Pedido::count(),
            'total_repartidores' => Repartidor::count(),
            'total_vehiculos' => Vehiculo::count(),
            'total_rutas' => Ruta::count(),
            'total_despachos' => Despacho::count(),
            'total_incidencias' => Incidencia::count(),
        ]);
    }

    public function pedidosPorEstado()
    {
        $data = Pedido::select('id_estado_pedido', DB::raw('count(*) as total'))
            ->with('estado')
            ->groupBy('id_estado_pedido')
            ->get()
            ->map(function ($item) {
                return [
                    'id_estado_pedido' => $item->id_estado_pedido,
                    'nombre_estado' => $item->estado->nombre_estado ?? 'Desconocido',
                    'total' => $item->total,
                ];
            });

        return $this->jsonResponse($data);
    }

    public function despachosPorEstado()
    {
        $data = Despacho::select('id_estado_despacho', DB::raw('count(*) as total'))
            ->with('estado')
            ->groupBy('id_estado_despacho')
            ->get()
            ->map(function ($item) {
                return [
                    'id_estado_despacho' => $item->id_estado_despacho,
                    'nombre_estado' => $item->estado->nombre_estado ?? 'Desconocido',
                    'total' => $item->total,
                ];
            });

        return $this->jsonResponse($data);
    }

    public function pedidosPorDia(Request $request)
    {
        $dias = $request->input('dias', 30);

        $data = Pedido::select(
                DB::raw('DATE(fecha_pedido) as fecha'),
                DB::raw('count(*) as total')
            )
            ->where('fecha_pedido', '>=', now()->subDays($dias))
            ->groupBy(DB::raw('DATE(fecha_pedido)'))
            ->orderBy('fecha')
            ->get();

        return $this->jsonResponse($data);
    }

    public function repartidoresPorEstado()
    {
        $data = Repartidor::select('id_estado_repartidor', DB::raw('count(*) as total'))
            ->with('estado')
            ->groupBy('id_estado_repartidor')
            ->get()
            ->map(function ($item) {
                return [
                    'id_estado_repartidor' => $item->id_estado_repartidor,
                    'nombre_estado' => $item->estado->nombre_estado ?? 'Desconocido',
                    'total' => $item->total,
                ];
            });

        return $this->jsonResponse($data);
    }

    public function vehiculosPorEstado()
    {
        $data = Vehiculo::select('id_estado_vehiculo', DB::raw('count(*) as total'))
            ->with('estado')
            ->groupBy('id_estado_vehiculo')
            ->get()
            ->map(function ($item) {
                return [
                    'id_estado_vehiculo' => $item->id_estado_vehiculo,
                    'nombre_estado' => $item->estado->nombre_estado ?? 'Desconocido',
                    'total' => $item->total,
                ];
            });

        return $this->jsonResponse($data);
    }

    public function incidenciasPorTipo()
    {
        $data = Incidencia::select('id_tipo_incidencia', DB::raw('count(*) as total'))
            ->with('tipoIncidencia')
            ->groupBy('id_tipo_incidencia')
            ->get()
            ->map(function ($item) {
                return [
                    'id_tipo_incidencia' => $item->id_tipo_incidencia,
                    'nombre_tipo' => $item->tipoIncidencia->nombre_tipo ?? 'Desconocido',
                    'total' => $item->total,
                ];
            });

        return $this->jsonResponse($data);
    }
}
