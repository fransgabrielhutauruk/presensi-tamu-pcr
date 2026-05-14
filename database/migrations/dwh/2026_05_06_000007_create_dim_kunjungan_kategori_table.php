<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sqlsrv_dwh')->create('dim_kunjungan_kategori', function (Blueprint $table) {
            $table->integer('kunjungankategori_id')->primary();
            $table->string('kategori_kunjungan');
        });
    }

    public function down(): void
    {
        Schema::connection('sqlsrv_dwh')->dropIfExists('dim_kunjungan_kategori');
    }
};
