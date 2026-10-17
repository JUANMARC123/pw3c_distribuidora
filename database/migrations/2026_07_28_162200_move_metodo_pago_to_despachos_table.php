<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn('metodo_pago');
        });

        Schema::table('despachos', function (Blueprint $table) {
            $table->enum('metodo_pago', ['efectivo', 'qr', 'transferencia', 'credito'])->nullable()->after('monto_recibido');
        });
    }

    public function down()
    {
        Schema::table('despachos', function (Blueprint $table) {
            $table->dropColumn('metodo_pago');
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->enum('metodo_pago', ['efectivo', 'qr', 'transferencia', 'credito'])->nullable()->after('total');
        });
    }
};
