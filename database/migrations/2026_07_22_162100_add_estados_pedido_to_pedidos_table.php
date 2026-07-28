<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE pedidos MODIFY COLUMN estado ENUM('pendiente','aprobado','preparacion','en_ruta','entregado','cancelado') NOT NULL DEFAULT 'pendiente'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE pedidos MODIFY COLUMN estado ENUM('pendiente','aprobado','entregado','cancelado') NOT NULL DEFAULT 'pendiente'");
    }
};
