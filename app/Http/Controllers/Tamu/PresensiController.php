<?php

namespace App\Http\Controllers\Tamu;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePresensiRequest;
use App\Models\Tamu;
use App\Models\Kunjungan;
use App\Models\KunjunganDetail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Twilio\Rest\Client;

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

    public function store(StorePresensiRequest $request): JsonResponse
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
                'waktu_keluar' => now()->addHours(2)->format('H:i:s'),
                'transportasi' => $request->transportasi ?? 'Tidak Diketahui',
                'status_validasi' => false,
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

            return response()->json([
                'status' => true,
                'message' => 'Presensi berhasil disimpan.',
                'data' => [
                    'kode_kunjungan' => $kunjungan->kunjungan_id,
                    'redirect_url' => route('tamu.sukses')
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
            ], 500);
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

    public function sukses()
    {
        return view('contents.tamu.pages.success');
    }

    public function event()
    {
        // Untuk sementara redirect ke halaman tujuan yang sama
        // Nanti bisa dibuat halaman khusus untuk tamu event
        return view('contents.tamu.pages.tujuan');
    }

    public function reminderCheckout()
    {
        $sid    = "ACb01d83636bbc05b4c8dadece07f83576";
        $token  = "a31fd634c31bacfd55ad99860e1aa2b3";
        $twilio = new Client($sid, $token);

        $message = $twilio->messages
            ->create(
                "whatsapp:+6289621530018",
                array(
                    "from" => "whatsapp:+14155238886",
                    "contentSid" => "HXb5b62575e6e4ff6129ad7c8efe1f983e",
                    "body" => "Your Message"
                )
            );
    }
}
