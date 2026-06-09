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
        Schema::table('kunjungan_detail', function (Blueprint $table) {
            Schema::table('kunjungan_detail', function (Blueprint $table) {
                $table->text('nilai')->change();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kunjungan_detail', function (Blueprint $table) {
            $table->string('nilai', 100)->change();
        });
    }
};
