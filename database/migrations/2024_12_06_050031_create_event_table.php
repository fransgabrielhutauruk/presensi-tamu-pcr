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
            $table->string('link_dokumentasi_event')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('eventkategori_id')->references('eventkategori_id')->on('event_kategori')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('event');
    }
}
