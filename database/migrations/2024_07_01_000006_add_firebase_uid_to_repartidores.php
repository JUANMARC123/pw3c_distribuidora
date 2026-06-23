<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('repartidores', function (Blueprint $table) {
            if (!Schema::hasColumn('repartidores', 'firebase_uid')) {
                $table->string('firebase_uid', 255)->nullable()->unique()->after('id_estado_repartidor');
            }
        });
    }

    public function down()
    {
        Schema::table('repartidores', function (Blueprint $table) {
            if (Schema::hasColumn('repartidores', 'firebase_uid')) {
                $table->dropColumn('firebase_uid');
            }
        });
    }
};
