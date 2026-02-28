<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activos_traspasados', function (Blueprint $table) {
            $table->unsignedInteger('n_orden')->nullable()->after('activo');
            $table->string('estatus', 50)->default('Pendiente')->after('comentarios');
            $table->unsignedBigInteger('autorizado_por')->nullable()->after('estatus');
            $table->unsignedBigInteger('surtido_por')->nullable()->after('autorizado_por');
            $table->unsignedBigInteger('cancelado_por')->nullable()->after('surtido_por');
            $table->timestamp('fecha_hora_surtido')->nullable()->after('cancelado_por');
            $table->timestamp('fecha_hora_cancelacion')->nullable()->after('fecha_hora_surtido');
        });
    }

    public function down(): void
    {
        Schema::table('activos_traspasados', function (Blueprint $table) {
            $table->dropColumn([
                'n_orden', 'estatus', 'autorizado_por', 'surtido_por',
                'cancelado_por', 'fecha_hora_surtido', 'fecha_hora_cancelacion',
            ]);
        });
    }
};
