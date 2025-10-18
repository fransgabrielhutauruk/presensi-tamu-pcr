<?php

namespace App\Http\Controllers\Tamu;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePresensiRequest;
use App\Models\Tamu;
use App\Models\Kunjungan;
use App\Models\KunjunganDetail;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PresensiController extends Controller
{
    public function index()
    {
        return view('contents.tamu.pages.index');
    }

    public function tujuan()
    {
        return view('contents.tamu.pages.event-or-nonevent');
    }

    public function noneventTujuan()
    {
        return view('contents.tamu.pages.nonevent-tujuan');
    }

    public function noneventForm()
    {
        return view('contents.tamu.pages.nonevent-form');
    }

    public function store(StorePresensiRequest $request)
    {
        $data = [
            'name' => $request->name,
            'gender' => $request->gender,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'created_by' => 'system'
        ];

        DB::beginTransaction();
        try {
            $tamu = Tamu::create($data);

            $kunjunganData = [
                'tamu_id' => $tamu->tamu_id,
                'kategori_tujuan' => $request->kategori_tujuan,
                'waktu_keluar' => $request->waktu_keluar,
                'transportasi' => $request->transportasi ?? 'Tidak Diketahui',
                'status_validasi' => false,
                'is_checkout' => false,
            ];

            $kunjungan = Kunjungan::create($kunjunganData);

            $detailData = $this->prepareDetailData($request);

            $urutan = 1;
            foreach ($detailData as $key => $value) {
                if (!empty($value)) {
                    KunjunganDetail::create([
                        'kunjungan_id' => $kunjungan->kunjungan_id,
                        'kunci' => $key,
                        'nilai' => $value,
                        'urutan' => $urutan++,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('tamu.sukses', $kunjungan->kunjungan_id)->with('success', 'Presensi berhasil disimpan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan. Silahkan coba lagi');
        }
    }

    private function prepareDetailData(Request $request): array
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
                    'keperluan' => $request->keperluan,
                    'pihak_dituju_ortu' => $request->pihak_dituju_ortu,
                ];
                break;

            case 'calon_ortu':
                $detailData = [
                    'asal_sekolah' => $request->asal_sekolah,
                    'prodi_diminati' => $request->prodi_diminati,
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

        return $detailData;
    }

    public function sukses($kunjunganId)
    {
        try {
            $kunjungan = Kunjungan::with('tamu')->findOrFail($kunjunganId);
            return view('contents.tamu.pages.success', compact('kunjungan'));
        } catch (\Exception $e) {
            return redirect()->route('tamu.home')->with('error', 'Kunjungan tidak ditemukan.');
        }
    }

    public function event()
    {
        return view('contents.tamu.pages.tujuan');
    }

    public function Checkout($kunjunganId)
    {
        try {
            $kunjungan = Kunjungan::with('tamu')->findOrFail($kunjunganId);

            if ($kunjungan->is_checkout) {
                return view('contents.tamu.pages.checkout-success', compact('kunjungan'));
            }
            return view('contents.tamu.pages.checkout-confirm', compact('kunjungan'));
        } catch (\Exception $e) {
            return redirect()->route('tamu.home')->with('error', 'Maaf, terjadi kesalahan saat memproses checkout');
        }
    }

    public function storeCheckout($kunjunganId)
    {
        try {
            $kunjungan = Kunjungan::with('tamu')->findOrFail($kunjunganId);
            $kunjungan->update([
                'is_checkout' => true,
                'checkout_time' => now()
            ]);
            return redirect()->route('tamu.feedback', $kunjunganId)
                ->with('success', 'Waktu checkout Anda berhasil terkirim!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan. Silahkan coba lagi');
        }
    }

    public function feedback($kunjunganId)
    {
        $kunjungan = Kunjungan::with('tamu')->findOrFail($kunjunganId);
        return view(
            'contents.tamu.pages.feedback',
            [
                'kunjunganId' => $kunjunganId
            ]
        );
    }

    public function storeFeedback(Request $request, $kunjunganId)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data');
        }

        try {
            Feedback::create([
                'kunjungan_id' => $kunjunganId,
                'rating' => $request->rating,
                'komentar' => $request->komentar,
            ]);
            return redirect()->route('tamu.home')->with('success', 'Terima kasih atas penilaian Anda!');
        } catch (\Exception $e) {
            Log::error('Failed ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }
}
