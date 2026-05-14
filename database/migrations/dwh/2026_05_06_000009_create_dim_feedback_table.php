<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sqlsrv_dwh')->create('dim_feedback', function (Blueprint $table) {
            $table->integer('feedback_id')->primary();
            $table->integer('kunjungan_id');
            $table->integer('rating')->nullable();
            $table->text('komentar')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection('sqlsrv_dwh')->dropIfExists('dim_feedback');
    }
};
