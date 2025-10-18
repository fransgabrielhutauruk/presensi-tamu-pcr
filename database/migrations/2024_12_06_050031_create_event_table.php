<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventTable extends Migration
{
    public function up()
    {
        Schema::create('event', function (Blueprint $table) {
            $table->increments('event_id');
            $table->unsignedInteger('eventkategori_id');
            $table->string('nama_event');
            $table->text('deskripsi_event')->nullable();
            $table->date('tanggal_event');
            $table->time('waktu_mulai_event')->nullable();
            $table->time('waktu_selesai_event')->nullable();
            $table->string('lokasi_event')->nullable();
            $table->string('status_event');
            $table->string('created_by', 10)->nullable();
            $table->string('updated_by', 10)->nullable();
            $table->string('deleted_by', 10)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('event');
    }
}
