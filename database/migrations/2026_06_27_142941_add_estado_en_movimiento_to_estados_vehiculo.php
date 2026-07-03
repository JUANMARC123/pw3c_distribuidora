<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (!DB::table('estados_vehiculo')->where('nombre_estado', 'En movimiento')->exists()) {
            DB::table('estados_vehiculo')->insert(['nombre_estado' => 'En movimiento']);
        }
    }

    public function down()
    {
        DB::table('estados_vehiculo')->where('nombre_estado', 'En movimiento')->delete();
    }
};
