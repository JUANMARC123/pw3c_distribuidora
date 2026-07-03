<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('control_ruta_paradas', function (Blueprint $table) {
            $table->time('hora_llegada')->nullable()->after('hora_estimada');
        });
    }

    public function down()
    {
        Schema::table('control_ruta_paradas', function (Blueprint $table) {
            $table->dropColumn('hora_llegada');
        });
    }
};
