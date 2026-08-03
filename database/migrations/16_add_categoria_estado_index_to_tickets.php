<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==========================================
        // Hallazgo A1 de auditoría: 'categoria_id' solo tenía el índice
        // implícito de la FK (single-column). Los reportes filtran por
        // categoria + estado combinados (ReporteController, GestorReporteController),
        // así que agregamos un compuesto para cubrir ese patrón de filtro.
        // ==========================================
        Schema::table('tickets', function (Blueprint $table) {
            $table->index(['categoria_id', 'estado_id'], 'idx_tickets_categoria_estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('idx_tickets_categoria_estado');
        });
    }
};
