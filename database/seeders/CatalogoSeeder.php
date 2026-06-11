<?php

namespace Database\Seeders;

use App\Models\Seguridad\EstadoUsuario;
use App\Models\Seguridad\Rol;
use App\Models\Seguridad\Modulo;
use App\Models\Seguridad\Accion;
use App\Models\Seguridad\TablaSistema;
use App\Models\Seguridad\Permiso;
use App\Models\Pedido\EstadoPedido;
use App\Models\Repartidor\EstadoRepartidor;
use App\Models\Repartidor\ExtensionCI;
use App\Models\Repartidor\Licencia;
use App\Models\Vehiculo\EstadoVehiculo;
use App\Models\Vehiculo\Marca;
use App\Models\Vehiculo\Modelo;
use App\Models\Vehiculo\Capacidad;
use App\Models\Despacho\EstadoDespacho;
use App\Models\Evidencia\TipoIncidencia;
use App\Models\Evidencia\TipoEvidencia;
use App\Models\Farmacia\Cargo;
use Illuminate\Database\Seeder;

class CatalogoSeeder extends Seeder
{
    public function run()
    {
        EstadoUsuario::insert([
            ['nombre_estado' => 'Activo'],
            ['nombre_estado' => 'Bloqueado'],
            ['nombre_estado' => 'Suspendido'],
        ]);

        Rol::insert([
            ['nombre' => 'Administrador'],
            ['nombre' => 'Supervisor'],
            ['nombre' => 'Operador'],
            ['nombre' => 'Repartidor'],
        ]);

        Modulo::insert([
            ['nombre' => 'Seguridad'],
            ['nombre' => 'Farmacias'],
            ['nombre' => 'Pedidos'],
            ['nombre' => 'Repartidores'],
            ['nombre' => 'Vehiculos'],
            ['nombre' => 'Logistica'],
            ['nombre' => 'Despachos'],
            ['nombre' => 'Incidencias'],
            ['nombre' => 'Reportes'],
        ]);

        Accion::insert([
            ['nombre' => 'Crear'],
            ['nombre' => 'Leer'],
            ['nombre' => 'Actualizar'],
            ['nombre' => 'Eliminar'],
            ['nombre' => 'Exportar'],
            ['nombre' => 'Importar'],
        ]);

        TablaSistema::insert([
            ['nombre' => 'usuarios'],
            ['nombre' => 'farmacias'],
            ['nombre' => 'pedidos'],
            ['nombre' => 'repartidores'],
            ['nombre' => 'vehiculos'],
            ['nombre' => 'rutas'],
            ['nombre' => 'despachos'],
            ['nombre' => 'incidencias'],
        ]);

        EstadoPedido::insert([
            ['nombre_estado' => 'Pendiente'],
            ['nombre_estado' => 'Aprobado'],
            ['nombre_estado' => 'En preparacion'],
            ['nombre_estado' => 'Despachado'],
            ['nombre_estado' => 'Entregado'],
            ['nombre_estado' => 'Cancelado'],
        ]);

        EstadoRepartidor::insert([
            ['nombre_estado' => 'Disponible'],
            ['nombre_estado' => 'En ruta'],
            ['nombre_estado' => 'Inactivo'],
        ]);

        EstadoVehiculo::insert([
            ['nombre_estado' => 'Operativo'],
            ['nombre_estado' => 'En mantenimiento'],
            ['nombre_estado' => 'Fuera de servicio'],
        ]);

        EstadoDespacho::insert([
            ['nombre_estado' => 'Pendiente'],
            ['nombre_estado' => 'En camino'],
            ['nombre_estado' => 'Entregado'],
            ['nombre_estado' => 'Fallido'],
        ]);

        Licencia::insert([
            ['categoria' => 'A'],
            ['categoria' => 'B'],
            ['categoria' => 'C'],
            ['categoria' => 'P'],
            ['categoria' => 'Profesional'],
        ]);

        ExtensionCI::insert([
            ['nombre_extension' => 'LP'],
            ['nombre_extension' => 'CBBA'],
            ['nombre_extension' => 'SC'],
            ['nombre_extension' => 'OR'],
            ['nombre_extension' => 'PT'],
            ['nombre_extension' => 'TJ'],
            ['nombre_extension' => 'CH'],
            ['nombre_extension' => 'BE'],
            ['nombre_extension' => 'PD'],
        ]);

        Marca::insert([
            ['nombre_marca' => 'Toyota'],
            ['nombre_marca' => 'Suzuki'],
            ['nombre_marca' => 'Nissan'],
            ['nombre_marca' => 'Mitsubishi'],
            ['nombre_marca' => 'Kia'],
        ]);

        Modelo::insert([
            ['id_marca' => 1, 'nombre_modelo' => 'Hilux'],
            ['id_marca' => 1, 'nombre_modelo' => 'Land Cruiser'],
            ['id_marca' => 2, 'nombre_modelo' => 'Vitara'],
            ['id_marca' => 2, 'nombre_modelo' => 'Carry'],
            ['id_marca' => 3, 'nombre_modelo' => 'Frontier'],
            ['id_marca' => 3, 'nombre_modelo' => 'NP300'],
            ['id_marca' => 4, 'nombre_modelo' => 'L200'],
            ['id_marca' => 4, 'nombre_modelo' => 'Montero Sport'],
            ['id_marca' => 5, 'nombre_modelo' => 'K2500'],
            ['id_marca' => 5, 'nombre_modelo' => 'Bongo'],
        ]);

        Capacidad::insert([
            ['capacidad_kg' => 500],
            ['capacidad_kg' => 1000],
            ['capacidad_kg' => 2000],
            ['capacidad_kg' => 3000],
            ['capacidad_kg' => 5000],
        ]);

        TipoIncidencia::insert([
            ['nombre_tipo' => 'Retraso en la entrega'],
            ['nombre_tipo' => 'Producto dañado'],
            ['nombre_tipo' => 'Direccion incorrecta'],
            ['nombre_tipo' => 'Cliente ausente'],
            ['nombre_tipo' => 'Fallo mecanico'],
            ['nombre_tipo' => 'Otro'],
        ]);

        TipoEvidencia::insert([
            ['nombre_tipo' => 'Foto de entrega'],
            ['nombre_tipo' => 'Firma digital'],
            ['nombre_tipo' => 'Documento adjunto'],
        ]);

        Cargo::insert([
            ['nombre_cargo' => 'Gerente'],
            ['nombre_cargo' => 'Administrador'],
            ['nombre_cargo' => 'Farmaceutico'],
            ['nombre_cargo' => 'Recepcionista'],
            ['nombre_cargo' => 'Almacenero'],
        ]);

        $modulos = Modulo::all();
        $acciones = Accion::all();
        $permisosData = [];
        foreach ($modulos as $modulo) {
            foreach ($acciones as $accion) {
                $permisosData[] = [
                    'id_modulo' => $modulo->id_modulo,
                    'id_accion' => $accion->id_accion,
                ];
            }
        }
        Permiso::insert($permisosData);
    }
}
