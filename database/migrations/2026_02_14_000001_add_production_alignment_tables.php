<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Fixed Asset Catalog (maps to prod "productos" in activo_fijo DB) ──
        Schema::create('activo_fijo_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_id')->constrained('activo_fijo_inventarios')->cascadeOnDelete();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('subsidiaria', 50)->default('');
            $table->unsignedBigInteger('sucursal')->default(0)->index();
            $table->string('codigo_1', 50)->default('')->index();
            $table->string('codigo_2', 50)->default('')->index();
            $table->string('codigo_3', 50)->default('')->index();
            $table->string('tag_rfid', 120)->default('')->index();
            $table->text('descripcion');
            $table->string('n_serie', 256)->default('')->index();
            $table->string('n_serie_anterior', 256)->default('');
            $table->string('n_serie_nuevo', 256)->default('')->index();
            $table->string('categoria_1', 256)->default('');
            $table->string('categoria_2', 256)->default('');
            $table->string('marca', 256)->default('');
            $table->string('modelo', 256)->default('');
            $table->string('tipo_activo', 256)->default('');
            $table->string('fecha_inicio_servicio', 256)->default('');
            $table->string('imagen1', 256)->default('');
            $table->string('imagen2', 256)->default('');
            $table->string('imagen3', 256)->default('');
            $table->integer('cantidad_teorica')->default(0);
            $table->text('observaciones')->nullable();
            $table->boolean('eliminado')->default(false)->index();
            $table->boolean('no_encontrado')->default(false)->index();
            $table->boolean('forzado')->default(false)->index();
            $table->boolean('traspasado')->default(false);
            $table->boolean('solicitado')->default(false);
            $table->dateTime('fecha_registro')->nullable();
            $table->timestamps();
        });

        // ── 2. Transfer Order Status Catalog ──
        Schema::create('ordenes_entrada_estatus', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('nombre_estatus', 50);
        });

        DB::table('ordenes_entrada_estatus')->insert([
            ['id' => 1, 'nombre_estatus' => 'Pendiente'],
            ['id' => 2, 'nombre_estatus' => 'En proceso'],
            ['id' => 3, 'nombre_estatus' => 'Rechazado'],
            ['id' => 4, 'nombre_estatus' => 'Surtido'],
            ['id' => 5, 'nombre_estatus' => 'Cancelado'],
        ]);

        // ── 3. Transfer Orders ──
        Schema::create('ordenes_entrada', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users');
            $table->integer('n_orden')->default(0);
            $table->foreignId('inventario_origen_id')->constrained('activo_fijo_inventarios');
            $table->foreignId('inventario_destino_id')->constrained('activo_fijo_inventarios');
            $table->string('motivo', 256)->default('');
            $table->text('comentarios')->nullable();
            $table->unsignedTinyInteger('estatus_id')->default(1);
            $table->foreignId('autorizado_por')->nullable()->constrained('users');
            $table->foreignId('surtido_por')->nullable()->constrained('users');
            $table->foreignId('cancelado_por')->nullable()->constrained('users');
            $table->foreignId('rechazado_por')->nullable()->constrained('users');
            $table->dateTime('fecha_hora_surtido')->nullable();
            $table->dateTime('fecha_hora_cancelacion')->nullable();
            $table->boolean('eliminado')->default(false)->index();
            $table->timestamps();

            $table->foreign('estatus_id')->references('id')->on('ordenes_entrada_estatus');
        });

        // ── 4. Transfer Order Line Items ──
        Schema::create('ordenes_entrada_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_entrada_id')->constrained('ordenes_entrada')->cascadeOnDelete();
            $table->foreignId('registro_id')->constrained('activo_fijo_registros');
            $table->foreignId('inventario_id')->constrained('activo_fijo_inventarios');
            $table->unsignedTinyInteger('estatus')->default(0);
            $table->boolean('eliminado')->default(false);
            $table->timestamps();
        });

        // ── 5. Mobile Session Logs ──
        Schema::create('log_sesiones_movil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_id')->constrained('activo_fijo_inventarios');
            $table->foreignId('usuario_id')->constrained('users');
            $table->dateTime('fecha_hora_entrada')->nullable();
            $table->dateTime('fecha_hora_salida')->nullable();
            $table->string('plataforma_dispositivo', 100)->nullable();
            $table->string('serie_dispositivo', 250)->nullable();
            $table->float('latitud')->default(0);
            $table->float('longitud')->default(0);
            $table->boolean('sesion_activa')->default(false)->index();
            $table->timestamps();

            $table->index('fecha_hora_entrada');
        });

        // ── 6. Alter users table ──
        Schema::table('users', function (Blueprint $table) {
            $table->string('archivo_imagen', 250)->nullable()->after('expiracion_sesion');
            $table->boolean('activo')->default(true)->after('archivo_imagen');
        });

        // ── 7. Alter activo_fijo_inventarios table ──
        Schema::table('activo_fijo_inventarios', function (Blueprint $table) {
            $table->string('sucursal_codigo', 150)->default('')->after('sucursal_id');
            $table->string('ciudad', 150)->default('')->after('sucursal_codigo');
            $table->string('local', 150)->default('')->after('ciudad');
            $table->string('nombre', 150)->default('')->after('local');
        });
    }

    public function down(): void
    {
        Schema::table('activo_fijo_inventarios', function (Blueprint $table) {
            $table->dropColumn(['sucursal_codigo', 'ciudad', 'local', 'nombre']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['archivo_imagen', 'activo']);
        });

        Schema::dropIfExists('log_sesiones_movil');
        Schema::dropIfExists('ordenes_entrada_detalle');
        Schema::dropIfExists('ordenes_entrada');
        Schema::dropIfExists('ordenes_entrada_estatus');
        Schema::dropIfExists('activo_fijo_productos');
    }
};
