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
                    [
                        'id' => 'Direktur',
                        'en' => 'Director'
                    ],
                    [
                        'id' => 'Penjaminan Mutu',
                        'en' => 'Quality Assurance'
                    ],
                    [
                        'id' => 'Bidang Akademik dan Inovasi Pembelajaran',
                        'en' => 'Academic Field and Learning Innovation'
                    ],
                    [
                        'id' => 'Bidang Sumber Daya',
                        'en' => 'Resource Field'
                    ],
                    [
                        'id' => 'Bidang Keuangan, Perencanaan dan Kelembagaan',
                        'en' => 'Finance, Planning and Institutional Affairs'
                    ],
                    [
                        'id' => 'Bidang Kemahasiswaan, Pemasaran, dan Kemitraan',
                        'en' => 'Student Affairs, Marketing, and Partnerships'
                    ],
                ]
            ],
            [
                'nama_opsi' => 'pihak_dituju_ortu',
                'deskripsi_opsi' => 'Digunakan pada form presensi non-event kategori kunjungan orang tua/wali mahasiswa',
                'nilai_opsi' => [
                    [
                        'id' => 'Bagian Administrasi Akademik (BAAK)',
                        'en' => 'Academic Administration Section'
                    ],
                    [
                        'id' => 'Bagian Keuangan',
                        'en' => 'Financial Department'
                    ],
                    [
                        'id' => 'Dosen Wali',
                        'en' => 'Guardian lecturer'
                    ],
                    [
                        'id' => 'Ketua Program Studi',
                        'en' => 'Head of The Study Program'
                    ],
                    [
                        'id' => 'Kemahasiswaan',
                        'en' => 'Student Affairs'
                    ],
                ]
            ],
            [
                'nama_opsi' => 'prodi',
                'deskripsi_opsi' => 'Program studi',
                'nilai_opsi' => [
                    [
                        'id' => 'Teknik Informatika (TI)',
                        'en' => 'Computer Engineering'
                    ],
                    [
                        'id' => 'Sistem Informasi (SI)',
                        'en' => 'Information Systems'
                    ],
                    [
                        'id' => 'Teknologi Rekayasa Komputer (TRK)',
                        'en' => 'Computer Engineering Technology'
                    ],
                    [
                        'id' => 'Magister Terapan Teknik Komputer (MTTK)',
                        'en' => 'Master of Applied Computer Engineering'
                    ],
                    [
                        'id' => 'Teknologi Rekayasa Jaringan Telekomunikasi (TRJT)',
                        'en' => 'Telecommunication Network Engineering Technology'
                    ],
                    [
                        'id' => 'Teknik Listrik (TL)',
                        'en' => 'Electrical Engineering'
                    ],
                    [
                        'id' => 'Teknik Elektronika (TET)',
                        'en' => 'Electronics Engineering'
                    ],
                    [
                        'id' => 'Teknologi Rekayasa Sistem Elektronika (TRSE)',
                        'en' => 'Electronics System Engineering Technology'
                    ],
                    [
                        'id' => 'Teknologi Rekayasa Mekatronika (TRM)',
                        'en' => 'Mechatronics Engineering Technology'
                    ],
                    [
                        'id' => 'Teknik Mesin (TMS)',
                        'en' => 'Mechanical Engineering'
                    ],
                    [
                        'id' => 'Hubungan Masyarakat dan Komunikasi Digital (HMKD)',
                        'en' => 'Public Relations and Digital Communication'
                    ],
                    [
                        'id' => 'Akuntansi Perpajakan (AKTP)',
                        'en' => 'Tax Accounting'
                    ],
                    [
                        'id' => 'Bisnis Digital (BD)',
                        'en' => 'Digital Business'
                    ]
                ]
            ]
        ];

        foreach ($opsiData as $data) {
            MstOpsiKunjungan::create($data);
        }
    }
}
