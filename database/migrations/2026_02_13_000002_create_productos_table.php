<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('codigo_1', 250)->index();
            $table->string('codigo_2', 250)->nullable()->index();
            $table->string('codigo_3', 250)->nullable();
            $table->string('codigo_4', 250)->nullable();
            $table->string('codigo_5', 250)->nullable();
            $table->text('descripcion');
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('categoria', 100)->nullable();
            $table->string('subcategoria', 100)->nullable();
            $table->string('subcategoria_2', 100)->nullable();
            $table->decimal('precio_compra', 10, 3)->default(0);
            $table->decimal('precio_venta', 10, 3)->default(0);
            $table->decimal('cantidad_teorica', 10, 3)->default(0);
            $table->decimal('factor', 10, 3)->default(1);
            $table->string('unidad_medida', 20)->nullable();
            $table->string('n_serie', 250)->nullable();
            $table->string('tag_rfid', 120)->nullable()->index();
            $table->text('observaciones')->nullable();
            $table->boolean('seriado')->default(false);
            $table->boolean('forzado')->default(false);
            $table->boolean('eliminado')->default(false);
            $table->timestamps();
        });

        Schema::create('lotes_caducidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('sku', 250)->index();
            $table->text('descripcion')->nullable();
            $table->string('lote', 250)->index();
            $table->date('fecha_caducidad')->nullable();
            $table->decimal('cantidad', 10, 3)->default(0);
            $table->string('almacen', 250)->nullable();
            $table->boolean('eliminado')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotes_caducidades');
        Schema::dropIfExists('productos');
    }
};
