<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE farmacias MODIFY latitud DECIMAL(10, 7) NULL');
        DB::statement('ALTER TABLE farmacias MODIFY longitud DECIMAL(10, 7) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE farmacias MODIFY latitud DECIMAL(10, 7) NOT NULL');
        DB::statement('ALTER TABLE farmacias MODIFY longitud DECIMAL(10, 7) NOT NULL');
    }
};
