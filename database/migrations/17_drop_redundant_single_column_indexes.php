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
        // Hallazgo A2 de auditoría: sobre-indexado.
        //
        // Cada índice que se elimina aquí solo es seguro de eliminar si OTRO
        // índice ya cubre esa misma columna como prefijo izquierdo (InnoDB
        // exige al menos un índice así por cada columna con FOREIGN KEY).
        // Verificado empíricamente contra el esquema real (SHOW INDEX), no
        // asumido:
        //
        // - estado_id: cubierto como prefijo izquierdo por 'idx_tickets_estado_tecnico'
        //   y 'idx_tickets_estado_prioridad' (migración 15) -> se puede soltar el
        //   índice suelto de la migración 11.
        // - tecnico_id: cubierto como prefijo izquierdo por 'idx_tickets_tecnico_estado'
        //   (migración 15) -> se puede soltar el índice suelto de la migración 11.
        // - created_at: no es columna FK. Cubierta como prefijo izquierdo por
        //   'idx_tickets_created_estado' (migración 15) -> se puede soltar.
        // - prioridad_id: NO tiene ningún compuesto donde sea prefijo izquierdo.
        //   'tickets_prioridad_id_index' es el ÚNICO índice sobre esa columna y es
        //   requerido por el FOREIGN KEY -> se conserva, NO se elimina.
        // - categorias.unidad_id: solo existen 2 índices idénticos de una sola
        //   columna (uno de la migración 11, otro de la 15), sin ningún compuesto
        //   alternativo -> se elimina solo uno de los dos, dejando siempre al
        //   menos un índice que satisfaga el FOREIGN KEY.
        // ==========================================
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['estado_id']);
            $table->dropIndex(['tecnico_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('categorias', function (Blueprint $table) {
            $table->dropIndex(['unidad_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->index('estado_id');
            $table->index('tecnico_id');
            $table->index('created_at');
        });

        Schema::table('categorias', function (Blueprint $table) {
            $table->index('unidad_id');
        });
    }
};
