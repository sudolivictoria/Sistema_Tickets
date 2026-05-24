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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            $table->string('asunto');
            $table->text('descripcion');
            $table->text('comentario')->nullable();

            //---quien genera el ticket
            $table->foreignId('user_id')->constrained('users');

            //--relacion estado del ticket
            $table->foreignId('estado_id')->constrained('estados');

            //--relacion con categoria
            $table->foreignId('categoria_id')->constrained('categorias');

            //--relacion con tipo de solicitud
            $table->foreignId('tipo_solicitud_id')->constrained('tipo_solicitudes');

            //--relacion con prioridad
            $table->foreignId('prioridad_id')->constrained('prioridades');


            //--tecnico asignado
            $table->unsignedBigInteger('tecnico_id')->nullable();
            $table->foreign('tecnico_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
