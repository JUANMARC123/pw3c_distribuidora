<?php

namespace App\Services;

use App\Models\HojaRuta;
use App\Models\Repartidor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseSyncService
{
    protected string $databaseUrl;

    public function __construct()
    {
        $this->databaseUrl = config('firebase.database_url');
    }

    public function iniciarViaje(HojaRuta $hojaRuta): void
    {
        try {
            $detalles = $hojaRuta->detalles()->with('pedido.farmacia')->get();
            $farmacias = $detalles->map(function ($detalle) {
                $pedido = $detalle->pedido;
                return [
                    'id_pedido' => (string) $pedido->id_pedido,
                    'id_detalle_hoja_ruta' => (string) $detalle->id_detalle,
                    'orden_visita' => $detalle->orden_visita,
                    'nombre_farmacia' => $pedido->farmacia->nombre,
                    'direccion' => $pedido->farmacia->direccion,
                    'latitud' => (float) $pedido->farmacia->latitud,
                    'longitud' => (float) $pedido->farmacia->longitud,
                    'radio_geocerca' => (float) ($pedido->farmacia->geocerca ?? 20),
                    'estado' => 'pendiente',
                ];
            })->sortBy('orden_visita')->values()->toArray();

            $repartidor = Repartidor::find($hojaRuta->id_repartidor);
            $firebaseUid = $repartidor?->firebase_uid;

            if (!$firebaseUid) {
                Log::warning('Repartidor sin firebase_uid', [
                    'id_repartidor' => $hojaRuta->id_repartidor
                ]);
                return;
            }

            $payload = [
                'id_hoja_ruta' => (string) $hojaRuta->id_hoja_ruta,
                'estado' => 'en_camino',
                'fecha_inicio' => $hojaRuta->fecha . ' ' . $hojaRuta->hora_salida,
                'farmacias' => $farmacias,
            ];

            $response = Http::timeout(10)->put(
                "{$this->databaseUrl}/viajes_activos/{$firebaseUid}.json",
                $payload
            );

            if ($response->failed()) {
                Log::error('Error al escribir en Firebase RTDB', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Excepción en FirebaseSyncService::iniciarViaje', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function limpiarViaje(HojaRuta $hojaRuta): void
    {
        $this->limpiarViajePorRepartidor($hojaRuta->id_repartidor);
    }

    public function limpiarViajePorRepartidor(int $idRepartidor): void
    {
        try {
            $repartidor = Repartidor::find($idRepartidor);
            $firebaseUid = $repartidor?->firebase_uid;

            if (!$firebaseUid) {
                return;
            }

            $response = Http::timeout(10)->delete(
                "{$this->databaseUrl}/viajes_activos/{$firebaseUid}.json"
            );

            if ($response->failed()) {
                Log::error('Error al limpiar nodo en Firebase RTDB', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Excepción en FirebaseSyncService::limpiarViajePorRepartidor', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function actualizarEstadoParada(string $firebaseUid, int $idDetalleHojaRuta, string $estado): void
    {
        try {
            // Get current viaje data to find the farmacia index
            $response = Http::timeout(10)->get(
                "{$this->databaseUrl}/viajes_activos/{$firebaseUid}.json"
            );

            if ($response->failed() || !$response->json()) {
                return;
            }

            $viaje = $response->json();
            $farmacias = $viaje['farmacias'] ?? [];

            foreach ($farmacias as $index => $farmacia) {
                if (($farmacia['id_detalle_hoja_ruta'] ?? '') === (string) $idDetalleHojaRuta) {
                    Http::timeout(10)->patch(
                        "{$this->databaseUrl}/viajes_activos/{$firebaseUid}/farmacias/{$index}.json",
                        ['estado' => $estado]
                    );
                    break;
                }
            }
        } catch (\Exception $e) {
            Log::error('Excepción en FirebaseSyncService::actualizarEstadoParada', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
