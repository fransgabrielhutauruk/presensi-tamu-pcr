<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sqlsrv_dwh')->create('fact_kunjungan', function (Blueprint $table) {
            $table->integer('kunjungan_id')->primary();
            $table->integer('tamu_id')->nullable();
            $table->integer('civitas_id')->nullable();
            $table->string('identitas');
            $table->string('is_vip');
            $table->string('jenis_kunjungan');
            $table->integer('event_id')->nullable();
            $table->integer('kunjungankategori_id')->nullable();
            $table->integer('transportasi_id')->nullable();
            $table->integer('waktu_id');
            $table->time('waktu_masuk')->nullable();
            $table->time('waktu_keluar')->nullable();
            $table->integer('jumlah_kunjungan');
            $table->integer('jumlah_rombongan')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection('sqlsrv_dwh')->dropIfExists('fact_kunjungan');
    }
};
