<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sqlsrv_dwh')->create('dim_event', function (Blueprint $table) {
            $table->integer('event_id')->primary();
            $table->integer('eventkategori_id')->nullable();
            $table->string('nama_event');
            $table->date('tanggal');
            $table->time('waktu_mulai')->nullable();
            $table->time('waktu_selesai')->nullable();
            $table->string('lokasi')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection('sqlsrv_dwh')->dropIfExists('dim_event');
    }
};
