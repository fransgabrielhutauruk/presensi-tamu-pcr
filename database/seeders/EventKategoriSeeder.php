<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventKategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'nama_kategori' => 'Akademik dan Inovasi Pembelajaran',
                'created_at' => now(),
            ],
            [
                'nama_kategori' => 'Sumber Daya',
                'created_at' => now(),
            ],
            [
                'nama_kategori' => 'Keuangan, Perencanaan, dan Kelembagaan',
                'created_at' => now(),
            ],
            [
                'nama_kategori' => 'Kemahasiswaan, Pemasaran, dan Kemitraan',
                'created_at' => now(),
            ],
            [
                'nama_kategori' => 'Jurusan Teknologi Informasi',
                'created_at' => now(),
            ],
            [
                'nama_kategori' => 'Jurusan Teknologi Industri',
                'created_at' => now(),
            ],
            [
                'nama_kategori' => 'Jurusan Bisnis dan Komunikasi',
                'created_at' => now(),
            ],
        ];

        DB::table('event_kategori')->insert($categories);
    }
}
