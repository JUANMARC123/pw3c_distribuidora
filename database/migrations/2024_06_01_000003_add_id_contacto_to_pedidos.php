<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_contacto')->nullable()->after('id_farmacia');

            $table->foreign('id_contacto')
                  ->references('id_contacto')
                  ->on('contactos_farmacia')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign(['id_contacto']);
            $table->dropColumn('id_contacto');
        });
    }
};
