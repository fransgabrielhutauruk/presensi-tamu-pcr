<?php

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\EventKategori;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

describe('BBT-6 Pembuatan Event Baru', function () {
    it('menyimpan data event valid lalu menampilkan detail event qr code dan tautan presensi', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        Role::firstOrCreate([
            'name' => UserRole::STAF->value,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'email' => 'staf-event@pcr.ac.id',
        ]);
        $user->assignRole(UserRole::STAF->value);

        $kategori = EventKategori::factory()->create([
            'nama_kategori' => 'Seminar',
        ]);

        $payload = [
            'eventkategori_id' => encid((string) $kategori->eventkategori_id),
            'nama_event' => 'Seminar Teknologi AI',
            'deskripsi_event' => 'Seminar teknologi untuk sivitas kampus.',
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '09:00',
            'waktu_selesai_event' => '12:00',
            'lokasi_event' => 'Aula Utama PCR',
            'kategori_lokasi' => 'dalam_kampus',
            'jenis_kegiatan' => 'non_pmb',
            'link_dokumentasi_event' => 'https://drive.google.com/drive/folders/pcr-seminar-ai',
        ];

        // Action
        $response = $this
            ->actingAs($user)
            ->withSession(['active_role' => UserRole::STAF->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.event.store'), $payload);

        $event = Event::query()->where('nama_event', 'Seminar Teknologi AI')->first();
        $eventIdHashed = encid((string) $event?->event_id);
        $presensiUrl = route('tamu.event.identitas', $eventIdHashed);

        // Assertion
        $response->assertOk()->assertJson([
            'status' => true,
            'message' => 'Data event berhasil disimpan',
        ]);

        expect($event)->not->toBeNull();

        $this->assertDatabaseHas('event', [
            'event_id' => $event->event_id,
            'eventkategori_id' => $kategori->eventkategori_id,
            'nama_event' => 'Seminar Teknologi AI',
            'lokasi_event' => 'Aula Utama PCR',
            'kategori_lokasi' => 'dalam_kampus',
            'jenis_kegiatan' => 'non_pmb',
            'link_dokumentasi_event' => 'https://drive.google.com/drive/folders/pcr-seminar-ai',
        ]);

        $this->actingAs($user)
            ->withSession(['active_role' => UserRole::STAF->value])
            ->get(route('app.event.qr-code', $eventIdHashed))
            ->assertOk()
            ->assertSee('Atau akses link alternatif:')
            ->assertSee($presensiUrl);
    });

    it('menolak pembuatan event ketika format waktu tidak valid', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        Role::firstOrCreate([
            'name' => UserRole::STAF->value,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'email' => 'staf-invalid-time@pcr.ac.id',
        ]);
        $user->assignRole(UserRole::STAF->value);

        $kategori = EventKategori::factory()->create([
            'nama_kategori' => 'Workshop',
        ]);

        $payload = [
            'eventkategori_id' => encid((string) $kategori->eventkategori_id),
            'nama_event' => 'Workshop UI UX',
            'deskripsi_event' => 'Workshop desain antarmuka.',
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '9-00',
            'waktu_selesai_event' => '12.00',
            'lokasi_event' => 'Lab Komputer 1',
            'kategori_lokasi' => 'dalam_kampus',
            'jenis_kegiatan' => 'non_pmb',
            'link_dokumentasi_event' => 'https://drive.google.com/drive/folders/workshop-uiux',
        ];

        // Action
        $response = $this
            ->actingAs($user)
            ->withSession(['active_role' => UserRole::STAF->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->from(route('app.event.index'))
            ->post(route('app.event.store'), $payload);

        // Assertion
        $response->assertRedirect(route('app.event.index'));
        $response->assertSessionHasErrors(['waktu_mulai_event', 'waktu_selesai_event']);
        $this->assertDatabaseCount('event', 0);
    });

    it('menolak pembuatan event ketika field mandatory dikosongkan', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        Role::firstOrCreate([
            'name' => UserRole::STAF->value,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'email' => 'staf-incomplete@pcr.ac.id',
        ]);
        $user->assignRole(UserRole::STAF->value);

        $kategori = EventKategori::factory()->create([
            'nama_kategori' => 'Pelatihan',
        ]);

        $payload = [
            'eventkategori_id' => encid((string) $kategori->eventkategori_id),
            'nama_event' => '',
            'deskripsi_event' => 'Pelatihan internal.',
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '08:00',
            'waktu_selesai_event' => '10:00',
            'lokasi_event' => '',
            'kategori_lokasi' => '',
            'jenis_kegiatan' => '',
            'link_dokumentasi_event' => 'https://drive.google.com/drive/folders/pelatihan-internal',
        ];

        // Action
        $response = $this
            ->actingAs($user)
            ->withSession(['active_role' => UserRole::STAF->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->from(route('app.event.index'))
            ->post(route('app.event.store'), $payload);

        // Assertion
        $response->assertRedirect(route('app.event.index'));
        $response->assertSessionHasErrors(['nama_event', 'lokasi_event', 'kategori_lokasi', 'jenis_kegiatan']);
        $this->assertDatabaseCount('event', 0);
    });
});
