<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePresensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            // Data Pengunjung
            'name' => 'required|string|max:255',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            
            // Data Kunjungan
            'kategori_tujuan' => 'required|in:instansi,bisnis,ortu,calon_ortu,lainnya',
            'waktu_keluar' => 'required|date_format:H:i|after:waktu_masuk',
            'transportasi' => 'required|string|max:255',
        ];

        switch ($this->kategori_tujuan) {
            case 'instansi':
                $rules = array_merge($rules, [
                    'instansi' => 'required|string|max:255',
                    'jenis_instansi' => 'required|string|max:255',
                    'jabatan' => 'required|string|max:255',
                    'tujuan_spesifik' => 'required|string|max:255',
                    'pihak_dituju' => 'required|string|max:255',
                ]);
                break;

            case 'bisnis':
                $rules = array_merge($rules, [
                    'instansi' => 'required|string|max:255',
                    'bidang_usaha' => 'required|string|max:255',
                    'skala_perusahaan' => 'required|string|max:255',
                    'jabatan' => 'required|string|max:255',
                    'jenis_kerjasama' => 'required|string|max:255',
                    'pihak_dituju' => 'required|string|max:255',
                ]);
                break;

            case 'ortu':
                $rules = array_merge($rules, [
                    'hubungan_dengan_mahasiswa' => 'required|string|max:255',
                    'nim_mahasiswa' => 'required|string|max:20',
                    'nama_mahasiswa' => 'required|string|max:255',
                    'keperluan' => 'required|string|max:255',
                    'pihak_dituju_ortu' => 'required|string|max:255',
                ]);
                break;

            case 'calon_ortu':
                $rules = array_merge($rules, [
                    'asal_sekolah' => 'required|string|max:255',
                    'prodi_diminati' => 'required|string|max:255',
                ]);
                break;

            case 'lainnya':
                $rules = array_merge($rules, [
                    'asal' => 'required|string|max:255',
                    'keperluan_detail' => 'required|string|max:1000',
                    'pihak_dituju_lainnya' => 'required|string|max:255',
                ]);
                break;
        }

        return $rules;
    }

    public function messages(): array
    {
        return idn_message();
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Lengkap',
            'gender' => 'Jenis Kelamin',
            'phone_number' => 'Nomor Telepon',
            'email' => 'Email',
            'kategori_tujuan' => 'Kategori Tujuan',
            'waktu_masuk' => 'Waktu Masuk',
            'waktu_keluar' => 'Waktu Keluar',
            'transportasi' => 'Transportasi',
            'instansi' => 'Instansi',
            'jenis_instansi' => 'Jenis Instansi',
            'jabatan' => 'Jabatan',
            'tujuan_spesifik' => 'Tujuan Spesifik',
            'pihak_dituju' => 'Pihak yang Dituju',
            'bidang_usaha' => 'Bidang Usaha',
            'skala_perusahaan' => 'Skala Perusahaan',
            'jenis_kerjasama' => 'Jenis Kerjasama',
            'hubungan_dengan_mahasiswa' => 'Hubungan dengan Mahasiswa',
            'nim_mahasiswa' => 'NIM Mahasiswa',
            'nama_mahasiswa' => 'Nama Mahasiswa',
            'keperluan' => 'Keperluan',
            'pihak_dituju_ortu' => 'Pihak yang Dituju',
            'asal_sekolah' => 'Asal Sekolah',
            'prodi_diminati' => 'Program Studi Diminati',
            'asal' => 'Asal',
            'keperluan_detail' => 'Detail Keperluan',
            'pihak_dituju_lainnya' => 'Pihak yang Dituju',
        ];
    }
}
