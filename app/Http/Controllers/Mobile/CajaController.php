<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\ApiController;
use App\Models\CajaRepartidor;
use App\Models\GastoOperativo;
use App\Models\Repartidor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CajaController extends ApiController
{
    /**
     * Busca la caja del día para un repartidor.
     * Prioriza la abierta; si no hay, devuelve la última del día.
     */
    private function cajaDeHoy(int $repartidorId, bool $soloAbierta = false): ?CajaRepartidor
    {
        $q = CajaRepartidor::with('hojasRuta')
            ->where('id_repartidor', $repartidorId)
            ->where('fecha', now()->toDateString())
            ->orderByDesc('id_caja');

        if ($soloAbierta) {
            $q->where('estado_caja', 'abierta');
        }

        return $q->first();
    }

    /**
     * Mapea una caja a un array con totales calculados (idéntico a lo que espera la app).
     */
    private function mapearCaja(CajaRepartidor $caja): array
    {
        $totalGastos = (float) GastoOperativo::where('id_caja', $caja->id_caja)->sum('monto');
        $egresos = GastoOperativo::where('id_caja', $caja->id_caja)
            ->orderByDesc('id_gasto')
            ->get()
            ->map(fn($g) => [
                'id_gasto' => $g->id_gasto,
                'tipo_gasto' => $g->tipo_gasto,
                'descripcion' => $g->descripcion,
                'monto' => (float) $g->monto,
            ]);

        $cobradoEfectivo = (float) $caja->hojasRuta->sum('cobrado_efectivo');
        $cobradoQr = (float) $caja->hojasRuta->sum('cobrado_qr');
        $ingresos = (float) $caja->ingresos;
        $montoInicial = (float) $caja->monto_inicial;

        return [
            'id_caja' => $caja->id_caja,
            'fecha' => $caja->fecha->toDateString(),
            'estado_caja' => $caja->estado_caja,
            'monto_inicial' => $montoInicial,
            'ingresos' => $ingresos,
            'gastos' => $totalGastos,
            'cobrado_efectivo' => $cobradoEfectivo,
            'cobrado_qr' => $cobradoQr,
            'total_cobrado' => round($cobradoEfectivo + $cobradoQr, 2),
            'efectivo_esperado' => round($montoInicial + $cobradoEfectivo + $ingresos - $totalGastos, 2),
            'monto_final' => $caja->monto_final ? (float) $caja->monto_final : null,
            'egresos' => $egresos,
        ];
    }

    public function cajaActual(string $firebaseUid): JsonResponse
    {
        $repartidor = Repartidor::where('firebase_uid', $firebaseUid)->first();

        if (!$repartidor) {
            return $this->errorResponse('Repartidor no encontrado', 404);
        }

        $caja = $this->cajaDeHoy($repartidor->id_repartidor);

        if (!$caja) {
            return $this->jsonResponse([
                'caja' => null,
                'mensaje' => 'Sin caja para la fecha de hoy',
            ]);
        }

        return $this->jsonResponse([
            'caja' => $this->mapearCaja($caja),
        ]);
    }

    public function registrarGasto(Request $request, string $firebaseUid): JsonResponse
    {
        $validated = $request->validate([
            'tipo_gasto' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'monto' => 'required|numeric|min:0',
            'comprobante' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,webp,heic,heif|max:10240',
        ]);

        $repartidor = Repartidor::where('firebase_uid', $firebaseUid)->first();

        if (!$repartidor) {
            return $this->errorResponse('Repartidor no encontrado', 404);
        }

        $caja = $this->cajaDeHoy($repartidor->id_repartidor, true);

        if (!$caja) {
            return $this->errorResponse('No hay caja abierta para hoy', 422);
        }

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

        return $this->jsonResponse([
            'id_gasto' => $gasto->id_gasto,
            'tipo_gasto' => $gasto->tipo_gasto,
            'descripcion' => $gasto->descripcion,
            'monto' => $gasto->monto,
        ], 'Gasto registrado', 201);
    }

    public function cerrar(Request $request, string $firebaseUid): JsonResponse
    {
        $validated = $request->validate([
            'monto_final' => 'required|numeric|min:0',
        ]);

        $repartidor = Repartidor::where('firebase_uid', $firebaseUid)->first();

        if (!$repartidor) {
            return $this->errorResponse('Repartidor no encontrado', 404);
        }

        $caja = $this->cajaDeHoy($repartidor->id_repartidor, true);

        if (!$caja) {
            return $this->errorResponse('No hay caja abierta para hoy', 422);
        }

        DB::transaction(function () use ($caja, $validated) {
            $caja->update([
                'estado_caja' => 'cerrada',
                'monto_final' => $validated['monto_final'],
            ]);
        });

        $caja->refresh();
        $caja->load('hojasRuta');

        return $this->jsonResponse($this->mapearCaja($caja), 'Caja cerrada correctamente');
    }
}
