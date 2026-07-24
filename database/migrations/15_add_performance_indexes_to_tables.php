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
        // 1. ÍNDICES PARA LA TABLA 'tickets'
        // ==========================================
        Schema::table('tickets', function (Blueprint $table) {
            //----Acelera la búsqueda de tickets por cliente (ClienteController, misTickets, ApiTableController)
            $table->index(['user_id', 'created_at'], 'idx_tickets_user_created');

            //------Acelera 'misAsignados' y actualización de técnicos ($query->where('tecnico_id', ...)->where('estado_id', ...))
            $table->index(['tecnico_id', 'estado_id'], 'idx_tickets_tecnico_estado');

            //------Acelera contadores de Dashboard (Abiertos / Pendientes / Resueltos)
            $table->index(['estado_id', 'tecnico_id'], 'idx_tickets_estado_tecnico');

            //------Acelera el filtrado y métricas por mes/año ($query->whereYear('created_at', ...)->whereMonth(...))
            $table->index(['created_at', 'estado_id'], 'idx_tickets_created_estado');

            //-----Acelera la categorización de prioridad en Dashboard y ApiTableController
            $table->index(['estado_id', 'prioridad_id'], 'idx_tickets_estado_prioridad');

            //------Acelera métricas del Historial ($ticket->fecha_cierre)
            $table->index(['fecha_cierre', 'estado_id'], 'idx_tickets_cierre_estado');
        });

        // ==========================================
        // 2. ÍNDICES PARA LA TABLA 'categorias'
        // ==========================================
        Schema::table('categorias', function (Blueprint $table) {
            //----Acelera búsquedas de categoria->unidad_id usadas en whereHas('categoria', ...)
            $table->index(['id', 'unidad_id'], 'idx_categorias_id_unidad');
        });

        // ==========================================
        // 3. ÍNDICES PARA LA TABLA 'users'
        // ==========================================
        Schema::table('users', function (Blueprint $table) {
            //---Acelera la selección de técnicos activos por unidad (User::where('unidad_id', ...)->where('activo', true))
            $table->index(['unidad_id', 'activo'], 'idx_users_unidad_activo');
        });

        // ==========================================
        // 4. ÍNDICES PARA LA TABLA 'prioridad_unidad'
        // ==========================================
        Schema::table('prioridad_unidad', function (Blueprint $table) {
            //-----Acelera la consulta del helper calcularFechaVencimientoSla()
            $table->index(['unidad_id', 'prioridad_id'], 'idx_prioridad_unidad_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('idx_tickets_user_created');
            $table->dropIndex('idx_tickets_tecnico_estado');
            $table->dropIndex('idx_tickets_estado_tecnico');
            $table->dropIndex('idx_tickets_created_estado');
            $table->dropIndex('idx_tickets_estado_prioridad');
            $table->dropIndex('idx_tickets_cierre_estado');
        });

        Schema::table('categorias', function (Blueprint $table) {
            $table->dropIndex('idx_categorias_id_unidad');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_unidad_activo');
        });

        Schema::table('prioridad_unidad', function (Blueprint $table) {
            $table->dropIndex('idx_prioridad_unidad_lookup');
        });
    }
};
