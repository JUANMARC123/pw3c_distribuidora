<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\ApiController;
use App\Models\Pedido;
use App\Models\Despacho;
use App\Models\Repartidor;
use App\Models\Vehiculo;
use App\Models\Incidencia;
use App\Models\Farmacia;
use App\Models\Producto;
use App\Models\HojaRuta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends ApiController
{
    public function resumen()
    {
        return $this->jsonResponse([
            'total_pedidos' => Pedido::count(),
            'pedidos_pendientes' => Pedido::where('estado', 'pendiente')->count(),
            'pedidos_aprobados' => Pedido::where('estado', 'aprobado')->count(),
            'pedidos_entregados' => Pedido::where('estado', 'entregado')->count(),
            'pedidos_cancelados' => Pedido::where('estado', 'cancelado')->count(),
            'total_despachos' => Despacho::count(),
            'despachos_pendientes' => Despacho::where('estado_despacho', 'pendiente')->count(),
            'despachos_entregados' => Despacho::where('estado_despacho', 'entregado')->count(),
            'total_farmacias' => Farmacia::count(),
            'total_productos' => Producto::count(),
            'total_repartidores' => Repartidor::count(),
            'repartidores_activos' => Repartidor::where('estado', 'activo')->count(),
            'repartidores_en_ruta' => Repartidor::where('estado', 'en_ruta')->count(),
            'total_vehiculos' => Vehiculo::count(),
            'rutas_activas' => HojaRuta::whereIn('estado_ruta', ['planificado', 'en_progreso'])->count(),
        ]);
    }

    public function pedidosPorEstado()
    {
        $data = Pedido::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get();

        return $this->jsonResponse($data);
    }

    public function despachosPorEstado()
    {
        $data = Despacho::select('estado_despacho as estado', DB::raw('count(*) as total'))
            ->groupBy('estado_despacho')
            ->get();

        return $this->jsonResponse($data);
    }

    public function pedidosPorDia(Request $request)
    {
        $dias = $request->dias ?? 30;

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
        $data = Repartidor::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get();

        return $this->jsonResponse($data);
    }

    public function vehiculosPorEstado()
    {
        $data = Vehiculo::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get();

        return $this->jsonResponse($data);
    }

    public function incidenciasPorTipo()
    {
        $data = Incidencia::select('tipo_incidencia as tipo', DB::raw('count(*) as total'))
            ->groupBy('tipo_incidencia')
            ->get();

        return $this->jsonResponse($data);
    }
}
