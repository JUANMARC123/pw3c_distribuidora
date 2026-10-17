<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        DB::table('pedidos')
            ->whereIn('estado', ['aprobado', 'preparacion'])
            ->update(['estado' => 'pendiente']);

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE pedidos MODIFY COLUMN estado ENUM('pendiente','en_ruta','entregado','cancelado') NOT NULL DEFAULT 'pendiente'");
        }
    }

    public function down()
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE pedidos MODIFY COLUMN estado ENUM('pendiente','aprobado','preparacion','en_ruta','entregado','cancelado') NOT NULL DEFAULT 'pendiente'");
        }
    }
};
