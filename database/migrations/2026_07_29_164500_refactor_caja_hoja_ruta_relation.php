<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('hojas_ruta', function (Blueprint $table) {
            $table->unsignedBigInteger('id_caja')->nullable()->after('id_vehiculo');
            $table->decimal('cobrado_efectivo', 10, 2)->default(0)->after('id_caja');
            $table->decimal('cobrado_qr', 10, 2)->default(0)->after('cobrado_efectivo');

            $table->foreign('id_caja')
                ->references('id_caja')->on('caja_repartidor')
                ->onDelete('set null');
        });

        $cajas = DB::table('caja_repartidor')->whereNotNull('id_hoja_ruta')->get(['id_caja', 'id_hoja_ruta']);
        foreach ($cajas as $caja) {
            DB::table('hojas_ruta')->where('id_hoja_ruta', $caja->id_hoja_ruta)->update(['id_caja' => $caja->id_caja]);
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'sqlite') {
            Schema::create('caja_repartidor_new', function (Blueprint $table) {
                $table->bigIncrements('id_caja');
                $table->unsignedBigInteger('id_repartidor');
                $table->date('fecha');
                $table->decimal('ingresos', 10, 2)->default(0);
                $table->decimal('monto_final', 10, 2)->nullable();
                $table->decimal('monto_inicial', 10, 2)->default(0);
                $table->string('estado_caja', 20)->default('abierta');
                $table->timestamps();
                $table->foreign('id_repartidor')
                    ->references('id_repartidor')->on('repartidores')
                    ->onDelete('cascade');
            });
            DB::statement('INSERT INTO caja_repartidor_new SELECT id_caja, id_repartidor, fecha, ingresos, monto_final, monto_inicial, estado_caja, created_at, updated_at FROM caja_repartidor');
            Schema::drop('caja_repartidor');
            Schema::rename('caja_repartidor_new', 'caja_repartidor');
        } else {
            Schema::table('caja_repartidor', function (Blueprint $table) {
                $table->dropForeign(['id_hoja_ruta']);
                $table->dropColumn('id_hoja_ruta');
            });
        }
    }

    public function down()
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'sqlite') {
            Schema::table('caja_repartidor', function (Blueprint $table) {
                $table->unsignedBigInteger('id_hoja_ruta')->nullable()->unique()->after('id_caja');
                $table->foreign('id_hoja_ruta')
                    ->references('id_hoja_ruta')->on('hojas_ruta')
                    ->onDelete('cascade');
            });

            $cajas = DB::table('hojas_ruta')->whereNotNull('id_caja')->get(['id_hoja_ruta', 'id_caja']);
            foreach ($cajas as $hr) {
                DB::table('caja_repartidor')->where('id_caja', $hr->id_caja)->update(['id_hoja_ruta' => $hr->id_hoja_ruta]);
            }
        }

        if ($driver === 'sqlite') {
            Schema::table('hojas_ruta', function (Blueprint $table) {
                $table->dropColumn(['cobrado_efectivo', 'cobrado_qr', 'id_caja']);
            });
        } else {
            Schema::table('hojas_ruta', function (Blueprint $table) {
                $table->dropForeign(['id_caja']);
                $table->dropColumn(['id_caja', 'cobrado_efectivo', 'cobrado_qr']);
            });
        }
    }
};
