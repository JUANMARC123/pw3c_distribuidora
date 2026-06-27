<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repartidores', function (Blueprint $table) {
            if (!Schema::hasColumn('repartidores', 'firebase_uid')) {
                $table->string('firebase_uid', 191)->nullable()->unique()->after('id_estado_repartidor');
            }
        });

        Schema::table('repartidores', function (Blueprint $table) {
            $table->unique('id_usuario', 'repartidores_id_usuario_unique');
        });
    }

    public function down(): void
    {
        Schema::table('repartidores', function (Blueprint $table) {
            $table->dropUnique('repartidores_id_usuario_unique');
        });

        Schema::table('repartidores', function (Blueprint $table) {
            if (Schema::hasColumn('repartidores', 'firebase_uid')) {
                $table->dropUnique(['firebase_uid']);
                $table->dropColumn('firebase_uid');
            }
        });
    }
};
