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
            $table->unsignedInteger('tamu_id');
            $table->unsignedInteger('event_id')->nullable();
            $table->enum('identitas', ['non-civitas', 'civitas']);
            $table->string('kategori_tujuan')->nullable();
            $table->integer('jumlah_rombongan')->nullable();
            $table->time('waktu_keluar');
            $table->string('transportasi')->nullable();
            $table->boolean('is_checkout')->default(false);
            $table->timestamp('checkout_time')->nullable();
            $table->boolean('status_validasi')->default(false);
            $table->timestamp('reminder_sent_at')->nullable();
            $table->string('created_by', 10)->nullable();
            $table->string('updated_by', 10)->nullable();
            $table->string('deleted_by', 10)->nullable();
            $table->timestamps();
            $table->softDeletes();
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
