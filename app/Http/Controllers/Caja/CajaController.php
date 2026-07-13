<?php

namespace App\Http\Controllers\Caja;

use App\Http\Controllers\ApiController;
use App\Models\CajaRepartidor;
use App\Models\GastoOperativo;
use App\Models\HojaRuta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CajaController extends ApiController
{
    public function abrirCaja(Request $request)
    {
        $validated = $request->validate([
            'id_hoja_ruta' => 'required|integer|unique:caja_repartidor,id_hoja_ruta|exists:hojas_ruta,id_hoja_ruta',
            'id_repartidor' => 'required|integer|exists:repartidores,id_repartidor',
            'fecha' => 'required|date',
            'monto_inicial' => 'required|numeric|min:0',
        ]);

        $existe = CajaRepartidor::where('id_repartidor', $validated['id_repartidor'])
            ->where('fecha', $validated['fecha'])
            ->exists();

        if ($existe) {
            return $this->errorResponse('El repartidor ya tiene una caja abierta para esta fecha.', 422);
        }

        $caja = CajaRepartidor::create([
            'id_hoja_ruta' => $validated['id_hoja_ruta'],
            'id_repartidor' => $validated['id_repartidor'],
            'fecha' => $validated['fecha'],
            'monto_inicial' => $validated['monto_inicial'],
            'ingresos' => 0,
            'monto_final' => null,
            'estado_caja' => 'abierta',
        ]);

        return $this->jsonResponse($caja, 'Caja abierta exitosamente.', 201);
    }

    public function registrarIngreso(Request $request)
    {
        $validated = $request->validate([
            'id_caja' => 'required|integer|exists:caja_repartidor,id_caja',
            'monto' => 'required|numeric|min:0',
        ]);

        $caja = CajaRepartidor::findOrFail($validated['id_caja']);

        $caja->increment('ingresos', $validated['monto']);

        return $this->jsonResponse($caja->fresh(), 'Ingreso registrado.');
    }

    public function registrarGasto(Request $request)
    {
        $validated = $request->validate([
            'id_caja' => 'required|integer|exists:caja_repartidor,id_caja',
            'tipo_gasto' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'monto' => 'required|numeric|min:0',
            'comprobante' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:2048',
        ]);

        $caja = CajaRepartidor::findOrFail($validated['id_caja']);

        $data = [
            'id_caja' => $caja->id_caja,
            'tipo_gasto' => $validated['tipo_gasto'],
            'descripcion' => $validated['descripcion'] ?? null,
            'monto' => $validated['monto'],
        ];

        if ($request->hasFile('comprobante')) {
            $data['comprobante'] = $request->file('comprobante')->store('gastos', 'public');
        }

        $gasto = GastoOperativo::create($data);

        return $this->jsonResponse($gasto, 'Gasto registrado.');
    }

    public function cerrarCaja(Request $request, $id)
    {
        $caja = CajaRepartidor::findOrFail($id);

        $validated = $request->validate([
            'monto_final' => 'required|numeric|min:0',
        ]);

        $totalGastos = GastoOperativo::where('id_caja', $id)->sum('monto');

        $caja->update([
            'monto_final' => $validated['monto_final'],
            'estado_caja' => 'cerrada',
        ]);

        return $this->jsonResponse($caja->fresh(), 'Caja cerrada exitosamente.');
    }

    public function cajaDelDia(Request $request)
    {
        $fecha = $request->fecha ?? now()->toDateString();
        $idRepartidor = $request->id_repartidor;

        $query = CajaRepartidor::with('hojaRuta', 'repartidor.usuario', 'gastos')
            ->where('fecha', $fecha);

        if ($idRepartidor) {
            $query->where('id_repartidor', $idRepartidor);
        }

        $cajas = $query->get();

        $resumen = [
            'total_ingresos' => $cajas->sum('ingresos'),
            'total_gastos' => GastoOperativo::whereIn('id_caja', $cajas->pluck('id_caja'))->sum('monto'),
            'cajas' => $cajas,
        ];

        return $this->jsonResponse($resumen);
    }

    public function resumenDiario(Request $request)
    {
        $fecha = $request->fecha ?? now()->toDateString();

        $cajas = CajaRepartidor::with('repartidor.usuario')
            ->where('fecha', $fecha)
            ->get();

        $totalIngresos = $cajas->sum('ingresos');

        return $this->jsonResponse([
            'fecha' => $fecha,
            'total_cajas' => $cajas->count(),
            'total_ingresos' => $totalIngresos,
            'cajas' => $cajas,
        ]);
    }
}
