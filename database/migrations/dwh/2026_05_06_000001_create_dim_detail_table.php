<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sqlsrv_dwh')->create('dim_detail', function (Blueprint $table) {
            $table->integer('detail_id')->primary();
            $table->string('kunci');
            $table->string('kategori');
        });
    }

    public function down(): void
    {
        Schema::connection('sqlsrv_dwh')->dropIfExists('dim_detail');
    }
};