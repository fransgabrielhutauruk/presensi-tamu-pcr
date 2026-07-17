<?php

namespace App\Http\Controllers\Tamu;

use App\Enums\KategoriTujuanEnum;
use App\Http\Controllers\Controller;
use App\Models\Civitas;
use App\Models\Dimension\DmPegawai;
use App\Models\Event;
use App\Models\Kunjungan;
use App\Models\KunjunganDetail;
use App\Models\MstOpsiKunjungan;
use App\Models\Tamu;
use App\Services\CypressTestingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class KunjunganEventController extends Controller
{
    private const SOURCE_CIVITAS = 'civitas';

    private const SOURCE_DM_PEGAWAI = 'dm_pegawai';

    private const SOURCE_API_MAHASISWA = 'api_mahasiswa';

    private const SOURCE_NOT_FOUND = 'not_found';

    private const SOURCE_INVALID_IDENTIFIER = 'invalid_identifier';

    private const SOURCE_EXTERNAL_ERROR = 'external_error';

    private CypressTestingService $cypressTestingService;

    public function __construct(CypressTestingService $cypressTestingService)
    {
        $this->cypressTestingService = $cypressTestingService;
    }

    public function listEvent(Request $request)
    {
        if ($this->cypressTestingService->isMockEnabled($request)) {
            $this->cypressTestingService->ensureEventFixturesForToday();
        }

        $currentDate = now()->format('Y-m-d');

        $query = Event::query()
            ->where(function ($q) use ($currentDate) {
                $q->whereDate('tanggal_event', $currentDate)
                    ->whereNull('tanggal_selesai_event');
            })
            ->orWhere(function ($q) use ($currentDate) {
                $q->whereNotNull('tanggal_selesai_event')
                    ->whereDate('tanggal_event', '<=', $currentDate)
                    ->whereDate('tanggal_selesai_event', '>=', $currentDate);
            });

        if ($request->has('kategori_lokasi') && in_array($request->kategori_lokasi, ['dalam_kampus', 'luar_kampus'])) {
            $query->where('kategori_lokasi', $request->kategori_lokasi);
        }

        $events = $query->orderBy('waktu_mulai_event', 'asc')->get();
        $kategoriLokasi = $request->get('kategori_lokasi');

        return view('contents.tamu.pages.event.list-event', compact('events', 'kategoriLokasi'));
    }

    public function identitas(Request $request, $eventId)
    {
        try {
            $event = $this->findEventOrFail($eventId);

            if ($this->isEventExpired($event)) {
                return redirect()->route('tamu.home')->with('error', 'Event ini sudah berakhir.');
            }

            return view('contents.tamu.pages.event.identitas', compact('event', 'eventId'));
        } catch (Throwable $exception) {
            Log::error('Gagal memuat halaman identitas: '.$exception->getMessage());

            return redirect()->route('tamu.home')->with('error', 'Event tidak ditemukan.');
        }
    }

    public function formPresensiNonCivitas(Request $request, $eventId)
    {
        try {
            $event = $this->findEventOrFail($eventId);

            if ($this->isEventExpired($event)) {
                return redirect()->route('tamu.home')->with('warning', 'Event ini sudah berakhir.');
            }

            $prodiOptions = [];
            if ($event->jenis_kegiatan === 'pmb') {
                $prodiOptions = MstOpsiKunjungan::getDropdownOptions('prodi', app()->getLocale());
            }

            return view('contents.tamu.pages.event.form-presensi', compact('event', 'eventId', 'prodiOptions'));
        } catch (Throwable $exception) {
            return redirect()->route('tamu.home')->with('warning', 'Event tidak ditemukan.');
        }
    }

    public function storePresensiNonCivitas(Request $request)
    {
        $rules = [
            'event_id' => 'required',
            'nama' => 'required',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'nomor_telepon' => 'required|max:20',
            'email' => 'required|email',
            'instansi' => 'required',
            'peran' => 'required',
        ];

        try {
            $event = $this->findEventByHashedIdOrFail($request->event_id);

            if ($event->jenis_kegiatan === 'pmb') {
                $rules['minat_masuk_pcr'] = 'required|in:Ya,Tidak,Ragu-ragu';
                $rules['prodi_diminati'] = 'required|array|min:1';
                $rules['prodi_diminati.*'] = 'string|max:255';
            } else {
                $rules['transportasi'] = 'required';
            }
        } catch (Throwable $exception) {
            return redirect()->back()->withInput()->with('error', 'Event tidak ditemukan.');
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Mohon periksa kembali data yang Anda masukkan.');
        }

        try {
            $kunjungan = DB::transaction(function () use ($request, $event) {
                $tamu = Tamu::create($this->buildTamuData($request));

                $kunjungan = Kunjungan::create($this->buildNonCivitasKunjunganData($request, $event, $tamu->tamu_id));

                $this->storeKunjunganDetails($kunjungan->kunjungan_id, $this->buildNonCivitasDetailData($request, $event));

                return $kunjungan;
            });

            $kunjunganIdHashed = encid($kunjungan->kunjungan_id);

            return redirect()->route('tamu.sukses', $kunjunganIdHashed);
        } catch (Throwable $exception) {
            Log::error('Gagal menyimpan presensi luar: '.$exception->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan. Silahkan coba lagi.');
        }
    }

    public function formPresensiCivitas(Request $request, $eventId)
    {
        try {
            $event = $this->findEventOrFail($eventId);

            if ($this->isEventExpired($event)) {
                return redirect()->route('tamu.home')->with('error', 'Event ini sudah berakhir.');
            }

            return view('contents.tamu.pages.event.form-presensi-civitas', compact('event', 'eventId'));
        } catch (Throwable $exception) {
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
            'peran' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Mohon periksa kembali data yang Anda masukkan.');
        }

        try {
            $event = $this->findEventByHashedIdOrFail($request->event_id);
            $nimNip = trim((string) $request->nim_nip);
            $identifierType = $this->resolveIdentifierType($nimNip);

            if ($identifierType === null) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Format NIM/NIP tidak valid.');
            }

            $kunjungan = DB::transaction(function () use ($request, $event, $nimNip, $identifierType) {
                $civitas = $this->findOrCreateCivitas($request, $nimNip, $identifierType);

                $kunjungan = Kunjungan::create($this->buildCivitasKunjunganData($event, $civitas->civitas_id));

                $this->storeKunjunganDetails($kunjungan->kunjungan_id, [
                    'peran' => $request->peran,
                ]);

                return $kunjungan;
            });

            $kunjunganIdHashed = encid($kunjungan->kunjungan_id);

            return redirect()->route('tamu.sukses', $kunjunganIdHashed);
        } catch (Throwable $exception) {
            Log::error('Gagal menyimpan presensi event civitas: '.$exception->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan. Silahkan coba lagi.');
        }
    }

    public function checkCivitasData(Request $request): JsonResponse
    {
        try {
            $nimNip = trim($request->input('nim_nip'));
            $identifierType = $this->resolveIdentifierType($nimNip);

            if ($identifierType === null) {
                return $this->invalidIdentifierResponse();
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
                    ],
                ]);
            }

            return response()->json([
                'status' => false,
                'source' => self::SOURCE_NOT_FOUND,
                'identifier_type' => $identifierType,
                'message' => 'Data tidak ditemukan. Silahkan isi data.',
                'autofilled_fields' => [],
            ]);
        } catch (Throwable $exception) {
            Log::error('Error checking civitas data: '.$exception->getMessage());

            return $this->jsonServerErrorResponse('Terjadi kesalahan saat memeriksa data');
        }
    }

    public function fetchExternalData(Request $request): JsonResponse
    {
        try {
            $nimNip = trim((string) $request->input('nim_nip'));
            $identifierType = $this->resolveIdentifierType($nimNip);

            if ($identifierType === null) {
                return $this->invalidIdentifierResponse();
            }

            if ($this->cypressTestingService->shouldUseMockMahasiswaApi($request, trim((string) $request->input('nim_nip')))) {
                return response()->json([
                    'status' => true,
                    'source' => self::SOURCE_API_MAHASISWA,
                    'identifier_type' => 'nim',
                    'autofilled_fields' => ['nama', 'email'],
                    'data' => [
                        'nama' => 'Sri Wahyuni',
                        'email' => 'sri@mahasiswa.pcr.ac.id',
                    ],
                    'message' => 'Data ditemukan. Lengkapi data yang belum terisi.',
                ]);
            }

            $lookupResult = $this->fetchExternalByIdentifier($nimNip, $identifierType);
            if ($lookupResult['status']) {
                return response()->json($lookupResult);
            }

            return response()->json($lookupResult, $lookupResult['http_code'] ?? 404);
        } catch (Throwable $exception) {
            Log::error('Error fetching external data: '.$exception->getMessage());

            return $this->jsonServerErrorResponse('Terjadi kesalahan saat mengambil data. Silahkan isi seluruh data di bawah.', self::SOURCE_EXTERNAL_ERROR);
        }
    }

    private function resolveIdentifierType(string $nimNip): ?string
    {
        if (! preg_match('/^(\d{6}|\d{10})$/', $nimNip)) {
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

        if (! $pegawai) {
            return [
                'status' => false,
                'source' => self::SOURCE_NOT_FOUND,
                'identifier_type' => 'nip',
                'autofilled_fields' => [],
                'message' => 'Data tidak ditemukan. Silahkan isi seluruh data di bawah.',
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
                'url_set' => ! empty($apiUrl),
                'key_set' => ! empty($apiKey),
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
        if (! empty($apiCollection)) {
            $queryParams['collection'] = $apiCollection;
        }

        $response = Http::timeout($apiTimeout)
            ->withHeaders([
                'apikey' => $apiKey,
                'Accept' => 'application/json',
            ])
            ->get($apiUrl, $queryParams);

        if (! $response->successful()) {
            Log::error('API Mahasiswa request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'status' => false,
                'source' => self::SOURCE_EXTERNAL_ERROR,
                'identifier_type' => 'nim',
                'http_code' => 500,
            ];
        }

        $responseData = $response->json();
        $items = is_array($responseData) ? ($responseData['items'] ?? null) : null;

        if (! is_array($items) || empty($items) || ! is_array($items[0] ?? null)) {
            return [
                'status' => false,
                'source' => self::SOURCE_NOT_FOUND,
                'identifier_type' => 'nim',
                'autofilled_fields' => [],
                'message' => 'Data tidak ditemukan. Silahkan isi seluruh data di bawah.',
                'http_code' => 404,
            ];
        }

        $mahasiswa = $items[0];

        $nama = $mahasiswa['nama'] ?? null;
        $email = $mahasiswa['email'] ?? null;

        if (is_string($nama) && $nama !== '') {
            $nama = mb_convert_case(mb_strtolower(trim($nama), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        }

        return [
            'status' => true,
            'source' => self::SOURCE_API_MAHASISWA,
            'identifier_type' => 'nim',
            'autofilled_fields' => ['nama', 'email'],
            'data' => [
                'nama' => $nama,
                'email' => $email,
            ],
            'message' => 'Data ditemukan. Lengkapi data yang belum terisi.',
        ];
    }

    private function findEventOrFail(string $encodedEventId): Event
    {
        return Event::findOrFail(decid($encodedEventId));
    }

    private function findEventByHashedIdOrFail(string $encodedEventId): Event
    {
        return Event::findOrFail(decid($encodedEventId));
    }

    private function isEventExpired(Event $event): bool
    {
        $currentDate = now()->format('Y-m-d');

        if (! empty($event->tanggal_selesai_event)) {
            return $event->tanggal_selesai_event < $currentDate;
        }

        return $event->tanggal_event && $event->tanggal_event < $currentDate;
    }

    private function buildTamuData(Request $request): array
    {
        return [
            'nama_tamu' => $request->nama,
            'jenis_kelamin_tamu' => $request->jenis_kelamin,
            'nomor_telepon_tamu' => $request->nomor_telepon,
            'email_tamu' => $request->email,
        ];
    }

    private function buildNonCivitasKunjunganData(Request $request, Event $event, int $tamuId): array
    {
        return [
            'tamu_id' => $tamuId,
            'kategori_tujuan' => KategoriTujuanEnum::EVENT->value,
            'identitas' => 'non-civitas',
            'event_id' => $event->event_id,
            'waktu_keluar' => $event->waktu_selesai_event,
            'transportasi' => $request->transportasi,
            'status_validasi' => false,
            'is_checkout' => false,
        ];
    }

    private function buildNonCivitasDetailData(Request $request, Event $event): array
    {
        $data = [
            'instansi' => $request->instansi,
            'peran' => $request->peran,
        ];

        if ($event->jenis_kegiatan === 'pmb') {
            $data['minat_masuk_pcr'] = $request->minat_masuk_pcr;
            $data['prodi_diminati'] = implode(', ', (array) $request->prodi_diminati);
        }

        return $data;
    }

    private function buildCivitasKunjunganData(Event $event, int $civitasId): array
    {
        return [
            'civitas_id' => $civitasId,
            'kategori_tujuan' => KategoriTujuanEnum::EVENT->value,
            'identitas' => 'civitas',
            'event_id' => $event->event_id,
            'waktu_keluar' => $event->waktu_selesai_event,
            'status_validasi' => false,
            'is_checkout' => false,
        ];
    }

    private function findOrCreateCivitas(Request $request, string $nimNip, string $identifierType): Civitas
    {
        $civitas = $this->findCivitasByIdentifier($nimNip, $identifierType);

        if ($civitas) {
            return $civitas;
        }

        return Civitas::create([
            'nama_civitas' => $request->nama,
            'nim' => $identifierType === 'nim' ? $nimNip : null,
            'nip' => $identifierType === 'nip' ? $nimNip : null,
            'jenis_kelamin' => $request->jenis_kelamin,
            'nomor_telepon' => $request->nomor_telepon,
            'email' => $request->email,
        ]);
    }

    private function storeKunjunganDetails(int $kunjunganId, array $detailData): void
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

    private function invalidIdentifierResponse(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'source' => self::SOURCE_INVALID_IDENTIFIER,
            'message' => 'Format NIM/NIP tidak valid. Gunakan 6 digit NIP atau 10 digit NIM.',
        ], 400);
    }

    private function jsonServerErrorResponse(string $message, ?string $source = null): JsonResponse
    {
        $payload = [
            'status' => false,
            'message' => $message,
        ];

        if ($source) {
            $payload['source'] = $source;
        }

        return response()->json($payload, 500);
    }
}
