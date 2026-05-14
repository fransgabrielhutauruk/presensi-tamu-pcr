<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sqlsrv_dwh')->create('dim_transportasi', function (Blueprint $table) {
            $table->integer('transportasi_id')->primary();
            $table->string('transportasi');
        });
    }

    public function down(): void
    {
        Schema::connection('sqlsrv_dwh')->dropIfExists('dim_transportasi');
    }
};
