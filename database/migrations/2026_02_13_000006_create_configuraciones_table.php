<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->boolean('modo_online')->default(true);
            $table->string('ip_servidor', 250)->nullable();
            $table->string('nombre_servidor', 250)->nullable();
            $table->timestamps();
        });

        Schema::create('configuraciones_app', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('plataforma', 50);
            $table->string('version', 20);
            $table->text('notas')->nullable();
            $table->timestamps();
        });

        Schema::create('log_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('empresa_id')->nullable()->constrained('empresas');
            $table->string('tipo', 100);
            $table->text('descripcion')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('log_sesiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users');
            $table->string('tipo', 50);
            $table->string('ip', 45)->nullable();
            $table->string('dispositivo', 250)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_sesiones');
        Schema::dropIfExists('log_movimientos');
        Schema::dropIfExists('configuraciones_app');
        Schema::dropIfExists('configuraciones');
    }
};
