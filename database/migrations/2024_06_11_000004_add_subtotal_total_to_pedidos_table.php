<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->default(0)->after('observaciones');
            $table->decimal('descuento', 10, 2)->default(0)->after('subtotal');
            $table->decimal('total', 10, 2)->default(0)->after('descuento');
        });
    }

    public function down()
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'descuento', 'total']);
        });
    }
};
