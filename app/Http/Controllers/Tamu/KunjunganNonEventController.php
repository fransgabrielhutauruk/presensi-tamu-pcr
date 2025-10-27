<?php

namespace App\Http\Controllers\Tamu;

use App\Models\Tamu;
use App\Models\Kunjungan;
use Illuminate\Http\Request;
use App\Models\KunjunganDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePresensiRequest;

class KunjunganNonEventController extends Controller
{
    public function tujuan()
    {
        return view('contents.tamu.pages.non-event.tujuan');
    }

    public function formPresensi(Request $request)
    {
        $tujuan = $request->get('tujuan');

        $validCategories = ['instansi', 'bisnis', 'ortu', 'calon_ortu', 'lainnya'];

        if (!$tujuan || !in_array($tujuan, $validCategories)) {
            return redirect()->route('tamu.non-event.tujuan')
                ->with('error', 'Silahkan pilih tujuan kunjungan yang sesuai.');
        }

        return view('contents.tamu.pages.non-event.form-presensi', compact('tujuan'));
    }

    public function storePresensi(StorePresensiRequest $request)
    {
        $data = [
            'nama_tamu' => $request->nama,
            'jenis_kelamin_tamu' => $request->jenis_kelamin,
            'email_tamu' => $request->email,
            'nomor_telepon_tamu' => $request->nomor_telepon,
        ];

        DB::beginTransaction();
        try {
            $tamu = Tamu::create($data);
            $kunjunganData = [
                'tamu_id' => $tamu->tamu_id,
                'kategori_tujuan' => $request->kategori_tujuan,
                'identitas' => 'tamu_luar',
                'waktu_keluar' => $request->waktu_keluar,
                'transportasi' => $request->transportasi,
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
            $kunjungan_id_hashed = encid($kunjungan->kunjungan_id);
            return redirect()->route('tamu.sukses', $kunjungan_id_hashed);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to store presensi: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan');
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
                    'pihak_dituju' => $request->pihak_dituju,
                    'keperluan' => $request->keperluan,
                ];
                break;

            case 'bisnis':
                $detailData = [
                    'instansi' => $request->instansi,
                    'bidang_usaha' => $request->bidang_usaha,
                    'skala_perusahaan' => $request->skala_perusahaan,
                    'jabatan' => $request->jabatan,
                    'pihak_dituju' => $request->pihak_dituju,
                    'keperluan' => $request->keperluan,
                ];
                break;

            case 'ortu':
                $detailData = [
                    'hubungan_dengan_mahasiswa' => $request->hubungan_dengan_mahasiswa,
                    'nama_mahasiswa' => $request->nama_mahasiswa,
                    'nim_mahasiswa' => $request->nim_mahasiswa,
                    'pihak_dituju' => $request->pihak_dituju,
                    'keperluan' => $request->keperluan,
                ];
                break;

            case 'calon_ortu':
                $detailData = [
                    'asal_sekolah' => $request->asal_sekolah,
                    'prodi_diminati' => $request->prodi_diminati,
                    'keperluan' => $request->keperluan,
                ];
                break;

            case 'lainnya':
                $detailData = [
                    'pihak_dituju' => $request->pihak_dituju,
                    'keperluan' => $request->keperluan,
                ];
                break;
        }

        return $detailData;
    }
}
