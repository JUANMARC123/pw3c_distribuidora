<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::table('estados_pedido')->where('id_estado_pedido', 2)->update(['nombre_estado' => 'En preparacion']);
        DB::table('estados_pedido')->where('id_estado_pedido', 3)->update(['nombre_estado' => 'En ruta']);
        DB::table('estados_pedido')->where('id_estado_pedido', 4)->update(['nombre_estado' => 'Entregado']);
        DB::table('estados_pedido')->where('id_estado_pedido', 5)->update(['nombre_estado' => 'Cancelado']);
        DB::table('estados_pedido')->where('id_estado_pedido', 6)->delete();
    }

    public function down()
    {
        DB::table('estados_pedido')->where('id_estado_pedido', 5)->update(['nombre_estado' => 'Entregado']);
        DB::table('estados_pedido')->where('id_estado_pedido', 4)->update(['nombre_estado' => 'Despachado']);
        DB::table('estados_pedido')->where('id_estado_pedido', 3)->update(['nombre_estado' => 'En preparacion']);
        DB::table('estados_pedido')->where('id_estado_pedido', 2)->update(['nombre_estado' => 'Aprobado']);
        DB::table('estados_pedido')->insert(['nombre_estado' => 'Cancelado']);
    }
};
