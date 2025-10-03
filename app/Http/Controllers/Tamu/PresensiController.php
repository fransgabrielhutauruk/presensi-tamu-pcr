<?php

namespace App\Http\Controllers\Tamu;

use App\Http\Controllers\Controller;
use App\Models\Tamu;
use App\Models\Kunjungan;
use App\Models\DetailKunjungan;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PresensiController extends Controller
{
    public function index()
    {
        return view('tamu.selamat-datang');
    }

    public function pilihTujuan()
    {
        $kategoriOptions = Kunjungan::getKategoriOptions();
        return view('tamu.pilih-tujuan', compact('kategoriOptions'));
    }

    public function simpanTujuan(Request $request)
    {
        $request->validate([
            'tujuan' => ['required', 'string', Rule::in(array_keys(Kunjungan::getKategoriOptions()))]
        ]);

        Session::put('presensi.tujuan', $request->tujuan);

        return redirect()->route('tamu.form');
    }

    public function form()
    {
        $tujuan = Session::get('presensi.tujuan');
        if (!$tujuan) {
            return redirect()->route('tamu.pilih-tujuan');
        }

        $kategoriOptions = Kunjungan::getKategoriOptions();
        $selectedKategori = $kategoriOptions[$tujuan] ?? $tujuan;

        return view('tamu.form', compact('tujuan', 'selectedKategori'));
    }

    public function simpanForm(Request $request)
    {
        $this->validatePresensiForm($request);

        try {
            DB::beginTransaction();

            // Cek apakah tamu sudah ada berdasarkan email atau phone
            $tamu = Tamu::where('email', $request->email)
                       ->orWhere('phone_number', $request->phone_number)
                       ->first();

            if (!$tamu) {
                $tamu = Tamu::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone_number' => $request->phone_number,
                ]);
            } else {
                // Update data tamu jika ada perubahan
                $tamu->update([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone_number' => $request->phone_number,
                ]);
            }

            $kunjungan = Kunjungan::create([
                'fk_tamu_id' => $tamu->tamu_id,
                'kategori_tujuan' => $request->kategori_tujuan,
                'waktu_masuk' => now()->format('H:i:s'),
                'waktu_keluar' => null,
                'transportasi' => $request->transportasi ?? 'Tidak disebutkan',
                'status_validasi' => false,
                'waktu_presensi' => now(),
            ]);

            $detailData = $this->prepareDetailData($request);

            $urutan = 1;
            foreach ($detailData as $key => $value) {
                if (!empty($value)) {
                    DetailKunjungan::create([
                        'fk_kunjungan_id' => $kunjungan->kunjungan_id,
                        'kunci' => $key,
                        'nilai' => $value,
                        'urutan' => $urutan++,
                    ]);
                }
            }

            DB::commit();

            Session::forget('presensi.tujuan');

            return redirect()->route('tamu.sukses', [
                'kode' => $kunjungan->kunjungan_id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'submit' => 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.'
            ])->withInput();
        }
    }

    private function validatePresensiForm(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => 'required|string|max:20',
            'kategori_tujuan' => ['required', Rule::in(array_keys(Kunjungan::getKategoriOptions()))],
            'transportasi' => 'nullable|string|max:100',
        ];

        // Validasi dinamis berdasarkan kategori tujuan
        switch ($request->kategori_tujuan) {
            case 'instansi':
                $rules = array_merge($rules, [
                    'instansi' => 'required|string|max:255',
                    'jenis_instansi' => 'nullable|string|max:100',
                    'jabatan' => 'nullable|string|max:100',
                    'tujuan_spesifik' => 'nullable|string|max:500',
                    'pihak_dituju' => 'nullable|string|max:255',
                ]);
                break;

            case 'bisnis':
                $rules = array_merge($rules, [
                    'instansi' => 'required|string|max:255',
                    'bidang_usaha' => 'nullable|string|max:100',
                    'skala_perusahaan' => 'nullable|string|max:50',
                    'jabatan' => 'nullable|string|max:100',
                    'jenis_kerjasama' => 'nullable|string|max:255',
                    'pihak_dituju' => 'nullable|string|max:255',
                ]);
                break;

            case 'ortu':
                $rules = array_merge($rules, [
                    'hubungan_dengan_mahasiswa' => 'required|string|max:50',
                    'nim_mahasiswa' => 'nullable|string|max:20',
                    'nama_mahasiswa' => 'required|string|max:255',
                    'prodi_mahasiswa' => 'nullable|string|max:100',
                    'keperluan' => 'nullable|string|max:500',
                    'pihak_dituju_ortu' => 'nullable|string|max:255',
                ]);
                break;

            case 'calon_ortu':
                $rules = array_merge($rules, [
                    'asal_sekolah' => 'required|string|max:255',
                    'prodi_diminati' => 'nullable|string|max:100',
                    'pertanyaan' => 'nullable|string|max:1000',
                ]);
                break;

            case 'lainnya':
                $rules = array_merge($rules, [
                    'asal' => 'nullable|string|max:255',
                    'keperluan_detail' => 'required|string|max:500',
                    'pihak_dituju_lainnya' => 'nullable|string|max:255',
                ]);
                break;
        }

        $request->validate($rules);
    }

    private function prepareDetailData(Request $request)
    {
        $detailData = [];

        switch ($request->kategori_tujuan) {
            case 'instansi':
                $detailData = [
                    'instansi' => $request->instansi,
                    'jenis_instansi' => $request->jenis_instansi,
                    'jabatan' => $request->jabatan,
                    'tujuan_spesifik' => $request->tujuan_spesifik,
                    'pihak_dituju' => $request->pihak_dituju,
                ];
                break;

            case 'bisnis':
                $detailData = [
                    'instansi' => $request->instansi,
                    'bidang_usaha' => $request->bidang_usaha,
                    'skala_perusahaan' => $request->skala_perusahaan,
                    'jabatan' => $request->jabatan,
                    'jenis_kerjasama' => $request->jenis_kerjasama,
                    'pihak_dituju' => $request->pihak_dituju,
                ];
                break;

            case 'ortu':
                $detailData = [
                    'hubungan_dengan_mahasiswa' => $request->hubungan_dengan_mahasiswa,
                    'nim_mahasiswa' => $request->nim_mahasiswa,
                    'nama_mahasiswa' => $request->nama_mahasiswa,
                    'prodi_mahasiswa' => $request->prodi_mahasiswa,
                    'keperluan' => $request->keperluan,
                    'pihak_dituju_ortu' => $request->pihak_dituju_ortu,
                ];
                break;

            case 'calon_ortu':
                $detailData = [
                    'asal_sekolah' => $request->asal_sekolah,
                    'prodi_diminati' => $request->prodi_diminati,
                    'pertanyaan' => $request->pertanyaan,
                ];
                break;

            case 'lainnya':
                $detailData = [
                    'asal' => $request->asal,
                    'keperluan_detail' => $request->keperluan_detail,
                    'pihak_dituju_lainnya' => $request->pihak_dituju_lainnya,
                ];
                break;
        }

        return array_filter($detailData); // Remove empty values
    }

    public function sukses(Request $request)
    {
        $kodeKunjungan = $request->get('kode');

        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $kodeKunjungan)) {
            return redirect()->route('tamu.index')->with('error', 'Kode kunjungan tidak valid.');
        }

        $kunjungan = Kunjungan::with('tamu')->where('kunjungan_id', $kodeKunjungan)->first();
        if (!$kunjungan) {
            return redirect()->route('tamu.index')->with('error', 'Kunjungan tidak ditemukan.');
        }

        $feedbackExists = Feedback::where('fk_kunjungan_id', $kodeKunjungan)->exists();

        return view('tamu.sukses', compact('kunjungan', 'feedbackExists'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'kode_kunjungan' => 'required|string|exists:kunjungan,kunjungan_id'
        ]);

        try {
            $kunjungan = Kunjungan::where('kunjungan_id', $request->kode_kunjungan)->first();
            
            if (!$kunjungan) {
                return back()->withErrors(['kode_kunjungan' => 'Kunjungan tidak ditemukan.']);
            }

            if ($kunjungan->waktu_keluar) {
                return back()->withErrors(['kode_kunjungan' => 'Anda sudah melakukan checkout.']);
            }

            $kunjungan->update([
                'waktu_keluar' => now()->format('H:i:s')
            ]);

            return redirect()->route('tamu.feedback', ['kode' => $kunjungan->kunjungan_id])
                           ->with('success', 'Checkout berhasil. Silakan berikan feedback Anda.');

        } catch (\Exception $e) {
            return back()->withErrors(['submit' => 'Terjadi kesalahan saat checkout.']);
        }
    }

    public function feedback(Request $request)
    {
        $kodeKunjungan = $request->get('kode');
        
        $kunjungan = Kunjungan::with('tamu')->where('kunjungan_id', $kodeKunjungan)->first();
        if (!$kunjungan) {
            return redirect()->route('tamu.index')->with('error', 'Kunjungan tidak ditemukan.');
        }

        $feedbackExists = Feedback::where('fk_kunjungan_id', $kodeKunjungan)->exists();
        if ($feedbackExists) {
            return redirect()->route('tamu.index')->with('info', 'Anda sudah mengisi feedback untuk kunjungan ini.');
        }

        return view('tamu.feedback', compact('kunjungan'));
    }

    public function simpanFeedback(Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:1000',
            'kode_kunjungan' => 'required|string|exists:kunjungan,kunjungan_id'
        ]);

        try {
            $feedbackExists = Feedback::where('fk_kunjungan_id', $request->kode_kunjungan)->exists();
            if ($feedbackExists) {
                return redirect()->route('tamu.index')->with('info', 'Anda sudah mengisi feedback untuk kunjungan ini.');
            }

            Feedback::create([
                'fk_kunjungan_id' => $request->kode_kunjungan,
                'rating' => $request->rating,
                'komentar' => $request->komentar,
            ]);

            return redirect()->route('tamu.index')->with('success', 'Terima kasih atas penilaian Anda!');
        } catch (\Exception $e) {
            return back()->withErrors(['submit' => 'Terjadi kesalahan saat menyimpan feedback.'])->withInput();
        }
    }
}