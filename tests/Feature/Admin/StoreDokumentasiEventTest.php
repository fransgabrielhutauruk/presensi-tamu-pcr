<?php

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\EventKategori;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

describe('BBT-8 Kirim Dokumentasi', function () {
    it('menyimpan url dokumentasi event yang valid lalu menampilkan pesan sukses', function () {
        /** @var Tests\TestCase $this */

        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        Role::firstOrCreate([
            'name' => UserRole::STAF->value,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'email' => 'staf-dokumentasi@pcr.ac.id',
        ]);
        $user->assignRole(UserRole::STAF->value);

        $kategori = EventKategori::factory()->create([
            'nama_kategori' => 'Seminar',
        ]);

        $event = Event::factory()->create([
            'eventkategori_id' => $kategori->eventkategori_id,
            'nama_event' => 'Seminar Data Science',
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '09:00:00',
            'waktu_selesai_event' => '12:00:00',
            'lokasi_event' => 'Aula Utama PCR',
            'link_dokumentasi_event' => null,
        ]);

        $payload = [
            'id' => encid((string) $event->event_id),
            'eventkategori_id' => encid((string) $kategori->eventkategori_id),
            'nama_event' => 'Seminar Data Science',
            'deskripsi_event' => 'Seminar pengenalan data science.',
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '09:00',
            'waktu_selesai_event' => '12:00',
            'lokasi_event' => 'Aula Utama PCR',
            'kategori_lokasi' => 'dalam_kampus',
            'jenis_kegiatan' => 'non_pmb',
            'link_dokumentasi_event' => 'https://drive.google.com/drive/folders/dokumentasi-event-ds',
        ];

        // Action
        $response = $this
            ->actingAs($user)
            ->withSession(['active_role' => UserRole::STAF->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.event.update'), $payload);

        // Assertion
        $response->assertOk()->assertJson([
            'status' => true,
            'message' => 'Data event berhasil diperbarui',
        ]);

        $this->assertDatabaseHas('event', [
            'event_id' => $event->event_id,
            'link_dokumentasi_event' => 'https://drive.google.com/drive/folders/dokumentasi-event-ds',
        ]);
    });

    it('menolak url dokumentasi event yang tidak valid', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        Role::firstOrCreate([
            'name' => UserRole::STAF->value,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'email' => 'staf-invalid-url@pcr.ac.id',
        ]);
        $user->assignRole(UserRole::STAF->value);

        $kategori = EventKategori::factory()->create([
            'nama_kategori' => 'Workshop',
        ]);

        $event = Event::factory()->create([
            'eventkategori_id' => $kategori->eventkategori_id,
            'nama_event' => 'Workshop AI',
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '10:00:00',
            'waktu_selesai_event' => '12:00:00',
            'lokasi_event' => 'Lab Komputer',
            'link_dokumentasi_event' => null,
        ]);

        $payload = [
            'id' => encid((string) $event->event_id),
            'eventkategori_id' => encid((string) $kategori->eventkategori_id),
            'nama_event' => 'Workshop AI',
            'deskripsi_event' => 'Workshop penerapan AI.',
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '10:00',
            'waktu_selesai_event' => '12:00',
            'lokasi_event' => 'Lab Komputer',
            'kategori_lokasi' => 'dalam_kampus',
            'jenis_kegiatan' => 'non_pmb',
            'link_dokumentasi_event' => 'invalid-url',
        ];

        // Action
        $response = $this
            ->actingAs($user)
            ->withSession(['active_role' => UserRole::STAF->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->from(route('app.event.index'))
            ->post(route('app.event.update'), $payload);

        // Assertion
        $response->assertRedirect(route('app.event.index'));
        $response->assertSessionHasErrors(['link_dokumentasi_event']);

        $event->refresh();
        expect($event->link_dokumentasi_event)->toBeNull();
    });
});
