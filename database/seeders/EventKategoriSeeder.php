<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
                'nama_kategori' => 'PMB',
                'deskripsi_kategori' => 'Penerimaan Mahasiswa Baru',
                'updated_by' => null,
                'deleted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kategori' => 'BP2M',
                'deskripsi_kategori' => 'Bagian Penelitian dan Pengabdian Kepada Masyarakat',
                'updated_by' => null,
                'deleted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('event_kategori')->insert($categories);
    }
}
