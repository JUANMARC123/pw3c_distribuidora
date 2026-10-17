<?php

namespace App\Http\Controllers\Caja;

use App\Http\Controllers\ApiController;
use App\Models\CajaIngreso;
use App\Models\CajaRepartidor;
use App\Models\GastoOperativo;
use App\Models\HojaRuta;
use Illuminate\Http\Request;

class CajaController extends ApiController
{
    public function abrirCaja(Request $request)
    {
        $validated = $request->validate([
            'id_repartidor' => 'required|integer|exists:repartidores,id_repartidor',
            'monto_inicial' => 'required|numeric|min:0',
        ]);

        $existe = CajaRepartidor::where('id_repartidor', $validated['id_repartidor'])
            ->where('fecha', now()->toDateString())
            ->where('estado_caja', 'abierta')
            ->exists();

        if ($existe) {
            return $this->errorResponse('El repartidor ya tiene una caja abierta hoy.', 422);
        }

        $caja = CajaRepartidor::create([
            'id_repartidor' => $validated['id_repartidor'],
            'fecha' => now()->toDateString(),
            'monto_inicial' => $validated['monto_inicial'],
            'ingresos' => 0,
            'monto_final' => null,
            'estado_caja' => 'abierta',
        ]);

        $hojasPendientes = HojaRuta::where('id_repartidor', $validated['id_repartidor'])
            ->where('fecha', $caja->fecha)
            ->whereNull('id_caja')
            ->get();

        foreach ($hojasPendientes as $hoja) {
            $hoja->update(['id_caja' => $caja->id_caja]);
        }

        return $this->jsonResponse($caja, 'Caja abierta exitosamente.', 201);
    }

    public function registrarIngreso(Request $request)
    {
        $validated = $request->validate([
            'id_caja' => 'required|integer|exists:caja_repartidor,id_caja',
            'id_hoja_ruta' => 'nullable|integer|exists:hojas_ruta,id_hoja_ruta',
            'descripcion' => 'nullable|string|max:500',
            'monto' => 'required|numeric|min:0',
        ]);

        $caja = CajaRepartidor::findOrFail($validated['id_caja']);

        $ingreso = CajaIngreso::create([
            'id_caja' => $caja->id_caja,
            'id_hoja_ruta' => $validated['id_hoja_ruta'] ?? null,
            'descripcion' => $validated['descripcion'] ?? null,
            'monto' => $validated['monto'],
        ]);

        $caja->increment('ingresos', $validated['monto']);

        return $this->jsonResponse($ingreso->load('hojaRuta'), 'Ingreso registrado.');
    }

