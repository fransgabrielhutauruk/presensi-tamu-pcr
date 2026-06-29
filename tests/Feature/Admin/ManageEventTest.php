<?php

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\EventKategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

describe('BBT-9 Mengelola Data Event', function () {
    it('menampilkan halaman daftar event dan memuat data event untuk admin', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'email' => 'admin-event-list@pcr.ac.id',
        ]);
        $user->assignRole(UserRole::ADMIN->value);

        $kategori = EventKategori::factory()->create([
            'nama_kategori' => 'Seminar',
        ]);

        Event::factory()->create([
            'eventkategori_id' => $kategori->eventkategori_id,
            'nama_event' => 'Seminar Big Data',
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '09:00:00',
            'waktu_selesai_event' => '11:00:00',
            'lokasi_event' => 'Aula PCR',
        ]);

        // Action
        $pageResponse = $this
            ->actingAs($user)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->get(route('app.event.index'));

        $dataResponse = $this
            ->actingAs($user)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.event.data', ['param1' => 'list']), [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]);

        // Assertion
        $pageResponse->assertOk()->assertSee('Kelola Event');

        $dataResponse->assertOk();
        $dataResponse->assertJsonFragment([
            'nama_event' => 'Seminar Big Data',
        ]);
    });

    it('berhasil mengedit data event dan menyimpan perubahan', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'email' => 'admin-edit-event@pcr.ac.id',
        ]);
        $user->assignRole(UserRole::ADMIN->value);

        $kategoriAwal = EventKategori::factory()->create([
            'nama_kategori' => 'Seminar',
        ]);
        $kategoriBaru = EventKategori::factory()->create([
            'nama_kategori' => 'Workshop',
        ]);

        $event = Event::factory()->create([
            'eventkategori_id' => $kategoriAwal->eventkategori_id,
            'nama_event' => 'Seminar Keamanan Data',
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '08:00:00',
            'waktu_selesai_event' => '10:00:00',
            'lokasi_event' => 'Aula 1',
            'link_dokumentasi_event' => null,
        ]);

        $payload = [
            'id' => encid((string) $event->event_id),
            'eventkategori_id' => encid((string) $kategoriBaru->eventkategori_id),
            'nama_event' => 'Workshop Keamanan Data',
            'deskripsi_event' => 'Perubahan topik menjadi workshop.',
            'tanggal_event' => now()->addDay()->toDateString(),
            'waktu_mulai_event' => '09:00',
            'waktu_selesai_event' => '11:00',
            'lokasi_event' => 'Lab Komputer 2',
            'kategori_lokasi' => 'dalam_kampus',
            'jenis_kegiatan' => 'non_pmb',
            'link_dokumentasi_event' => 'https://drive.google.com/drive/folders/workshop-keamanan-data',
        ];

        // Action
        $response = $this
            ->actingAs($user)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.event.update'), $payload);

        // Assertion
        $response->assertOk()->assertJson([
            'status' => true,
            'message' => 'Data event berhasil diperbarui',
        ]);

        $this->assertDatabaseHas('event', [
            'event_id' => $event->event_id,
            'eventkategori_id' => $kategoriBaru->eventkategori_id,
            'nama_event' => 'Workshop Keamanan Data',
            'lokasi_event' => 'Lab Komputer 2',
            'link_dokumentasi_event' => 'https://drive.google.com/drive/folders/workshop-keamanan-data',
        ]);
    });

    it('berhasil menghapus data event', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'email' => 'admin-delete-event@pcr.ac.id',
        ]);
        $user->assignRole(UserRole::ADMIN->value);

        $kategori = EventKategori::factory()->create([
            'nama_kategori' => 'Seminar',
        ]);

        $event = Event::factory()->create([
            'eventkategori_id' => $kategori->eventkategori_id,
            'nama_event' => 'Seminar Forensik Digital',
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '13:00:00',
            'waktu_selesai_event' => '15:00:00',
            'lokasi_event' => 'Aula 3',
        ]);

        // Action
        $response = $this
            ->actingAs($user)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.event.destroy'), [
                'id' => encid((string) $event->event_id),
            ]);

        // Assertion
        $response->assertOk()->assertJson([
            'status' => true,
            'message' => 'Data event berhasil dihapus',
        ]);

        $this->assertSoftDeleted('event', [
            'event_id' => $event->event_id,
        ]);
    });

    it('mengembalikan data kosong ketika filter kategori tidak memiliki kecocokan', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'email' => 'admin-filter@pcr.ac.id',
        ]);
        $user->assignRole(UserRole::ADMIN->value);

        $kategoriAdaData = EventKategori::factory()->create([
            'nama_kategori' => 'Seminar',
        ]);
        $kategoriTanpaData = EventKategori::factory()->create([
            'nama_kategori' => 'Workshop',
        ]);

        Event::factory()->create([
            'eventkategori_id' => $kategoriAdaData->eventkategori_id,
            'nama_event' => 'Seminar Cloud',
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '09:00:00',
            'waktu_selesai_event' => '11:00:00',
            'lokasi_event' => 'Aula PCR',
        ]);

        // Action
        $response = $this
            ->actingAs($user)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('app.event.data', ['param1' => 'list', 'filter_kategori' => encid((string) $kategoriTanpaData->eventkategori_id)]));

        // Assertion
        $response->assertOk();
        expect($response->json('data'))->toBeArray()->toHaveCount(0);
    });

    it('menolak update event ketika link dokumentasi tidak valid', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'email' => 'admin-update-invalid@pcr.ac.id',
        ]);
        $user->assignRole(UserRole::ADMIN->value);

        $kategori = EventKategori::factory()->create([
            'nama_kategori' => 'Seminar',
        ]);

        $event = Event::factory()->create([
            'eventkategori_id' => $kategori->eventkategori_id,
            'nama_event' => 'Seminar Keamanan',
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '10:00:00',
            'waktu_selesai_event' => '12:00:00',
            'lokasi_event' => 'Aula 2',
            'link_dokumentasi_event' => null,
        ]);

        $payload = [
            'id' => encid((string) $event->event_id),
            'eventkategori_id' => encid((string) $kategori->eventkategori_id),
            'nama_event' => 'Seminar Keamanan',
            'deskripsi_event' => 'Seminar keamanan informasi',
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '10:00',
            'waktu_selesai_event' => '12:00',
            'lokasi_event' => 'Aula 2',
            'kategori_lokasi' => 'dalam_kampus',
            'jenis_kegiatan' => 'non_pmb',
            'link_dokumentasi_event' => 'bukan-url-valid',
        ];

        // Action
        $response = $this
            ->actingAs($user)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->from(route('app.event.index'))
            ->post(route('app.event.update'), $payload);

        // Assertion
        $response->assertRedirect(route('app.event.index'));
        $response->assertSessionHasErrors(['link_dokumentasi_event']);

        $event->refresh();
        expect($event->link_dokumentasi_event)->toBeNull();
    });

    it('mengembalikan 404 ketika menghapus event dengan id yang tidak valid', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'email' => 'admin-delete-invalid@pcr.ac.id',
        ]);
        $user->assignRole(UserRole::ADMIN->value);

        // Action
        $response = $this
            ->actingAs($user)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.event.destroy'), [
                'id' => encid('999999'),
            ]);

        // Assertion
        $response->assertNotFound();
    });
});
