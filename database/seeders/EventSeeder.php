<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = [
            [
                'eventkategori_id' => 1,
                'nama_event' => 'Kunjungan SMKN 4 Pekanbaru',
                'tanggal_event' => '2025-11-02',
                'waktu_mulai_event' => '07:00:00',
                'waktu_selesai_event' => '12:00:00',
                'lokasi_event' => 'GSG',
                'updated_by' => null,
                'deleted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('event')->insert($events);
    }
}
