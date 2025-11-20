<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventKategoriTable extends Migration
{
    public function up()
    {
        Schema::create('event_kategori', function (Blueprint $table) {
            $table->increments('eventkategori_id');
            $table->string('nama_kategori');
            $table->text('deskripsi_kategori')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_kategori');
    }
}
