<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activo_fijo_rfid_tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id')->index();
            $table->string('epc', 120)->index();
            $table->integer('rssi')->default(0);
            $table->integer('read_count')->default(1);
            $table->boolean('matched')->default(false);
            $table->unsignedBigInteger('matched_registro_id')->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('activo_fijo_inventarios')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activo_fijo_rfid_tags');
    }
};
