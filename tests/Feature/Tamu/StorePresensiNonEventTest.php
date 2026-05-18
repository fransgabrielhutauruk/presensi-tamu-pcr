<?php

use App\Models\Kunjungan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

uses(RefreshDatabase::class);

describe('BBT-2 Pengisian Presensi Non-Event', function () {
    it('menyimpan presensi non-event saat semua field wajib valid lalu menampilkan halaman sukses', function () {
        // Setup
        /** @var Tests\TestCase $this */
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $payload = [
            'nama' => 'Budi Santoso',
            'jenis_kelamin' => 'Laki-laki',
            'nomor_telepon' => '081234567890',
            'email' => 'budi@example.com',
            'kategori_tujuan' => 'instansi',
            'estimasi_durasi' => 2,
            'transportasi' => 'Mobil',
            'jumlah_rombongan' => 3,
            'instansi' => 'PT Maju Jaya',
            'jenis_instansi' => 'Swasta',
            'jabatan' => 'Manajer Operasional',
            'pihak_dituju' => 'Direktur Akademik',
            'keperluan' => 'Diskusi kerja sama pelatihan',
        ];

        // Action
        $response = $this->post(route('tamu.non-event.store-presensi'), $payload);

        $redirectUrl = $response->headers->get('Location');
        $kunjungan = Kunjungan::query()
            ->with(['tamu', 'details'])
            ->where('kategori_tujuan', 'instansi')
            ->where('identitas', 'non-civitas')
            ->latest('kunjungan_id')
            ->first();

        // Assertion
        $response->assertRedirect();
        expect($redirectUrl)->not->toBeEmpty();
        expect($kunjungan)->not->toBeNull();

        $this->assertDatabaseHas('tamu', [
            'tamu_id' => $kunjungan->tamu_id,
            'nama_tamu' => 'Budi Santoso',
            'jenis_kelamin_tamu' => 'Laki-laki',
            'email_tamu' => 'budi@example.com',
            'nomor_telepon_tamu' => '081234567890',
        ]);

        $this->assertDatabaseHas('kunjungan', [
            'kunjungan_id' => $kunjungan->kunjungan_id,
            'tamu_id' => $kunjungan->tamu_id,
            'identitas' => 'non-civitas',
            'kategori_tujuan' => 'instansi',
            'jumlah_rombongan' => 3,
            'transportasi' => 'Mobil',
            'status_validasi' => false,
            'is_checkout' => false,
        ]);

        $this->assertDatabaseCount('kunjungan_detail', 5);

        $this->get($redirectUrl)
            ->assertOk()
            ->assertSee(__('visitor.registration_complete'));
    });

    it('menyimpan presensi non-event kategori bisnis saat semua field wajib valid', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $payload = [
            'nama' => 'Rudi Hartono',
            'jenis_kelamin' => 'Laki-laki',
            'nomor_telepon' => '081355555555',
            'email' => 'rudi@example.com',
            'kategori_tujuan' => 'bisnis',
            'estimasi_durasi' => 3,
            'transportasi' => 'Mobil',
            'jumlah_rombongan' => 2,
            'instansi' => 'PT Solusi Digital',
            'kategori_instansi' => 'Teknologi Informasi',
            'skala_instansi' => 'Perusahaan Menengah',
            'jabatan' => 'Business Development',
            'pihak_dituju' => 'Wakil Direktur',
            'keperluan' => 'Penjajakan kerja sama strategis',
        ];

        // Action
        $response = $this->post(route('tamu.non-event.store-presensi'), $payload);

        $redirectUrl = $response->headers->get('Location');
        $kunjungan = Kunjungan::query()
            ->with(['tamu', 'details'])
            ->where('kategori_tujuan', 'bisnis')
            ->where('identitas', 'non-civitas')
            ->latest('kunjungan_id')
            ->first();

        // Assertion
        $response->assertRedirect();
        expect($redirectUrl)->not->toBeEmpty();
        expect($kunjungan)->not->toBeNull();

        $this->assertDatabaseHas('tamu', [
            'tamu_id' => $kunjungan->tamu_id,
            'nama_tamu' => 'Rudi Hartono',
            'jenis_kelamin_tamu' => 'Laki-laki',
            'email_tamu' => 'rudi@example.com',
            'nomor_telepon_tamu' => '081355555555',
        ]);

        $this->assertDatabaseHas('kunjungan', [
            'kunjungan_id' => $kunjungan->kunjungan_id,
            'tamu_id' => $kunjungan->tamu_id,
            'identitas' => 'non-civitas',
            'kategori_tujuan' => 'bisnis',
            'jumlah_rombongan' => 2,
            'transportasi' => 'Mobil',
            'status_validasi' => false,
            'is_checkout' => false,
        ]);

        $this->assertDatabaseCount('kunjungan_detail', 6);

        $this->get($redirectUrl)
            ->assertOk()
            ->assertSee(__('visitor.registration_complete'));
    });

    it('menyimpan presensi non-event kategori ortu saat semua field wajib valid', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $payload = [
            'nama' => 'Nuraini',
            'jenis_kelamin' => 'Perempuan',
            'nomor_telepon' => '081366666666',
            'email' => 'nuraini@example.com',
            'kategori_tujuan' => 'ortu',
            'estimasi_durasi' => 2,
            'transportasi' => 'Motor',
            'jumlah_rombongan' => 1,
            'hubungan_dengan_mahasiswa' => 'Orang Tua',
            'nama_mahasiswa' => 'Andi Pratama',
            'prodi_mahasiswa' => 'Teknik Informatika',
            'nim_mahasiswa' => '2312012345',
            'pihak_dituju' => 'Dosen Wali',
            'keperluan' => 'Konsultasi akademik',
        ];

        // Action
        $response = $this->post(route('tamu.non-event.store-presensi'), $payload);

        $redirectUrl = $response->headers->get('Location');
        $kunjungan = Kunjungan::query()
            ->with(['tamu', 'details'])
            ->where('kategori_tujuan', 'ortu')
            ->where('identitas', 'non-civitas')
            ->latest('kunjungan_id')
            ->first();

        // Assertion
        $response->assertRedirect();
        expect($redirectUrl)->not->toBeEmpty();
        expect($kunjungan)->not->toBeNull();

        $this->assertDatabaseHas('tamu', [
            'tamu_id' => $kunjungan->tamu_id,
            'nama_tamu' => 'Nuraini',
            'jenis_kelamin_tamu' => 'Perempuan',
            'email_tamu' => 'nuraini@example.com',
            'nomor_telepon_tamu' => '081366666666',
        ]);

        $this->assertDatabaseHas('kunjungan', [
            'kunjungan_id' => $kunjungan->kunjungan_id,
            'tamu_id' => $kunjungan->tamu_id,
            'identitas' => 'non-civitas',
            'kategori_tujuan' => 'ortu',
            'jumlah_rombongan' => 1,
            'transportasi' => 'Motor',
            'status_validasi' => false,
            'is_checkout' => false,
        ]);

        $this->assertDatabaseCount('kunjungan_detail', 6);

        $this->get($redirectUrl)
            ->assertOk()
            ->assertSee(__('visitor.registration_complete'));
    });

    it('menyimpan presensi non-event kategori informasi kampus saat semua field wajib valid', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $payload = [
            'nama' => 'Dewi Lestari',
            'jenis_kelamin' => 'Perempuan',
            'nomor_telepon' => '081377777777',
            'email' => 'dewi@example.com',
            'kategori_tujuan' => 'informasi_kampus',
            'estimasi_durasi' => 1,
            'transportasi' => 'Online Ride',
            'jumlah_rombongan' => 1,
            'asal_sekolah' => 'SMA Negeri 1 Pekanbaru',
            'prodi_diminati' => 'Sistem Informasi',
            'keperluan' => 'Mencari informasi PMB',
        ];

        // Action
        $response = $this->post(route('tamu.non-event.store-presensi'), $payload);

        $redirectUrl = $response->headers->get('Location');
        $kunjungan = Kunjungan::query()
            ->with(['tamu', 'details'])
            ->where('kategori_tujuan', 'informasi_kampus')
            ->where('identitas', 'non-civitas')
            ->latest('kunjungan_id')
            ->first();

        // Assertion
        $response->assertRedirect();
        expect($redirectUrl)->not->toBeEmpty();
        expect($kunjungan)->not->toBeNull();

        $this->assertDatabaseHas('tamu', [
            'tamu_id' => $kunjungan->tamu_id,
            'nama_tamu' => 'Dewi Lestari',
            'jenis_kelamin_tamu' => 'Perempuan',
            'email_tamu' => 'dewi@example.com',
            'nomor_telepon_tamu' => '081377777777',
        ]);

        $this->assertDatabaseHas('kunjungan', [
            'kunjungan_id' => $kunjungan->kunjungan_id,
            'tamu_id' => $kunjungan->tamu_id,
            'identitas' => 'non-civitas',
            'kategori_tujuan' => 'informasi_kampus',
            'jumlah_rombongan' => 1,
            'transportasi' => 'Online Ride',
            'status_validasi' => false,
            'is_checkout' => false,
        ]);

        $this->assertDatabaseCount('kunjungan_detail', 3);

        $this->get($redirectUrl)
            ->assertOk()
            ->assertSee(__('visitor.registration_complete'));
    });

    it('menyimpan presensi non-event kategori lainnya saat semua field wajib valid', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $payload = [
            'nama' => 'Agus Saputra',
            'jenis_kelamin' => 'Laki-laki',
            'nomor_telepon' => '081388888888',
            'email' => 'agus@example.com',
            'kategori_tujuan' => 'lainnya',
            'estimasi_durasi' => 2,
            'transportasi' => 'Jalan Kaki',
            'jumlah_rombongan' => 1,
            'pihak_dituju' => 'Petugas Informasi',
            'keperluan' => 'Kunjungan umum',
        ];

        // Action
        $response = $this->post(route('tamu.non-event.store-presensi'), $payload);

        $redirectUrl = $response->headers->get('Location');
        $kunjungan = Kunjungan::query()
            ->with(['tamu', 'details'])
            ->where('kategori_tujuan', 'lainnya')
            ->where('identitas', 'non-civitas')
            ->latest('kunjungan_id')
            ->first();

        // Assertion
        $response->assertRedirect();
        expect($redirectUrl)->not->toBeEmpty();
        expect($kunjungan)->not->toBeNull();

        $this->assertDatabaseHas('tamu', [
            'tamu_id' => $kunjungan->tamu_id,
            'nama_tamu' => 'Agus Saputra',
            'jenis_kelamin_tamu' => 'Laki-laki',
            'email_tamu' => 'agus@example.com',
            'nomor_telepon_tamu' => '081388888888',
        ]);

        $this->assertDatabaseHas('kunjungan', [
            'kunjungan_id' => $kunjungan->kunjungan_id,
            'tamu_id' => $kunjungan->tamu_id,
            'identitas' => 'non-civitas',
            'kategori_tujuan' => 'lainnya',
            'jumlah_rombongan' => 1,
            'transportasi' => 'Jalan Kaki',
            'status_validasi' => false,
            'is_checkout' => false,
        ]);

        $this->assertDatabaseCount('kunjungan_detail', 2);

        $this->get($redirectUrl)
            ->assertOk()
            ->assertSee(__('visitor.registration_complete'));
    });

    it('menolak pengiriman kategori instansi saat field wajib kategori kosong', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $payload = [
            'nama' => 'Budi Santoso',
            'jenis_kelamin' => 'Laki-laki',
            'nomor_telepon' => '081234567890',
            'email' => 'budi@example.com',
            'kategori_tujuan' => 'instansi',
            'estimasi_durasi' => 2,
            'transportasi' => 'Mobil',
            'jumlah_rombongan' => 3,
            'instansi' => 'PT Maju Jaya',
            'jenis_instansi' => '',
            'jabatan' => 'Manajer Operasional',
            'pihak_dituju' => 'Direktur Akademik',
            'keperluan' => 'Diskusi kerja sama pelatihan',
        ];

        // Action
        $response = $this
            ->from(route('tamu.non-event.form-presensi', ['tujuan' => 'instansi']))
            ->post(route('tamu.non-event.store-presensi'), $payload);

        // Assertion
        $response->assertRedirect(route('tamu.non-event.form-presensi', ['tujuan' => 'instansi']));
        $response->assertSessionHasErrors(['jenis_instansi']);

        $this->assertDatabaseCount('tamu', 0);
        $this->assertDatabaseCount('kunjungan', 0);
        $this->assertDatabaseCount('kunjungan_detail', 0);
    });

    it('menolak pengiriman kategori bisnis saat field wajib kategori kosong', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $payload = [
            'nama' => 'Rudi Hartono',
            'jenis_kelamin' => 'Laki-laki',
            'nomor_telepon' => '081355555555',
            'email' => 'rudi@example.com',
            'kategori_tujuan' => 'bisnis',
            'estimasi_durasi' => 3,
            'transportasi' => 'Mobil',
            'jumlah_rombongan' => 2,
            'instansi' => 'PT Solusi Digital',
            'kategori_instansi' => '',
            'skala_instansi' => 'Perusahaan Menengah',
            'jabatan' => 'Business Development',
            'pihak_dituju' => 'Wakil Direktur',
            'keperluan' => 'Penjajakan kerja sama strategis',
        ];

        // Action
        $response = $this
            ->from(route('tamu.non-event.form-presensi', ['tujuan' => 'bisnis']))
            ->post(route('tamu.non-event.store-presensi'), $payload);

        // Assertion
        $response->assertRedirect(route('tamu.non-event.form-presensi', ['tujuan' => 'bisnis']));
        $response->assertSessionHasErrors(['kategori_instansi']);

        $this->assertDatabaseCount('tamu', 0);
        $this->assertDatabaseCount('kunjungan', 0);
        $this->assertDatabaseCount('kunjungan_detail', 0);
    });

    it('menolak pengiriman kategori ortu saat field wajib kategori kosong', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $payload = [
            'nama' => 'Nuraini',
            'jenis_kelamin' => 'Perempuan',
            'nomor_telepon' => '081366666666',
            'email' => 'nuraini@example.com',
            'kategori_tujuan' => 'ortu',
            'estimasi_durasi' => 2,
            'transportasi' => 'Motor',
            'jumlah_rombongan' => 1,
            'hubungan_dengan_mahasiswa' => 'Orang Tua',
            'nama_mahasiswa' => '',
            'prodi_mahasiswa' => 'Teknik Informatika',
            'nim_mahasiswa' => '2312012345',
            'pihak_dituju' => 'Dosen Wali',
            'keperluan' => 'Konsultasi akademik',
        ];

        // Action
        $response = $this
            ->from(route('tamu.non-event.form-presensi', ['tujuan' => 'ortu']))
            ->post(route('tamu.non-event.store-presensi'), $payload);

        // Assertion
        $response->assertRedirect(route('tamu.non-event.form-presensi', ['tujuan' => 'ortu']));
        $response->assertSessionHasErrors(['nama_mahasiswa']);

        $this->assertDatabaseCount('tamu', 0);
        $this->assertDatabaseCount('kunjungan', 0);
        $this->assertDatabaseCount('kunjungan_detail', 0);
    });

    it('menolak pengiriman kategori informasi kampus saat field wajib kategori kosong', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $payload = [
            'nama' => 'Dewi Lestari',
            'jenis_kelamin' => 'Perempuan',
            'nomor_telepon' => '081377777777',
            'email' => 'dewi@example.com',
            'kategori_tujuan' => 'informasi_kampus',
            'estimasi_durasi' => 1,
            'transportasi' => 'Online Ride',
            'jumlah_rombongan' => 1,
            'asal_sekolah' => '',
            'prodi_diminati' => 'Sistem Informasi',
            'keperluan' => 'Mencari informasi PMB',
        ];

        // Action
        $response = $this
            ->from(route('tamu.non-event.form-presensi', ['tujuan' => 'informasi_kampus']))
            ->post(route('tamu.non-event.store-presensi'), $payload);

        // Assertion
        $response->assertRedirect(route('tamu.non-event.form-presensi', ['tujuan' => 'informasi_kampus']));
        $response->assertSessionHasErrors(['asal_sekolah']);

        $this->assertDatabaseCount('tamu', 0);
        $this->assertDatabaseCount('kunjungan', 0);
        $this->assertDatabaseCount('kunjungan_detail', 0);
    });

    it('menolak pengiriman kategori lainnya saat field wajib kategori kosong', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $payload = [
            'nama' => 'Agus Saputra',
            'jenis_kelamin' => 'Laki-laki',
            'nomor_telepon' => '081388888888',
            'email' => 'agus@example.com',
            'kategori_tujuan' => 'lainnya',
            'estimasi_durasi' => 2,
            'transportasi' => 'Jalan Kaki',
            'jumlah_rombongan' => 1,
            'pihak_dituju' => '',
            'keperluan' => 'Kunjungan umum',
        ];

        // Action
        $response = $this
            ->from(route('tamu.non-event.form-presensi', ['tujuan' => 'lainnya']))
            ->post(route('tamu.non-event.store-presensi'), $payload);

        // Assertion
        $response->assertRedirect(route('tamu.non-event.form-presensi', ['tujuan' => 'lainnya']));
        $response->assertSessionHasErrors(['pihak_dituju']);

        $this->assertDatabaseCount('tamu', 0);
        $this->assertDatabaseCount('kunjungan', 0);
        $this->assertDatabaseCount('kunjungan_detail', 0);
    });
});
