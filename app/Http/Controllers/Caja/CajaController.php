<?php

namespace App\Http\Controllers\Caja;

use App\Http\Controllers\ApiController;
use App\Models\Logistica\ControlRuta;
use App\Models\Caja\CajaRepartidor;
use App\Models\Caja\GastoOperativo;
use App\Models\Repartidor\Repartidor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CajaController extends ApiController
{
    public function abrirCaja(Request $request)
    {
        $request->validate([
            'id_repartidor' => 'required|integer|exists:repartidores,id_repartidor',
            'id_control_ruta' => 'nullable|integer|exists:control_rutas,id_control_ruta',
            'monto_inicial' => 'required|numeric|min:0',
        ]);

        $existe = CajaRepartidor::where('id_repartidor', $request->id_repartidor)
            ->where('fecha', now()->toDateString())
            ->exists();

        if ($existe) {
            return $this->errorResponse('El repartidor ya tiene una caja abierta hoy.', 409);
        }

        $caja = CajaRepartidor::create([
            'id_control_ruta' => $request->id_control_ruta,
            'id_repartidor' => $request->id_repartidor,
            'fecha' => now()->toDateString(),
            'monto_inicial' => $request->monto_inicial,
            'ingresos' => 0,
            'gastos' => 0,
            'monto_final' => $request->monto_inicial,
            'id_estado_caja' => 1,
        ]);

        return $this->jsonResponse($caja, 'Caja abierta exitosamente.', 201);
    }

    public function registrarIngreso(Request $request)
    {
        $request->validate([
            'id_caja' => 'required|integer|exists:caja_repartidor,id_caja',
            'monto' => 'required|numeric|min:0.01',
            'concepto' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $caja = CajaRepartidor::findOrFail($request->id_caja);

        if ($caja->estado->nombre_estado !== 'Abierta') {
            return $this->errorResponse('La caja no está abierta.', 400);
        }

        $caja->increment('ingresos', $request->monto);
        $caja->increment('monto_final', $request->monto);

        return $this->jsonResponse($caja->fresh()->load('estado'), 'Ingreso registrado.');
    }

    public function registrarGasto(Request $request)
    {
        $request->validate([
            'id_caja' => 'required|integer|exists:caja_repartidor,id_caja',
            'tipo_gasto' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:255',
            'monto' => 'required|numeric|min:0.01',
        ]);

        $caja = CajaRepartidor::findOrFail($request->id_caja);

        if ($caja->estado->nombre_estado !== 'Abierta') {
            return $this->errorResponse('La caja no está abierta.', 400);
        }

        DB::transaction(function () use ($request, $caja) {
            GastoOperativo::create([
                'id_caja' => $caja->id_caja,
                'tipo_gasto' => $request->tipo_gasto,
                'descripcion' => $request->descripcion,
                'monto' => $request->monto,
                'fecha' => now(),
            ]);

            $caja->increment('gastos', $request->monto);
            $caja->decrement('monto_final', $request->monto);
        });

        return $this->jsonResponse([
            'caja' => $caja->fresh()->load('estado', 'gastos'),
        ], 'Gasto registrado.');
    }

    public function cerrarCaja($id)
    {
        $caja = CajaRepartidor::with('repartidor.usuario', 'gastos')->findOrFail($id);

        if ($caja->estado->nombre_estado !== 'Abierta') {
            return $this->errorResponse('La caja ya está cerrada o revisada.', 400);
        }

        $caja->update(['id_estado_caja' => 2]);

        return $this->jsonResponse($caja, 'Caja cerrada exitosamente.');
    }

    public function cajaDelDia(Request $request)
    {
        $fecha = $request->fecha ?? now()->toDateString();
        $id_repartidor = $request->id_repartidor;

        $query = CajaRepartidor::with([
            'repartidor.usuario',
            'controlRuta',
            'estado',
            'gastos',
        ])->where('fecha', $fecha);

        if ($id_repartidor) {
            $query->where('id_repartidor', $id_repartidor);
        }

        $cajas = $query->orderBy('id_repartidor')->get();

        $totales = [
            'total_ingresos' => $cajas->sum('ingresos'),
            'total_gastos' => $cajas->sum('gastos'),
            'total_final' => $cajas->sum('monto_final'),
            'repartidores_activos' => $cajas->count(),
        ];

        return $this->jsonResponse([
            'cajas' => $cajas,
            'totales' => $totales,
            'fecha' => $fecha,
        ]);
    }

    public function resumenDiario()
    {
        $hoy = now()->toDateString();

        $cajasHoy = CajaRepartidor::where('fecha', $hoy)->get();

        return $this->jsonResponse([
            'fecha' => $hoy,
            'repartidores_con_caja' => $cajasHoy->count(),
            'ingresos_totales' => $cajasHoy->sum('ingresos'),
            'gastos_totales' => $cajasHoy->sum('gastos'),
            'saldo_total' => $cajasHoy->sum('monto_final'),
            'cajas_abiertas' => $cajasHoy->where('id_estado_caja', 1)->count(),
            'cajas_cerradas' => $cajasHoy->where('id_estado_caja', 2)->count(),
        ]);
    }
}
