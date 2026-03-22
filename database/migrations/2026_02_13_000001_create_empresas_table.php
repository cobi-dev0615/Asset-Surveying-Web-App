<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 256);
            $table->binary('logo')->nullable();
            $table->foreignId('usuario_id')->constrained('users');
            $table->boolean('eliminado')->default(false);
            $table->timestamps();
        });

        Schema::create('sucursales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('codigo', 50)->index();
            $table->string('nombre', 150);
            $table->string('ciudad', 150)->nullable();
            $table->string('direccion', 500)->nullable();
            $table->boolean('eliminado')->default(false);
            $table->timestamps();

            $table->unique(['empresa_id', 'codigo']);
        });

        Schema::create('empresa_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->unique(['empresa_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_user');
        Schema::dropIfExists('sucursales');
        Schema::dropIfExists('empresas');
    }
};
