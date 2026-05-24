<?php

namespace App\Http\Controllers\Tamu;

use App\Enums\KategoriTujuanEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePresensiRequest;
use App\Models\Kunjungan;
use App\Models\KunjunganDetail;
use App\Models\MstOpsiKunjungan;
use App\Models\Tamu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class KunjunganNonEventController extends Controller
{
    public function tujuan()
    {
        return view('contents.tamu.pages.non-event.tujuan');
    }

    public function formPresensi(Request $request)
    {
        $tujuan = $request->get('tujuan');

        if (!$tujuan || !KategoriTujuanEnum::isValid($tujuan)) {
            return redirect()->route('tamu.non-event.tujuan')
                ->with('error', 'Silahkan pilih tujuan kunjungan yang sesuai.');
        }

        $options = MstOpsiKunjungan::getMultipleDropdownOptions([
            'pihak_dituju',
            'pihak_dituju_ortu',
            'prodi'
        ], app()->getLocale());

        return view('contents.tamu.pages.non-event.form-presensi', compact('tujuan', 'options'));
    }

    public function storePresensi(StorePresensiRequest $request)
    {
        try {
            $kunjungan = DB::transaction(function () use ($request) {
                $tamu = Tamu::create($this->buildTamuData($request));

                $kunjungan = Kunjungan::create($this->buildKunjunganData($request, $tamu->tamu_id));

                $this->storeDetailData($kunjungan->kunjungan_id, $this->prepareDetailData($request));

                return $kunjungan;
            });

            $kunjunganIdHashed = encid($kunjungan->kunjungan_id);

            return redirect()->route('tamu.sukses', $kunjunganIdHashed);
        } catch (Throwable $exception) {
            Log::error('Failed to store presensi: ' . $exception->getMessage());

            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan');
        }
    }

    private function prepareDetailData(Request $request): array
    {
        $fieldsByKategori = [
            KategoriTujuanEnum::INSTANSI->value => [
                'instansi',
                'jenis_instansi',
                'jabatan',
                'pihak_dituju',
                'keperluan',
            ],
            KategoriTujuanEnum::BISNIS->value => [
                'instansi',
                'kategori_instansi',
                'skala_instansi',
                'jabatan',
                'pihak_dituju',
                'keperluan',
            ],
            KategoriTujuanEnum::ORTU->value => [
                'hubungan_dengan_mahasiswa',
                'nama_mahasiswa',
                'prodi_mahasiswa',
                'nim_mahasiswa',
                'pihak_dituju',
                'keperluan',
            ],
            KategoriTujuanEnum::INFORMASI_KAMPUS->value => [
                'asal_sekolah',
                'prodi_diminati',
                'keperluan',
            ],
            KategoriTujuanEnum::LAINNYA->value => [
                'pihak_dituju',
                'keperluan',
            ],
        ];

        $selectedFields = $fieldsByKategori[$request->kategori_tujuan] ?? [];

        $detailData = [];
        foreach ($selectedFields as $field) {
            $detailData[$field] = $request->input($field);
        }

        return $detailData;
    }

    private function buildTamuData(Request $request): array
    {
        return [
            'nama_tamu' => $request->nama,
            'jenis_kelamin_tamu' => $request->jenis_kelamin,
            'email_tamu' => $request->email,
            'nomor_telepon_tamu' => $request->nomor_telepon,
        ];
    }

    private function buildKunjunganData(Request $request, int $tamuId): array
    {
        $estimasiDurasi = (int) $request->estimasi_durasi;
        $waktuKeluar = now()->addHours($estimasiDurasi);

        return [
            'tamu_id' => $tamuId,
            'kategori_tujuan' => $request->kategori_tujuan,
            'identitas' => 'non-civitas',
            'waktu_keluar' => $waktuKeluar->format('H:i:s'),
            'transportasi' => $request->transportasi,
            'status_validasi' => false,
            'is_checkout' => false,
        ];
    }

    private function storeDetailData(int $kunjunganId, array $detailData): void
    {
        $urutan = 1;

        foreach ($detailData as $key => $value) {
            if (empty($value)) {
                continue;
            }

            KunjunganDetail::create([
                'kunjungan_id' => $kunjunganId,
                'kunci' => $key,
                'nilai' => $value,
                'urutan' => $urutan++,
            ]);
        }
    }
}
