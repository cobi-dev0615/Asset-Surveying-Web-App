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
        Schema::table('activos_traspasados', function (Blueprint $table) {
            $table->string('motivo', 255)->nullable()->after('usuario_id');
            $table->text('comentarios')->nullable()->after('motivo');
        });
    }

    public function down(): void
    {
        Schema::table('activos_traspasados', function (Blueprint $table) {
            $table->dropColumn(['motivo', 'comentarios']);
        });
    }
};
