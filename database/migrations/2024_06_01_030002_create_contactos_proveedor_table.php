<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contactos_proveedor', function (Blueprint $table) {
            $table->id('id_contacto_proveedor');
            $table->unsignedBigInteger('id_proveedor');
            $table->string('nombre_contacto', 150);
            $table->string('cargo', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 180)->nullable();

            $table->foreign('id_proveedor')
                  ->references('id_proveedor')
                  ->on('proveedores')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contactos_proveedor');
    }
};
