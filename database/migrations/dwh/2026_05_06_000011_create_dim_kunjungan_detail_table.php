<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sqlsrv_dwh')->create('dim_kunjungan_detail', function (Blueprint $table) {
            $table->integer('kunjungan_id');
            $table->integer('detail_id');
            $table->string('nilai');

            $table->primary(['kunjungan_id', 'detail_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('sqlsrv_dwh')->dropIfExists('dim_kunjungan_detail');
    }
};
