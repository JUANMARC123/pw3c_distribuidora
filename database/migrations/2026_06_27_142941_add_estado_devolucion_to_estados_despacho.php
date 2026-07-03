<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::table('estados_despacho')->where('id_estado_despacho', 3)->update(['nombre_estado' => 'Entregado completo']);
        if (!DB::table('estados_despacho')->where('nombre_estado', 'Devolucion')->exists()) {
            DB::table('estados_despacho')->insert(['nombre_estado' => 'Devolucion']);
        }
    }

    public function down()
    {
        DB::table('estados_despacho')->where('nombre_estado', 'Devolucion')->delete();
        DB::table('estados_despacho')->where('id_estado_despacho', 3)->update(['nombre_estado' => 'Entregado']);
    }
};
