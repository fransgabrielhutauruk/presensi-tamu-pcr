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
            'nama' => 'required',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'nomor_telepon' => 'required|max:20',
            'email' => 'required|email',

            'kategori_tujuan' => 'required|in:instansi,bisnis,ortu,informasi_kampus,lainnya',
            'waktu_keluar' => 'required|date_format:H:i',
            'transportasi' => 'required|string|max:255',
            'jumlah_rombongan' => 'required|integer|min:1'
        ];

        switch ($this->kategori_tujuan) {
            case 'instansi':
                $rules = array_merge($rules, [
                    'instansi' => 'required|string|max:255',
                    'jenis_instansi' => 'required|string|max:255',
                    'jabatan' => 'required|string|max:255',
                    'pihak_dituju' => 'required|string|max:255',
                    'keperluan' => 'required|string|max:1000',
                ]);
                break;

            case 'bisnis':
                $rules = array_merge($rules, [
                    'instansi' => 'required|string|max:255',
                    'kategori_instansi' => 'required|string|max:255',
                    'skala_instansi' => 'required|string|max:255',
                    'jabatan' => 'required|string|max:255',
                    'pihak_dituju' => 'required|string|max:255',
                    'keperluan' => 'required|string|max:1000',
                ]);
                break;

            case 'ortu':
                $rules = array_merge($rules, [
                    'hubungan_dengan_mahasiswa' => 'required|string|max:255',
                    'nama_mahasiswa' => 'required|string|max:255',
                    'prodi_mahasiswa' => 'required|string|max:255',
                    'nim_mahasiswa' => 'max:20',
                    'pihak_dituju' => 'required|string|max:255',
                    'keperluan' => 'required|string|max:1000',
                ]);
                break;

            case 'informasi_kampus':
                $rules = array_merge($rules, [
                    'asal_sekolah' => 'required|string|max:255',
                    'prodi_diminati' => 'required|string|max:255',
                    'keperluan' => 'required|string|max:1000',
                ]);
                break;

            case 'lainnya':
                $rules = array_merge($rules, [
                    'pihak_dituju' => 'required|string|max:255',
                    'keperluan' => 'required|string|max:1000',
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
