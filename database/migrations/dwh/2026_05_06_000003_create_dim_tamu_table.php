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
        Schema::connection($this->connection)->create('dim_tamu', function (Blueprint $table) {
            $table->integer('tamu_id')->primary();
            $table->string('nama');
            $table->string('email')->nullable();
            $table->string('nomor_telepon')->nullable();
            $table->string('jenis_kelamin')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('dim_tamu');
    }
};
