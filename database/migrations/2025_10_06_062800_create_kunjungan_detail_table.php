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
        Schema::create('kunjungan_detail', function (Blueprint $table) {
            $table->increments('kunjungandetail_id');
            $table->unsignedInteger('kunjungan_id');
            $table->string('kunci');
            $table->string('nilai');
            $table->integer('urutan')->default(0);
            $table->string('created_by', 10)->nullable();
            $table->string('updated_by', 10)->nullable();
            $table->string('deleted_by', 10)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('kunjungan_id')->references('kunjungan_id')->on('kunjungan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_kunjungan');
    }
};
