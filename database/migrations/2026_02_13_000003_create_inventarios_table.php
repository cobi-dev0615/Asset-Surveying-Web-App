<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventarios_status', function (Blueprint $table) {
            $table->id();
            $table->string('status', 50);
        });

        Schema::create('inventarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->string('nombre', 150);
            $table->foreignId('usuario_id')->constrained('users');
            $table->string('nombre_usuario', 150)->nullable();
            $table->string('auditor', 150)->nullable();
            $table->binary('firma_auditor')->nullable();
            $table->string('gerente', 150)->nullable();
            $table->binary('firma_gerente')->nullable();
            $table->string('subgerente', 150)->nullable();
            $table->binary('firma_subgerente')->nullable();
            $table->integer('inicio_conteo')->default(0);
            $table->integer('fin_conteo')->default(0);
            $table->foreignId('status_id')->default(1)->constrained('inventarios_status');
            $table->boolean('finalizado')->default(false);
            $table->text('comentarios')->nullable();
            $table->text('motivo_cancelacion')->nullable();
            $table->boolean('eliminado')->default(false);
            $table->timestamps();
        });

        Schema::create('almacenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_id')->constrained('inventarios');
            $table->string('nombre', 250);
            $table->integer('n_conteo')->default(0);
            $table->boolean('eliminado')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('almacenes');
        Schema::dropIfExists('inventarios');
        Schema::dropIfExists('inventarios_status');
    }
};
