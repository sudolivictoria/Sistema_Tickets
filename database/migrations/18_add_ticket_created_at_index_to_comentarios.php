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
        // Hallazgo B2: obtenerComentarios() siempre filtra por ticket_id y ordena
        // por created_at (->oldest()); el índice implícito de la FK solo cubre
        // ticket_id, no el orden. Este compuesto sirve exactamente ese patrón.
        Schema::table('comentarios', function (Blueprint $table) {
            $table->index(['ticket_id', 'created_at'], 'idx_comentarios_ticket_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comentarios', function (Blueprint $table) {
            $table->dropIndex('idx_comentarios_ticket_created');
        });
    }
};
