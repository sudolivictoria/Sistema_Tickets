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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo');

            //---->password sistema
            $table->string('password');

            //---->Gmail 
            $table->string('email')->unique();
            $table->text('password_gmail')->nullable();

            //--->365 
            $table->string('email_365')->nullable();
            $table->text('password_365')->nullable();

            //---->estado del usuario
            $table->boolean('activo')->default(true);

            $table->string('cargo')->nullable();

            //----Relaciones para los Filtros (Unidad y Rol)
            $table->foreignId('rol_id')->nullable()->constrained('rol');
            $table->foreignId('unidad_id')->nullable()->constrained('unidad');

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
