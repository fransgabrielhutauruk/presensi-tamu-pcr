<?php

namespace App\Http\Controllers\Tamu;

use App\Models\Tamu;
use App\Models\Event;
use App\Models\Civitas;
use App\Models\Kunjungan;
use Illuminate\Http\Request;
use App\Models\KunjunganDetail;
use App\Enums\KategoriTujuanEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Dimension\DmPegawai;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class KunjunganEventController extends Controller
{
    private const SOURCE_CIVITAS = 'civitas';
    private const SOURCE_DM_PEGAWAI = 'dm_pegawai';
    private const SOURCE_API_MAHASISWA = 'api_mahasiswa';
    private const SOURCE_NOT_FOUND = 'not_found';
    private const SOURCE_INVALID_IDENTIFIER = 'invalid_identifier';
    private const SOURCE_EXTERNAL_ERROR = 'external_error';

    public function listEvent(Request $request)
    {
        $currentDate = now()->format('Y-m-d');

        $events = Event::with('eventKategori')
            ->where('tanggal_event', '=', $currentDate)
            ->orderBy('waktu_mulai_event', 'asc')
            ->get();

        return view('contents.tamu.pages.event.list-event', compact('events'));
    }

    public function identitas(Request $request, $eventId)
    {
        try {
            $event = Event::with('eventKategori')->findOrFail(decid($eventId));
            $eventDate = $event->tanggal_event;
            $currentDate = now()->format('Y-m-d');
            if ($eventDate && $eventDate < $currentDate) {
                return redirect()->route('tamu.home')->with('error', 'Event ini sudah berakhir.');
            }
            return view('contents.tamu.pages.event.identitas', compact('event', 'eventId'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat halaman identitas' . $e->getMessage());
            return redirect()->route('tamu.home')->with('error', 'Event tidak ditemukan.');
        }
    }

    public function formPresensiNonCivitas(Request $request, $eventId)
    {
        try {
            $event = Event::with('eventKategori')->findOrFail(decid($eventId));
            $eventDate = $event->tanggal_event;
            $currentDate = now()->format('Y-m-d');
            if ($eventDate && $eventDate < $currentDate) {
                return redirect()->route('tamu.home')->with('warning', 'Event ini sudah berakhir.');
            }

            return view('contents.tamu.pages.event.form-presensi', compact('event', 'eventId'));
        } catch (\Exception $e) {
            return redirect()->route('tamu.home')->with('warning', 'Event tidak ditemukan.');
        }
    }

    public function storePresensiNonCivitas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required',
            'nama' => 'required',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'nomor_telepon' => 'required|max:20',
            'email' => 'required|email',
            'institusi' => 'required',
            'jabatan' => 'required',
            'jumlah_rombongan' => 'required|integer|min:1|max:50',
            'transportasi' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Mohon periksa kembali data yang Anda masukkan.');
        }

        try {
            $event = Event::findOrFail(decid($request->event_id));
            DB::beginTransaction();
            $tamuData = [
                'nama_tamu' => $request->nama,
                'jenis_kelamin_tamu' => $request->jenis_kelamin,
                'nomor_telepon_tamu' => $request->nomor_telepon,
                'email_tamu' => $request->email,
            ];
            $tamu = Tamu::create($tamuData);
            $kunjunganData = [
                'tamu_id' => $tamu->tamu_id,
                'kategori_tujuan' => KategoriTujuanEnum::EVENT->value,
                'identitas' => 'non-civitas',
                'event_id' => $event->event_id,
                'waktu_keluar' => $event->waktu_selesai_event,
                'transportasi' => $request->transportasi,
                'status_validasi' => false,
                'is_checkout' => false,
            ];
            $kunjungan = Kunjungan::create($kunjunganData);
            $detailData = [
                'institusi' => $request->institusi,
                'jabatan' => $request->jabatan,
                'jumlah_rombongan' => $request->jumlah_rombongan,
            ];
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

            $kunjunganIdHashed = encid($kunjungan->kunjungan_id);
            DB::commit();
            return redirect()->route('tamu.sukses', $kunjunganIdHashed);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan presensi luar: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan. Silahkan coba lagi.');
        }
    }

    public function formPresensiCivitas(Request $request, $eventId)
    {
        try {
            $event = Event::with('eventKategori')->findOrFail(decid($eventId));
            $eventDate = $event->tanggal_event;
            $currentDate = now()->format('Y-m-d');
            if ($eventDate && $eventDate < $currentDate) {
                return redirect()->route('tamu.home')->with('error', 'Event ini sudah berakhir.');
            }
            return view('contents.tamu.pages.event.form-presensi-civitas', compact('event', 'eventId'));
        } catch (\Exception $e) {
            return redirect()->route('tamu.home')->with('error', 'Event tidak ditemukan.');
        }
    }

    public function storePresensiCivitas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required',
            'nim_nip' => 'required|string|max:20',
            'nama' => 'required',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'nomor_telepon' => 'required|numeric',
            'email' => 'required|email',
            'jabatan' => 'required'
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Mohon periksa kembali data yang Anda masukkan.');
        }

        try {
            $event = Event::findOrFail(decid($request->event_id));
            $nimNip = $this->normalizeIdentifier((string) $request->nim_nip);
            $identifierType = $this->resolveIdentifierType($nimNip);

            if ($identifierType === null) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Format NIM/NIP tidak valid.');
            }

            DB::beginTransaction();

            $civitas = $this->findCivitasByIdentifier($nimNip, $identifierType);

            if (!$civitas) {
                $civitasData = [
                    'nama_civitas' => $request->nama,
                    'nim' => $identifierType === 'nim' ? $nimNip : null,
                    'nip' => $identifierType === 'nip' ? $nimNip : null,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'nomor_telepon' => $request->nomor_telepon,
                    'email' => $request->email,
                ];
                $civitas = Civitas::create($civitasData);
            }

            $kunjunganData = [
                'civitas_id' => $civitas->civitas_id,
                'kategori_tujuan' => KategoriTujuanEnum::EVENT->value,
                'identitas' => 'civitas',
                'event_id' => $event->event_id,
                'waktu_keluar' => $event->waktu_selesai_event,
                'status_validasi' => false,
                'is_checkout' => false,
            ];
            $kunjungan = Kunjungan::create($kunjunganData);

            KunjunganDetail::create([
                'kunjungan_id' => $kunjungan->kunjungan_id,
                'kunci' => 'jabatan',
                'nilai' => $request->jabatan,
                'urutan' => 1,
            ]);

            $kunjunganIdHashed = encid($kunjungan->kunjungan_id);
            DB::commit();
            return redirect()->route('tamu.sukses', $kunjunganIdHashed);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Gagal menyimpan presensi event civitas: ' . $th->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan. Silahkan coba lagi.');
        }
    }

    /**
     * Check civitas data by NIM/NIP
     */
    public function checkCivitasData(Request $request): JsonResponse
    {
        try {
            $nimNip = $this->normalizeIdentifier((string) $request->input('nim_nip'));
            $identifierType = $this->resolveIdentifierType($nimNip);

            if ($identifierType === null) {
                return response()->json([
                    'status' => false,
                    'source' => self::SOURCE_INVALID_IDENTIFIER,
                    'message' => 'Format NIM/NIP tidak valid. Gunakan 6 digit NIP atau 10 digit NIM.'
                ], 400);
            }

            $civitas = $this->findCivitasByIdentifier($nimNip, $identifierType);

            if ($civitas) {
                return response()->json([
                    'status' => true,
                    'source' => self::SOURCE_CIVITAS,
                    'identifier_type' => $identifierType,
                    'autofilled_fields' => ['nama', 'jenis_kelamin', 'nomor_telepon', 'email'],
                    'data' => [
                        'nama' => $civitas->nama_civitas,
                        'jenis_kelamin' => $civitas->jenis_kelamin,
                        'nomor_telepon' => $civitas->nomor_telepon,
                        'email' => $civitas->email,
                    ]
                ]);
            }

            return response()->json([
                'status' => false,
                'source' => self::SOURCE_NOT_FOUND,
                'identifier_type' => $identifierType,
                'message' => 'Data tidak ditemukan di database lokal',
                'autofilled_fields' => [],
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking civitas data: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat memeriksa data'
            ], 500);
        }
    }

    /**
     * Fetch data from DmPegawai or API Mahasiswa
     */
    public function fetchExternalData(Request $request): JsonResponse
    {
        try {
            $nimNip = $this->normalizeIdentifier((string) $request->input('nim_nip'));
            $identifierType = $this->resolveIdentifierType($nimNip);

            if ($identifierType === null) {
                return response()->json([
                    'status' => false,
                    'source' => self::SOURCE_INVALID_IDENTIFIER,
                    'message' => 'Format NIM/NIP tidak valid. Gunakan 6 digit NIP atau 10 digit NIM.'
                ], 400);
            }

            $lookupResult = $this->fetchExternalByIdentifier($nimNip, $identifierType);
            if ($lookupResult['status']) {
                return response()->json($lookupResult);
            }

            return response()->json($lookupResult, $lookupResult['http_code'] ?? 404);
        } catch (\Exception $e) {
            Log::error('Error fetching external data: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'source' => self::SOURCE_EXTERNAL_ERROR,
                'message' => 'Terjadi kesalahan saat mengambil data'
            ], 500);
        }
    }

    private function normalizeIdentifier(string $nimNip): string
    {
        return preg_replace('/\D/', '', $nimNip);
    }

    private function resolveIdentifierType(string $nimNip): ?string
    {
        if (!preg_match('/^(\d{6}|\d{10})$/', $nimNip)) {
            return null;
        }

        return strlen($nimNip) === 6 ? 'nip' : 'nim';
    }

    private function findCivitasByIdentifier(string $nimNip, string $identifierType): ?Civitas
    {
        return Civitas::where($identifierType, $nimNip)->first();
    }

    private function fetchExternalByIdentifier(string $nimNip, string $identifierType): array
    {
        if ($identifierType === 'nip') {
            return $this->fetchPegawaiByNip($nimNip);
        }

        return $this->fetchMahasiswaByNim($nimNip);
    }

    private function fetchPegawaiByNip(string $nip): array
    {
        $pegawai = DmPegawai::where('nip', $nip)->first();

        if (!$pegawai) {
            return [
                'status' => false,
                'source' => self::SOURCE_NOT_FOUND,
                'identifier_type' => 'nip',
                'autofilled_fields' => [],
                'message' => 'Gagal mengambil data. Lengkapi data yang belum terisi.',
                'http_code' => 404,
            ];
        }

        return [
            'status' => true,
            'source' => self::SOURCE_DM_PEGAWAI,
            'identifier_type' => 'nip',
            'autofilled_fields' => ['nama', 'email'],
            'data' => [
                'nama' => $pegawai->nama ?? null,
                'email' => $pegawai->email ?? null,
            ],
        ];
    }

    private function fetchMahasiswaByNim(string $nim): array
    {
        $apiUrl = (string) config('services.mahasiswa_api.url');
        $apiKey = (string) config('services.mahasiswa_api.key');
        $apiCollection = config('services.mahasiswa_api.collection');
        $apiTimeout = (int) config('services.mahasiswa_api.timeout', 60);

        if (empty($apiUrl) || empty($apiKey)) {
            Log::error('Mahasiswa API config missing', [
                'url_set' => !empty($apiUrl),
                'key_set' => !empty($apiKey),
            ]);

            return [
                'status' => false,
                'source' => self::SOURCE_EXTERNAL_ERROR,
                'identifier_type' => 'nim',
                'message' => 'Konfigurasi API mahasiswa belum lengkap',
                'http_code' => 500,
            ];
        }

        $queryParams = ['nim' => $nim];
        if (!empty($apiCollection)) {
            $queryParams['collection'] = $apiCollection;
        }

        $response = Http::timeout($apiTimeout)
            ->withHeaders([
                'apikey' => $apiKey,
                'Accept' => 'application/json',
            ])
            ->get($apiUrl, $queryParams);

        if (!$response->successful()) {
            Log::error('API Mahasiswa request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'status' => false,
                'source' => self::SOURCE_EXTERNAL_ERROR,
                'identifier_type' => 'nim',
                'http_code' => 500,
            ];
        }

        $mahasiswa = $this->extractMahasiswaItem($response->json());
        if (!$mahasiswa) {
            return [
                'status' => false,
                'source' => self::SOURCE_NOT_FOUND,
                'identifier_type' => 'nim',
                'autofilled_fields' => [],
                'message' => 'Data mahasiswa dengan NIM tersebut tidak ditemukan',
                'http_code' => 404,
            ];
        }

        $mappedMahasiswa = $this->mapMahasiswaData($mahasiswa);

        return [
            'status' => true,
            'source' => self::SOURCE_API_MAHASISWA,
            'identifier_type' => 'nim',
            'autofilled_fields' => ['nama', 'email'],
            'data' => [
                'nama' => $mappedMahasiswa['nama'],
                'email' => $mappedMahasiswa['email'],
            ],
            'message' => 'Data ditemukan. Lengkapi data yang belum terisi.',
        ];
    }

    private function isValidIdentifier(string $nimNip): bool
    {
        return $this->resolveIdentifierType($nimNip) !== null;
    }

    private function extractMahasiswaItem($responseData): ?array
    {
        if (!is_array($responseData)) {
            return null;
        }

        $candidates = [
            $responseData,
            $responseData['data'] ?? null,
            $responseData['result'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }

            if (isset($candidate['items']) && is_array($candidate['items']) && !empty($candidate['items'])) {
                return (array) $candidate['items'][0];
            }

            if (array_key_exists('nama', $candidate) || array_key_exists('email', $candidate) || array_key_exists('nama_mahasiswa', $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function mapMahasiswaData(array $data): array
    {
        $nama = $data['nama'] ?? $data['nama_mahasiswa'] ?? $data['name'] ?? null;
        $email = $data['email'] ?? $data['email_pcr'] ?? $data['email_mahasiswa'] ?? null;

        if (is_string($nama) && $nama !== '') {
            $nama = mb_convert_case(mb_strtolower(trim($nama), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        }

        return [
            'nama' => $nama,
            'email' => $email,
        ];
    }
}