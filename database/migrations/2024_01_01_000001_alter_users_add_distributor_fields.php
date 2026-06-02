<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nombre', 100)->nullable()->after('name');
            $table->string('apellido', 100)->nullable()->after('nombre');
            $table->string('telefono', 30)->nullable()->after('email');
            $table->boolean('estado')->default(true)->after('telefono');
            $table->dateTime('ultimo_acceso')->nullable()->after('estado');
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['nombre', 'apellido', 'telefono', 'estado', 'ultimo_acceso']);
        });
    }
};
