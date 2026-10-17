<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropOldTables extends Migration
{
    public function up()
    {
        $tables = [
            'gastos_operativos',
            'caja_repartidor',
            'incidencias',
            'evidencias_entrega',
            'despachos',
            'historial_estado_despacho',
            'historial_estado_ruta',
            'historial_estado_vehiculo',
            'historial_estado_repartidor',
            'historial_estado_pedido',
            'planificacion_rutas',
            'control_ruta_paradas',
            'control_rutas',
            'ruta_paradas',
            'rutas',
            'asignaciones_vehiculo_repartidor',
            'detalle_pedido',
            'pedidos',
            'contactos_farmacia',
            'farmacias',
            'productos',
            'vehiculos',
            'modelos',
            'marcas',
            'capacidades',
            'repartidores',
            'usuarios',
            'sesiones_usuario',
            'auditorias',
            'usuario_roles',
            'rol_permiso',
            'permisos',
            'modulos',
            'acciones',
            'tablas_sistema',
            'estados_caja',
            'estados_ruta',
            'estados_detalle',
            'estados_despacho',
            'estados_vehiculo',
            'estados_repartidor',
            'estados_pedido',
            'estados_usuario',
            'licencias',
            'extensiones_ci',
            'cargos',
            'tipos_evidencia',
            'tipos_incidencia',
            'password_resets',
            'failed_jobs',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
            }
        }
    }

    public function down()
    {
    }
}
