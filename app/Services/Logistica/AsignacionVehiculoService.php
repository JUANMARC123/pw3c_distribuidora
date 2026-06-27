<?php

namespace App\Services\Logistica;

use App\Models\Logistica\AsignacionVehiculoRepartidor;
use Illuminate\Support\Facades\DB;

class AsignacionVehiculoService
{
    public function asignar(int $idRepartidor, int $idVehiculo, ?string $observaciones = null): AsignacionVehiculoRepartidor
    {
        return DB::transaction(function () use ($idRepartidor, $idVehiculo, $observaciones) {
            AsignacionVehiculoRepartidor::where('id_repartidor', $idRepartidor)
                ->where('activo', true)
                ->whereNull('fecha_fin')
                ->lockForUpdate()
                ->update([
                    'activo' => false,
                    'fecha_fin' => now(),
                ]);

            AsignacionVehiculoRepartidor::where('id_vehiculo', $idVehiculo)
                ->where('activo', true)
                ->whereNull('fecha_fin')
                ->lockForUpdate()
                ->update([
                    'activo' => false,
                    'fecha_fin' => now(),
                ]);

            return AsignacionVehiculoRepartidor::create([
                'id_repartidor' => $idRepartidor,
                'id_vehiculo' => $idVehiculo,
                'fecha_inicio' => now(),
                'activo' => true,
                'observaciones' => $observaciones,
            ]);
        });
    }

    public function liberar(int $idRepartidor): void
    {
        DB::transaction(function () use ($idRepartidor) {
            AsignacionVehiculoRepartidor::where('id_repartidor', $idRepartidor)
                ->where('activo', true)
                ->whereNull('fecha_fin')
                ->lockForUpdate()
                ->update([
                    'activo' => false,
                    'fecha_fin' => now(),
                ]);
        });
    }
}
