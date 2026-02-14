<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Summary-level count records (one per product per inventory)
        Schema::create('inventario_registros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_id')->constrained('inventarios');
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('producto_id')->nullable()->constrained('productos');
            $table->string('nombre_conteo', 150)->nullable();
            $table->decimal('cantidad', 10, 3)->default(0);
            $table->string('codigo_1', 250)->nullable()->index();
            $table->string('codigo_2', 250)->nullable();
            $table->string('codigo_3', 250)->nullable();
            $table->string('ubicacion_1', 250)->nullable();
            $table->string('ubicacion_2', 250)->nullable();
            $table->string('ubicacion_3', 250)->nullable();
            $table->decimal('precio_compra', 10, 3)->default(0);
            $table->decimal('precio_venta', 10, 3)->default(0);
            $table->decimal('factor', 10, 3)->default(1);
            $table->string('unidad_medida', 250)->nullable();
            $table->foreignId('almacen_id')->nullable()->constrained('almacenes');
            $table->string('nombre_almacen', 250)->nullable();
            $table->decimal('cantidad_teorica', 10, 3)->default(0);
            $table->string('lote', 250)->nullable();
            $table->string('fecha_caducidad', 50)->nullable();
            $table->boolean('sincronizado')->default(false);
            $table->boolean('forzado')->default(false);
            $table->boolean('eliminado')->default(false);
            $table->timestamps();
        });

        // Line-item detail records (individual scans)
        Schema::create('inventario_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registro_id')->constrained('inventario_registros');
            $table->foreignId('inventario_id')->constrained('inventarios');
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('producto_id')->nullable()->constrained('productos');
            $table->integer('n_conteo')->default(1);
            $table->string('nombre_conteo', 150)->nullable();
            $table->decimal('cantidad', 10, 3)->default(0);
            $table->decimal('factor', 10, 3)->default(1);
            $table->string('codigo_1', 250)->nullable();
            $table->string('codigo_2', 250)->nullable();
            $table->string('codigo_3', 250)->nullable();
            $table->string('unidad_medida', 250)->nullable();
            $table->string('nombre_usuario', 250)->nullable();
            $table->string('lote', 250)->nullable();
            $table->string('fecha_caducidad', 50)->nullable();
            $table->string('fecha_elaboracion', 50)->nullable();
            $table->string('n_serie', 250)->nullable();
            $table->string('ubicacion_1', 250)->nullable();
            $table->string('ubicacion_2', 250)->nullable();
            $table->foreignId('almacen_id')->nullable()->constrained('almacenes');
            $table->string('nombre_almacen', 250)->nullable();
            $table->double('latitud')->default(0);
            $table->double('longitud')->default(0);
            $table->string('id_dispositivo', 250)->nullable();
            $table->string('marca_dispositivo', 250)->nullable();
            $table->string('modelo_dispositivo', 250)->nullable();
            $table->string('version_app', 250)->nullable();
            $table->integer('id_app')->default(0);
            $table->boolean('forzado')->default(false);
            $table->boolean('editado')->default(false);
            $table->boolean('eliminado')->default(false);
            $table->date('fecha_captura')->nullable();
            $table->time('hora_captura')->nullable();
            $table->timestamps();
        });

        // Cross-count verification records
        Schema::create('inventario_cruzado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_id')->constrained('inventarios');
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('producto_id')->nullable()->constrained('productos');
            $table->decimal('cantidad', 10, 3)->default(0);
            $table->string('codigo_1', 250)->nullable();
            $table->string('codigo_2', 250)->nullable();
            $table->string('nombre_almacen', 250)->nullable();
            $table->string('lote', 250)->nullable();
            $table->string('fecha_caducidad', 50)->nullable();
            $table->boolean('eliminado')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_cruzado');
        Schema::dropIfExists('inventario_detalle');
        Schema::dropIfExists('inventario_registros');
    }
};
