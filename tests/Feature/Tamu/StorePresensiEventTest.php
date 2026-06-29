<?php

use App\Models\Civitas;
use App\Models\Kunjungan;
use App\Models\Tamu;
use Database\Factories\CivitasFactory;
use Database\Factories\DmPegawaiFactory;
use Database\Factories\EventFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('BBT-3 Pengisian Presensi Event', function () {
    it('menyimpan presensi tamu event non-civitas saat semua field wajib valid lalu menampilkan halaman sukses', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $event = EventFactory::new()->create([
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '09:00:00',
            'waktu_selesai_event' => '12:00:00',
        ]);

        $payload = [
            'event_id' => encid((string) $event->event_id),
            'nama' => 'Siti Aminah',
            'jenis_kelamin' => 'Perempuan',
            'nomor_telepon' => '081298765432',
            'email' => 'siti@example.com',
            'instansi' => 'PT Inovasi Nusantara',
            'peran' => 'Peserta',
            'transportasi' => 'Mobil',
        ];

        // Action
        $response = $this->post(route('tamu.event.store-presensi-non-civitas'), $payload);

        $redirectUrl = $response->headers->get('Location');
        $kunjungan = Kunjungan::query()
            ->with(['tamu', 'details'])
            ->where('event_id', $event->event_id)
            ->where('identitas', 'non-civitas')
            ->latest('kunjungan_id')
            ->first();

        // Assertion
        $response->assertRedirect();
        expect($redirectUrl)->not->toBeEmpty();
        expect($kunjungan)->not->toBeNull();

        $this->assertDatabaseHas('tamu', [
            'tamu_id' => $kunjungan->tamu_id,
            'nama_tamu' => 'Siti Aminah',
            'jenis_kelamin_tamu' => 'Perempuan',
            'email_tamu' => 'siti@example.com',
            'nomor_telepon_tamu' => '081298765432',
        ]);

        $this->assertDatabaseHas('kunjungan', [
            'kunjungan_id' => $kunjungan->kunjungan_id,
            'tamu_id' => $kunjungan->tamu_id,
            'event_id' => $event->event_id,
            'identitas' => 'non-civitas',
            'kategori_tujuan' => 'event',
            'transportasi' => 'Mobil',
            'status_validasi' => false,
            'is_checkout' => false,
        ]);

        $this->assertDatabaseHas('kunjungan_detail', [
            'kunjungan_id' => $kunjungan->kunjungan_id,
            'kunci' => 'instansi',
            'nilai' => 'PT Inovasi Nusantara',
        ]);

        $this->assertDatabaseHas('kunjungan_detail', [
            'kunjungan_id' => $kunjungan->kunjungan_id,
            'kunci' => 'peran',
            'nilai' => 'Peserta',
        ]);

        $this->get($redirectUrl)
            ->assertOk()
            ->assertSee(__('visitor.registration_complete'));
    });

    it('menyimpan presensi civitas saat nim ditemukan di tabel civitas', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $event = EventFactory::new()->create([
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '09:00:00',
            'waktu_selesai_event' => '12:00:00',
        ]);

        $civitas = CivitasFactory::new()->create([
            'nim' => '2312012345',
            'nama_civitas' => 'Andi Pratama',
            'jenis_kelamin' => 'Laki-laki',
            'nomor_telepon' => '081211112222',
            'email' => 'andi@pcr.ac.id',
        ]);

        $checkResponse = $this->postJson(route('tamu.event.check-civitas'), [
            'nim_nip' => '2312012345',
        ]);

        $payload = [
            'event_id' => encid((string) $event->event_id),
            'nim_nip' => '2312012345',
            'nama' => 'Andi Pratama',
            'jenis_kelamin' => 'Laki-laki',
            'nomor_telepon' => '081211112222',
            'email' => 'andi@pcr.ac.id',
            'peran' => 'Peserta',
        ];

        // Action
        $response = $this->post(route('tamu.event.store-presensi-civitas'), $payload);

        $redirectUrl = $response->headers->get('Location');
        $kunjungan = Kunjungan::query()
            ->with(['civitas', 'details'])
            ->where('event_id', $event->event_id)
            ->where('identitas', 'civitas')
            ->latest('kunjungan_id')
            ->first();

        // Assertion
        $checkResponse->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('source', 'civitas')
            ->assertJsonPath('identifier_type', 'nim');

        $response->assertRedirect();
        expect($redirectUrl)->not->toBeEmpty();
        expect($kunjungan)->not->toBeNull();

        $this->assertDatabaseCount('tamu', 0);
        $this->assertDatabaseCount('civitas', 1);

        $this->assertDatabaseHas('kunjungan', [
            'kunjungan_id' => $kunjungan->kunjungan_id,
            'civitas_id' => $civitas->civitas_id,
            'event_id' => $event->event_id,
            'identitas' => 'civitas',
            'kategori_tujuan' => 'event',
            'status_validasi' => false,
            'is_checkout' => false,
        ]);

        $this->assertDatabaseHas('kunjungan_detail', [
            'kunjungan_id' => $kunjungan->kunjungan_id,
            'kunci' => 'peran',
            'nilai' => 'Peserta',
        ]);

        $this->get($redirectUrl)
            ->assertOk()
            ->assertSee(__('visitor.registration_complete'));
    });

    it('menyimpan presensi civitas saat nip didapat dari dm pegawai', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $event = EventFactory::new()->create([
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '09:00:00',
            'waktu_selesai_event' => '12:00:00',
        ]);

        $pegawai = DmPegawaiFactory::new()->create([
            'nip' => '123456',
            'nama' => 'Bambang Setiawan',
            'email' => 'bambang@pcr.ac.id',
        ]);

        $fetchResponse = $this->postJson(route('tamu.event.fetch-external-data'), [
            'nim_nip' => '123456',
        ]);

        $payload = [
            'event_id' => encid((string) $event->event_id),
            'nim_nip' => '123456',
            'nama' => $pegawai->nama,
            'jenis_kelamin' => 'Laki-laki',
            'nomor_telepon' => '081233334444',
            'email' => $pegawai->email,
            'peran' => 'Pengisi Acara / Narasumber',
        ];

        // Action
        $response = $this->post(route('tamu.event.store-presensi-civitas'), $payload);

        $redirectUrl = $response->headers->get('Location');
        $kunjungan = Kunjungan::query()
            ->with(['civitas', 'details'])
            ->where('event_id', $event->event_id)
            ->where('identitas', 'civitas')
            ->latest('kunjungan_id')
            ->first();

        // Assertion
        $fetchResponse->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('source', 'dm_pegawai')
            ->assertJsonPath('identifier_type', 'nip')
            ->assertJsonPath('data.nama', 'Bambang Setiawan')
            ->assertJsonPath('data.email', 'bambang@pcr.ac.id');

        $response->assertRedirect();
        expect($redirectUrl)->not->toBeEmpty();
        expect($kunjungan)->not->toBeNull();

        $this->assertDatabaseCount('tamu', 0);
        $this->assertDatabaseCount('civitas', 1);

        $this->assertDatabaseHas('civitas', [
            'civitas_id' => $kunjungan->civitas_id,
            'nip' => '123456',
            'nama_civitas' => 'Bambang Setiawan',
            'email' => 'bambang@pcr.ac.id',
        ]);

        $this->assertDatabaseHas('kunjungan', [
            'kunjungan_id' => $kunjungan->kunjungan_id,
            'civitas_id' => $kunjungan->civitas_id,
            'event_id' => $event->event_id,
            'identitas' => 'civitas',
            'kategori_tujuan' => 'event',
            'status_validasi' => false,
            'is_checkout' => false,
        ]);

        $this->assertDatabaseHas('kunjungan_detail', [
            'kunjungan_id' => $kunjungan->kunjungan_id,
            'kunci' => 'peran',
            'nilai' => 'Pengisi Acara / Narasumber',
        ]);

        $this->get($redirectUrl)
            ->assertOk()
            ->assertSee(__('visitor.registration_complete'));
    });

    it('menyimpan presensi civitas saat nim didapat dari api mahasiswa', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        config([
            'services.mahasiswa_api.url' => 'https://api-mahasiswa.test/data',
            'services.mahasiswa_api.key' => 'test-key',
            'services.mahasiswa_api.collection' => 'mahasiswa',
            'services.mahasiswa_api.timeout' => 30,
        ]);

        Http::fake([
            'https://api-mahasiswa.test/data*' => Http::response([
                'items' => [
                    [
                        'nama' => 'SRI WAHYUNI',
                        'email' => 'sri@mahasiswa.pcr.ac.id',
                    ],
                ],
            ], 200),
        ]);

        $event = EventFactory::new()->create([
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '09:00:00',
            'waktu_selesai_event' => '12:00:00',
        ]);

        $fetchResponse = $this->postJson(route('tamu.event.fetch-external-data'), [
            'nim_nip' => '2312098765',
        ]);
        
        $payload = [
            'event_id' => encid((string) $event->event_id),
            'nim_nip' => '2312098765',
            'nama' => 'Sri Wahyuni',
            'jenis_kelamin' => 'Perempuan',
            'nomor_telepon' => '081255556666',
            'email' => 'sri@mahasiswa.pcr.ac.id',
            'peran' => 'Peserta',
        ];

        // Action
        $response = $this->post(route('tamu.event.store-presensi-civitas'), $payload);

        $redirectUrl = $response->headers->get('Location');
        $kunjungan = Kunjungan::query()
            ->with(['civitas', 'details'])
            ->where('event_id', $event->event_id)
            ->where('identitas', 'civitas')
            ->latest('kunjungan_id')
            ->first();

        // Assertion
        $fetchResponse->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('source', 'api_mahasiswa')
            ->assertJsonPath('identifier_type', 'nim')
            ->assertJsonPath('data.nama', 'Sri Wahyuni')
            ->assertJsonPath('data.email', 'sri@mahasiswa.pcr.ac.id');

        Http::assertSentCount(1);

        $response->assertRedirect();
        expect($redirectUrl)->not->toBeEmpty();
        expect($kunjungan)->not->toBeNull();

        $this->assertDatabaseCount('tamu', 0);
        $this->assertDatabaseCount('civitas', 1);

        $this->assertDatabaseHas('civitas', [
            'civitas_id' => $kunjungan->civitas_id,
            'nim' => '2312098765',
            'nama_civitas' => 'Sri Wahyuni',
            'email' => 'sri@mahasiswa.pcr.ac.id',
        ]);

        $this->assertDatabaseHas('kunjungan', [
            'kunjungan_id' => $kunjungan->kunjungan_id,
            'civitas_id' => $kunjungan->civitas_id,
            'event_id' => $event->event_id,
            'identitas' => 'civitas',
            'kategori_tujuan' => 'event',
            'status_validasi' => false,
            'is_checkout' => false,
        ]);

        $this->assertDatabaseHas('kunjungan_detail', [
            'kunjungan_id' => $kunjungan->kunjungan_id,
            'kunci' => 'peran',
            'nilai' => 'Peserta',
        ]);

        $this->get($redirectUrl)
            ->assertOk()
            ->assertSee(__('visitor.registration_complete'));
    });

    it('menolak presensi event non-civitas saat field wajib kosong', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $event = EventFactory::new()->create([
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '09:00:00',
            'waktu_selesai_event' => '12:00:00',
        ]);

        $payload = [
            'event_id' => encid((string) $event->event_id),
            'nama' => 'Siti Aminah',
            'jenis_kelamin' => 'Perempuan',
            'nomor_telepon' => '081298765432',
            'email' => 'siti@example.com',
            'instansi' => '',
            'peran' => 'Peserta',
            'transportasi' => 'Mobil',
        ];

        // Action
        $response = $this
            ->from(route('tamu.event.form-presensi-non-civitas', encid((string) $event->event_id)))
            ->post(route('tamu.event.store-presensi-non-civitas'), $payload);

        // Assertion
        $response->assertRedirect(route('tamu.event.form-presensi-non-civitas', encid((string) $event->event_id)));
        $response->assertSessionHasErrors(['instansi']);

        $this->assertDatabaseCount('tamu', 0);
        $this->assertDatabaseCount('kunjungan', 0);
        $this->assertDatabaseCount('kunjungan_detail', 0);
    });

    it('menolak presensi event civitas saat field wajib kosong', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $event = EventFactory::new()->create([
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '09:00:00',
            'waktu_selesai_event' => '12:00:00',
        ]);

        $payload = [
            'event_id' => encid((string) $event->event_id),
            'nim_nip' => '2312012345',
            'nama' => 'Andi Pratama',
            'jenis_kelamin' => 'Laki-laki',
            'nomor_telepon' => '081211112222',
            'email' => 'andi@pcr.ac.id',
            'peran' => '',
        ];

        // Action
        $response = $this
            ->from(route('tamu.event.form-presensi-civitas', encid((string) $event->event_id)))
            ->post(route('tamu.event.store-presensi-civitas'), $payload);

        // Assertion
        $response->assertRedirect(route('tamu.event.form-presensi-civitas', encid((string) $event->event_id)));
        $response->assertSessionHasErrors(['peran']);

        $this->assertDatabaseCount('civitas', 0);
        $this->assertDatabaseCount('kunjungan', 0);
        $this->assertDatabaseCount('kunjungan_detail', 0);
    });

    it('menolak presensi event civitas saat format nim nip tidak valid', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $event = EventFactory::new()->create([
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '09:00:00',
            'waktu_selesai_event' => '12:00:00',
        ]);

        $payload = [
            'event_id' => encid((string) $event->event_id),
            'nim_nip' => '12345',
            'nama' => 'Andi Pratama',
            'jenis_kelamin' => 'Laki-laki',
            'nomor_telepon' => '081211112222',
            'email' => 'andi@pcr.ac.id',
            'peran' => 'Peserta',
        ];

        // Action
        $response = $this
            ->from(route('tamu.event.form-presensi-civitas', encid((string) $event->event_id)))
            ->post(route('tamu.event.store-presensi-civitas'), $payload);

        // Assertion
        $response->assertRedirect(route('tamu.event.form-presensi-civitas', encid((string) $event->event_id)));
        $response->assertSessionHas('error', 'Format NIM/NIP tidak valid.');

        $this->assertDatabaseCount('civitas', 0);
        $this->assertDatabaseCount('kunjungan', 0);
        $this->assertDatabaseCount('kunjungan_detail', 0);
    });

    it('mengembalikan not found saat fetch external data nip tidak ditemukan', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        // Action
        $response = $this->postJson(route('tamu.event.fetch-external-data'), [
            'nim_nip' => '654321',
        ]);

        // Assertion
        $response->assertStatus(404)
            ->assertJsonPath('status', false)
            ->assertJsonPath('source', 'not_found')
            ->assertJsonPath('identifier_type', 'nip');
    });
});
