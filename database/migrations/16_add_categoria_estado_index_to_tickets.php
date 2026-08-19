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
        //-----Los reportes filtran por categoria + estado combinados (ReporteController, GestorReporteController),
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