    public function registrarGasto(Request $request)
    {
        $validated = $request->validate([
            'id_caja' => 'required|integer|exists:caja_repartidor,id_caja',
            'tipo_gasto' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'monto' => 'required|numeric|min:0',
            'comprobante' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,webp,heic,heif|max:10240',
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
        $caja = CajaRepartidor::with('hojasRuta')->findOrFail($id);

        $validated = $request->validate([
            'monto_final' => 'required|numeric|min:0',
        ]);

        $totalGastos = GastoOperativo::where('id_caja', $id)->sum('monto');
        $totalCobradoEfectivo = $caja->hojasRuta->sum('cobrado_efectivo');
        $totalCobradoQr = $caja->hojasRuta->sum('cobrado_qr');

        $caja->update([
            'monto_final' => $validated['monto_final'],
            'estado_caja' => 'cerrada',
        ]);

        return $this->jsonResponse($caja->fresh(), 'Caja cerrada exitosamente.');
    }

    public function cajaDelDia(Request $request)
    {
        $periodo = $request->periodo ?? 'dia';
        $fecha = $request->fecha ?? now()->toDateString();
        $idRepartidor = $request->id_repartidor;

        $query = CajaRepartidor::with('hojasRuta', 'repartidor.usuario', 'gastos', 'ingresosDetalle.hojaRuta')
            ->orderByDesc('fecha')
            ->orderByDesc('id_caja');

        $this->aplicarFiltroPeriodo($query, $periodo, $fecha);

        if ($idRepartidor) {
            $query->where('id_repartidor', $idRepartidor);
        }

        $cajas = $query->get();

        $cajas->each(function ($caja) {
            $caja->setAttribute('hojas_disponibles', HojaRuta::where('id_repartidor', $caja->id_repartidor)
                ->where('fecha', $caja->fecha)
                ->whereNull('id_caja')
                ->orderByDesc('id_hoja_ruta')
                ->get(['id_hoja_ruta', 'fecha', 'estado_ruta', 'cobrado_efectivo', 'cobrado_qr']));
        });

        $resumen = [
            'periodo' => $periodo,
            'fecha' => $fecha,
            'total_ingresos' => $cajas->sum('ingresos'),
            'total_gastos' => GastoOperativo::whereIn('id_caja', $cajas->pluck('id_caja'))->sum('monto'),
            'cajas' => $cajas,
        ];

        return $this->jsonResponse($resumen);
    }

    public function vincularHoja(Request $request, $id)
    {
        $validated = $request->validate([
            'id_hoja_ruta' => 'required|integer|exists:hojas_ruta,id_hoja_ruta',
        ]);

        $caja = CajaRepartidor::with('repartidor.usuario')->findOrFail($id);

        if ($caja->estado_caja !== 'abierta') {
            return $this->errorResponse('Solo se pueden vincular hojas a una caja abierta.', 422);
        }

        $hoja = HojaRuta::findOrFail($validated['id_hoja_ruta']);

        if ((int) $hoja->id_repartidor !== (int) $caja->id_repartidor) {
            return $this->errorResponse('La hoja de ruta no pertenece al repartidor de esta caja.', 422);
        }

        if ($hoja->fecha->format('Y-m-d') !== $caja->fecha->format('Y-m-d')) {
            return $this->errorResponse('La hoja de ruta no corresponde a la fecha de esta caja.', 422);
        }

        if ($hoja->id_caja && (int) $hoja->id_caja !== (int) $caja->id_caja) {
            return $this->errorResponse('La hoja de ruta ya esta vinculada a otra caja.', 422);
        }

        $hoja->update(['id_caja' => $caja->id_caja]);

        $caja->load('hojasRuta');
        $caja->setAttribute('hojas_disponibles', HojaRuta::where('id_repartidor', $caja->id_repartidor)
            ->where('fecha', $caja->fecha)
            ->whereNull('id_caja')
            ->orderByDesc('id_hoja_ruta')
            ->get(['id_hoja_ruta', 'fecha', 'estado_ruta', 'cobrado_efectivo', 'cobrado_qr']));

        return $this->jsonResponse($caja, 'Hoja de ruta vinculada a la caja.');
    }

    public function resumenDiario(Request $request)
    {
        $periodo = $request->periodo ?? 'dia';
        $fecha = $request->fecha ?? now()->toDateString();

        $query = CajaRepartidor::with('repartidor.usuario')
            ->orderByDesc('fecha');

        $this->aplicarFiltroPeriodo($query, $periodo, $fecha);

        $cajas = $query->get();

        $totalIngresos = $cajas->sum('ingresos');

        return $this->jsonResponse([
            'periodo' => $periodo,
            'fecha' => $fecha,
            'total_cajas' => $cajas->count(),
            'total_ingresos' => $totalIngresos,
            'cajas' => $cajas,
        ]);
    }

    protected function aplicarFiltroPeriodo($query, $periodo, $fecha)
    {
        switch ($periodo) {
            case 'todos':
                break;

            case 'semana':
                $inicio = \Illuminate\Support\Carbon::parse($fecha)->startOfWeek(\Illuminate\Support\Carbon::MONDAY);
                $fin = $inicio->copy()->endOfWeek(\Illuminate\Support\Carbon::SUNDAY);
                $query->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()]);
                break;

            case 'mes':
                $d = \Illuminate\Support\Carbon::parse($fecha);
                $query->whereYear('fecha', $d->year)
                    ->whereMonth('fecha', $d->month);
                break;

            case 'año':
                $query->whereYear('fecha', \Illuminate\Support\Carbon::parse($fecha)->year);
                break;

            default:
                $query->where('fecha', $fecha);
        }
    }
}
