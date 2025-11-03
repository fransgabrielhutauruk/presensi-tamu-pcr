<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\KontenMainSeeder as SeedersKontenMainSeeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            MstOpsiKunjunganSeeder::class,
            EventKategoriSeeder::class,
            EventSeeder::class
        ]);
    }
}
