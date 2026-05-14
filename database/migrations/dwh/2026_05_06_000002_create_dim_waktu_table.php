<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sqlsrv_dwh')->create('dim_waktu', function (Blueprint $table) {
            $table->integer('waktu_id')->primary();
            $table->integer('tahun');
            $table->integer('bulan');
            $table->date('tanggal');
        });
    }

    public function down(): void
    {
        Schema::connection('sqlsrv_dwh')->dropIfExists('dim_waktu');
    }
};