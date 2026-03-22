<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fixed asset inventory sessions
        Schema::create('activo_fijo_inventarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('status_id')->default(1)->constrained('inventarios_status');
            $table->string('comentarios', 250)->nullable();
            $table->integer('inicio_conteo')->default(0);
            $table->integer('fin_conteo')->default(0);
            $table->boolean('finalizado')->default(false);
            $table->boolean('eliminado')->default(false);
            $table->timestamps();
        });

        // Individual asset scan records
        Schema::create('activo_fijo_registros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_id')->constrained('activo_fijo_inventarios');
            $table->foreignId('usuario_id')->constrained('users');
            $table->unsignedBigInteger('id_producto')->default(0)->index();
            $table->string('codigo_1', 250)->nullable()->index();
            $table->string('codigo_1_anterior', 250)->nullable();
            $table->string('codigo_2', 250)->nullable()->index();
            $table->string('codigo_3', 250)->nullable();
            $table->string('tag_rfid', 120)->nullable()->index();
            $table->string('n_serie', 250)->nullable();
            $table->string('n_serie_anterior', 250)->nullable();
            $table->string('n_serie_nuevo', 250)->nullable();
            $table->string('nombre_almacen', 250)->nullable();
            $table->string('ubicacion_1', 50)->nullable();
            $table->string('categoria', 250)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('imagen1', 128)->nullable();
            $table->string('imagen2', 128)->nullable();
            $table->string('imagen3', 128)->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('traspasado')->default(false);
            $table->string('sucursal_origen', 50)->nullable();
            $table->boolean('forzado')->default(false);
            $table->boolean('solicitado')->default(false);
            $table->double('latitud')->default(0);
            $table->double('longitud')->default(0);
            $table->string('version_app', 20)->nullable();
            $table->boolean('eliminado')->default(false);
            $table->timestamps();
        });

        // Assets not found during survey
        Schema::create('activos_no_encontrados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_id')->constrained('activo_fijo_inventarios');
            $table->unsignedBigInteger('activo');
            $table->foreignId('usuario_id')->constrained('users');
            $table->double('latitud')->default(0);
            $table->double('longitud')->default(0);
            $table->timestamps();
        });

        // Asset transfers between branches
        Schema::create('activos_traspasados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activo');
            $table->foreignId('sucursal_origen_id')->constrained('sucursales');
            $table->foreignId('sucursal_destino_id')->constrained('sucursales');
            $table->foreignId('usuario_id')->constrained('users');
            $table->boolean('eliminado')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activos_traspasados');
        Schema::dropIfExists('activos_no_encontrados');
        Schema::dropIfExists('activo_fijo_registros');
        Schema::dropIfExists('activo_fijo_inventarios');
    }
};
