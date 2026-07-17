<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event', function (Blueprint $table) {
            $table->string('kategori_lokasi')->nullable()->after('eventkategori_id');
            $table->string('jenis_kegiatan')->nullable()->after('kategori_lokasi');
        });
    }

    public function down(): void
    {
        Schema::table('event', function (Blueprint $table) {
            $table->dropColumn(['kategori_lokasi', 'jenis_kegiatan']);
        });
    }
};
