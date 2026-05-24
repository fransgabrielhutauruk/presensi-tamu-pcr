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
        Schema::connection($this->connection)->table('fact_kunjungan', function (Blueprint $table) {
            $table->dropColumn('jumlah_rombongan');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('fact_kunjungan', function (Blueprint $table) {
            $table->integer('jumlah_rombongan')->nullable();
        });
    }
};
