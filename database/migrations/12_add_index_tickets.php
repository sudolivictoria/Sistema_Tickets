<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->index('estado_id');
            $table->index('prioridad_id');
            $table->index('tecnico_id');
            $table->index('created_at'); 
        });

        Schema::table('categorias', function (Blueprint $table) {
            $table->index('unidad_id'); 
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['estado_id', 'prioridad_id', 'tecnico_id', 'created_at']);
        });
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropIndex(['unidad_id']);
        });
    }
};
