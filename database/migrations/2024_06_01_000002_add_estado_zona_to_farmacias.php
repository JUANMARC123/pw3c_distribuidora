<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $hasEstado = Schema::hasColumn('farmacias', 'id_estado_farmacia');
        if (!$hasEstado) {
            Schema::table('farmacias', function (Blueprint $table) {
                $table->tinyInteger('id_estado_farmacia')->unsigned()->default(1)->after('email');
                $table->string('zona', 100)->nullable()->after('id_estado_farmacia');

                $table->foreign('id_estado_farmacia')
                      ->references('id_estado_farmacia')
                      ->on('estados_farmacia')
                      ->onDelete('restrict')
                      ->onUpdate('cascade');
            });
        }
    }

    public function down()
    {
        Schema::table('farmacias', function (Blueprint $table) {
            $table->dropForeign(['id_estado_farmacia']);
            $table->dropColumn(['id_estado_farmacia', 'zona']);
        });
    }
};
