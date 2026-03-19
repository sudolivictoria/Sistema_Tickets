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
        Schema::create('manuales', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('archivo_path'); // Ruta del PDF
            $table->foreignId('categoria_id')->constrained('categoria_manuales')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
