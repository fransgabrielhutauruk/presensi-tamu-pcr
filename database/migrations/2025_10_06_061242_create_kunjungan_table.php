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
        Schema::create('kunjungan', function (Blueprint $table) {
            $table->increments('kunjungan_id');
            $table->unsignedInteger('tamu_id')->nullable();
            $table->unsignedInteger('civitas_id')->nullable();
            $table->unsignedInteger('event_id')->nullable();
            $table->enum('identitas', ['non-civitas', 'civitas']);
            $table->boolean('is_vip')->default(false);
            $table->string('kategori_tujuan')->nullable();
            $table->integer('jumlah_rombongan')->nullable();
            $table->time('waktu_keluar');
            $table->string('transportasi')->nullable();
            $table->boolean('is_checkout')->default(false);
            $table->timestamp('checkout_time')->nullable();
            $table->boolean('status_validasi')->default(false);
            $table->timestamp('reminder_sent_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('tamu_id')->references('tamu_id')->on('tamu')->onDelete('cascade');
            $table->foreign('civitas_id')->references('civitas_id')->on('civitas')->onDelete('cascade');
            $table->foreign('event_id')->references('event_id')->on('event')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kunjungan');
    }
};
