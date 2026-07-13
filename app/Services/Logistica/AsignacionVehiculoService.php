<?php

namespace App\Services\Logistica;

use App\Models\AsignacionVehiculoRepartidor;
use Illuminate\Support\Facades\DB;

class AsignacionVehiculoService
{
    public function asignar(int $idRepartidor, int $idVehiculo): AsignacionVehiculoRepartidor
    {
        return DB::transaction(function () use ($idRepartidor, $idVehiculo) {
            AsignacionVehiculoRepartidor::where('id_repartidor', $idRepartidor)
                ->whereNull('fecha_devolucion')
                ->update(['fecha_devolucion' => now()]);

            return AsignacionVehiculoRepartidor::create([
                'id_repartidor' => $idRepartidor,
                'id_vehiculo' => $idVehiculo,
                'fecha_asignacion' => now(),
            ]);
        });
    }

    public function liberar(int $idRepartidor): void
    {
        DB::transaction(function () use ($idRepartidor) {
            AsignacionVehiculoRepartidor::where('id_repartidor', $idRepartidor)
                ->whereNull('fecha_devolucion')
                ->update(['fecha_devolucion' => now()]);
        });
    }
}
