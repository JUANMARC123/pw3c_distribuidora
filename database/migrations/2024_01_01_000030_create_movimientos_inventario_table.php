<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_id')->constrained('inventarios')->onDelete('cascade');
            $table->foreignId('despacho_id')->nullable()->constrained('despachos')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->string('tipo_movimiento', 30);
            $table->integer('cantidad');
            $table->dateTime('fecha');
            $table->timestamps();

            $table->index(['inventario_id', 'fecha']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
