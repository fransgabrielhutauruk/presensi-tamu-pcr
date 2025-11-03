<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MstOpsiKunjungan;

class MstOpsiKunjunganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $opsiData = [
            [
                'nama_opsi' => 'pihak_dituju',
                'deskripsi_opsi' => 'Digunakan pada form presensi non-event kategori: Kunjungan Resmi Instansi dan Keperluan Bisnis/Kemitraan',
                'nilai_opsi' => [
                    ['label' => 'Direktur'],
                    ['label' => 'Penjaminan Mutu'],
                    ['label' => 'Akademik dan Inovasi Pembelajaran'],
                    ['label' => 'Keuangan, Perencanaan, dan Kelembagaan'],
                ]
            ],
            [
                'nama_opsi' => 'prodi',
                'deskripsi_opsi' => 'Program studi',
                'nilai_opsi' => [
                    ['label' => 'Teknik Informatika (TI)'],
                    ['label' => 'Sistem Informasi (SI)'],
                    ['label' => 'Teknologi Rekayasa Komputer (TRK)'],
                    ['label' => 'Magister Terapan Teknik Komputer (MTTK)'],
                    ['label' => 'Teknologi Rekayasa Jaringan Telekomunikasi (TRJT)'],
                    ['label' => 'Teknik Listrik (TL)'],
                    ['label' => 'Teknik Elektronika (TET)'],
                    ['label' => 'Teknologi Rekayasa Sistem Elektronika (TRSE)'],
                    ['label' => 'Teknologi Rekayasa Mekatronika (TRM)'],
                    ['label' => 'Teknik Mesin (TMS)'],
                    ['label' => 'Akuntansi Perpajakan (AKTP)'],
                    ['label' => 'Hubungan Masyarakat dan Komunikasi Digital (HMKD)'],
                    ['label' => 'Bisnis Digital (BD)'],
                ]
            ],
        ];

        foreach ($opsiData as $data) {
            MstOpsiKunjungan::create($data);
        }
    }
}
