<?php

namespace App\Http\Controllers\Catalogo;

use App\Http\Controllers\ApiController;
use App\Models\Pedido;
use App\Models\Repartidor;
use App\Models\Usuario;
use App\Models\Vehiculo;
use Illuminate\Http\JsonResponse;

/**
 * Catálogos derivados de la base de datos.
 *
 * Las tablas `estados_*`, `marcas`, `modelos`, `licencias`, `cargos`, etc. fueron
 * eliminadas en 2026_07_10_150000_drop_old_tables.php. Los estados pasaron a ser
 * ENUMs de texto sobre cada tabla. El front-end sigue esperando objetos con ids
 * numéricos, así que este controlador es el puente: genera ids estables desde las
 * constantes de cada modelo y expone listas distintas para marca/modelo.
 */
class CatalogoController extends ApiController
{
    /**
     * GET /api/catalogos/estados-pedido
     */
    public function estadosPedido(): JsonResponse
    {
        return $this->jsonResponse(Pedido::estadosCatalogo());
    }

    /**
     * GET /api/catalogos/estados-repartidor
     */
    public function estadosRepartidor(): JsonResponse
    {
        return $this->jsonResponse(Repartidor::estadosCatalogo());
    }

    /**
     * GET /api/catalogos/licencias
     */
    public function licencias(): JsonResponse
    {
        return $this->jsonResponse(Repartidor::licenciasCatalogo());
    }

    /**
     * GET /api/catalogos/estados-vehiculo
     */
    public function estadosVehiculo(): JsonResponse
    {
        return $this->jsonResponse(Vehiculo::estadosCatalogo());
    }

    /**
     * GET /api/catalogos/estados-usuario
     */
    public function estadosUsuario(): JsonResponse
    {
        return $this->jsonResponse(Usuario::estadosCatalogo());
    }

    /**
     * GET /api/catalogos/marcas
     * Marcas distintas realmente presentes en el parque automotor.
     */
    public function marcas(): JsonResponse
    {
        $marcas = Vehiculo::query()
            ->whereNotNull('marca')
            ->where('marca', '<>', '')
            ->distinct()
            ->orderBy('marca')
            ->pluck('marca')
            ->values();

        $catalogo = $marcas->map(fn ($marca, $i) => [
            'id_marca'     => $i + 1,
            'nombre_marca' => $marca,
        ])->all();

        return $this->jsonResponse($catalogo);
    }

    /**
     * GET /api/catalogos/modelos
     */
    public function modelos(): JsonResponse
    {
        $modelos = Vehiculo::query()
            ->whereNotNull('modelo')
            ->where('modelo', '<>', '')
            ->distinct()
            ->orderBy('modelo')
            ->pluck('modelo')
            ->values();

        $catalogo = $modelos->map(fn ($modelo, $i) => [
            'id_modelo'    => $i + 1,
            'nombre_modelo' => $modelo,
        ])->all();

        return $this->jsonResponse($catalogo);
    }

    /**
     * GET /api/catalogos/cargos
     * Lista fija de cargos para contactos de farmacia (`contactos_farmacia.cargo`
     * es texto libre; no existe una tabla de cargos).
     */
    public function cargos(): JsonResponse
    {
        $cargos = [
            'Gerente',
            'Encargado',
            'Farmacéutico',
            'Administrador',
            'Recepcionista',
            'Regente',
            'Auxiliar',
        ];

        $catalogo = [];
        foreach ($cargos as $i => $nombre) {
            $catalogo[] = [
                'id_cargo'    => $i + 1,
                'nombre_cargo' => $nombre,
            ];
        }

        return $this->jsonResponse($catalogo);
    }
}
