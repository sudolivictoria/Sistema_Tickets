<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //----usamos SQL directo porque modificar un tipo de columna existente requiere doctrine/dbal,
        //----que no está instalado; esto evita agregar una dependencia nueva en producción
        DB::statement('ALTER TABLE tipo_solicitudes MODIFY descripcion_solicitud TEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE tipo_solicitudes MODIFY descripcion_solicitud VARCHAR(255) NULL');
    }
};
