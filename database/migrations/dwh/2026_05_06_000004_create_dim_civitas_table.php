<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The connection name for the migration.
     *
     * @var string
     */
    protected $connection;

    public function __construct()
    {
        $this->connection = config('database.dwh_connection', 'mysql_dwh');
    }

    public function up(): void
    {
        Schema::connection($this->connection)->create('dim_civitas', function (Blueprint $table) {
            $table->integer('civitas_id')->primary();
            $table->string('nama');
            $table->string('nip')->nullable();
            $table->string('nim')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->string('nomor_telepon')->nullable();
            $table->string('email')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('dim_civitas');
    }
};
