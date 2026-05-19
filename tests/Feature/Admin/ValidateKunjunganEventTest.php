<?php

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Kunjungan;
use App\Models\User;
use Database\Factories\KunjunganFactory;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

describe('BBT-7 Validasi Presensi Tamu Event', function () {
    it('mengubah status kunjungan event dari belum divalidasi menjadi tervalidasi', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        Role::firstOrCreate([
            'name' => UserRole::STAF->value,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'email' => 'validator-event@pcr.ac.id',
        ]);
        $user->assignRole(UserRole::STAF->value);

        $event = Event::factory()->create([
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '09:00:00',
            'waktu_selesai_event' => '12:00:00',
        ]);

        $kunjungan = KunjunganFactory::new()->create([
            'event_id' => $event->event_id,
            'identitas' => 'non-civitas',
            'kategori_tujuan' => 'event',
            'status_validasi' => false,
        ]);

        // Action
        $response = $this
            ->actingAs($user)
            ->withSession(['active_role' => UserRole::STAF->value])
            ->post(route('app.kunjungan.validate-single', encid((string) $kunjungan->kunjungan_id)));

        // Assertion
        $response->assertOk()->assertJson([
            'status' => true,
            'message' => 'Kunjungan berhasil divalidasi',
        ]);

        $kunjungan->refresh();
        expect($kunjungan->status_validasi)->toBeTrue();

        $this->assertDatabaseHas('kunjungan', [
            'kunjungan_id' => $kunjungan->kunjungan_id,
            'event_id' => $event->event_id,
            'status_validasi' => true,
        ]);
    });

    it('menghapus data kunjungan saat aksi tolak presensi dilakukan', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        Role::firstOrCreate([
            'name' => UserRole::STAF->value,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'email' => 'rejector-event@pcr.ac.id',
        ]);
        $user->assignRole(UserRole::STAF->value);

        $event = Event::factory()->create([
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '13:00:00',
            'waktu_selesai_event' => '15:00:00',
        ]);

        $kunjungan = KunjunganFactory::new()->create([
            'event_id' => $event->event_id,
            'identitas' => 'non-civitas',
            'kategori_tujuan' => 'event',
            'status_validasi' => false,
        ]);

        // Action
        $response = $this
            ->actingAs($user)
            ->withSession(['active_role' => UserRole::STAF->value])
            ->post(route('app.kunjungan.reject-single', encid((string) $kunjungan->kunjungan_id)));

        // Assertion
        $response->assertOk()->assertJson([
            'status' => true,
            'message' => 'Kunjungan berhasil dihapus',
        ]);

        $this->assertSoftDeleted('kunjungan', [
            'kunjungan_id' => $kunjungan->kunjungan_id,
        ]);
    });

    it('mengembalikan 404 saat aksi validasi dilakukan dengan id kunjungan tidak valid', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        Role::firstOrCreate([
            'name' => UserRole::STAF->value,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'email' => 'validator-invalid-id@pcr.ac.id',
        ]);
        $user->assignRole(UserRole::STAF->value);

        // Action
        $response = $this
            ->actingAs($user)
            ->withSession(['active_role' => UserRole::STAF->value])
            ->post(route('app.kunjungan.validate-single', encid('999999')));

        // Assertion
        $response->assertNotFound();
    });

    it('memvalidasi beberapa kunjungan sekaligus melalui bulk validasi action validate', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        Role::firstOrCreate([
            'name' => UserRole::STAF->value,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'email' => 'bulk-accept@pcr.ac.id',
        ]);
        $user->assignRole(UserRole::STAF->value);

        $event = Event::factory()->create([
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '10:00:00',
            'waktu_selesai_event' => '12:00:00',
        ]);

        $kunjungan1 = KunjunganFactory::new()->create([
            'event_id' => $event->event_id,
            'identitas' => 'non-civitas',
            'kategori_tujuan' => 'event',
            'status_validasi' => false,
        ]);
        $kunjungan2 = KunjunganFactory::new()->create([
            'event_id' => $event->event_id,
            'identitas' => 'non-civitas',
            'kategori_tujuan' => 'event',
            'status_validasi' => false,
        ]);

        $payload = [
            'ids' => [
                encid((string) $kunjungan1->kunjungan_id),
                encid((string) $kunjungan2->kunjungan_id),
            ],
            'action' => 'validate',
        ];

        // Action
        $response = $this
            ->actingAs($user)
            ->withSession(['active_role' => UserRole::STAF->value])
            ->post(route('app.kunjungan.bulk-validasi'), $payload);

        // Assertion
        $response->assertOk()->assertJson([
            'status' => true,
            'message' => '2 kunjungan berhasil divalidasi',
        ]);

        $this->assertDatabaseHas('kunjungan', [
            'kunjungan_id' => $kunjungan1->kunjungan_id,
            'status_validasi' => true,
        ]);
        $this->assertDatabaseHas('kunjungan', [
            'kunjungan_id' => $kunjungan2->kunjungan_id,
            'status_validasi' => true,
        ]);
    });

    it('menghapus beberapa kunjungan sekaligus melalui bulk validasi action reject', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        Role::firstOrCreate([
            'name' => UserRole::STAF->value,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'email' => 'bulk-reject@pcr.ac.id',
        ]);
        $user->assignRole(UserRole::STAF->value);

        $event = Event::factory()->create([
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '14:00:00',
            'waktu_selesai_event' => '16:00:00',
        ]);

        $kunjungan1 = KunjunganFactory::new()->create([
            'event_id' => $event->event_id,
            'identitas' => 'non-civitas',
            'kategori_tujuan' => 'event',
            'status_validasi' => false,
        ]);
        $kunjungan2 = KunjunganFactory::new()->create([
            'event_id' => $event->event_id,
            'identitas' => 'non-civitas',
            'kategori_tujuan' => 'event',
            'status_validasi' => false,
        ]);

        $payload = [
            'ids' => [
                encid((string) $kunjungan1->kunjungan_id),
                encid((string) $kunjungan2->kunjungan_id),
            ],
            'action' => 'reject',
        ];

        // Action
        $response = $this
            ->actingAs($user)
            ->withSession(['active_role' => UserRole::STAF->value])
            ->post(route('app.kunjungan.bulk-validasi'), $payload);

        // Assertion
        $response->assertOk()->assertJson([
            'status' => true,
            'message' => '2 kunjungan berhasil dihapus',
        ]);

        $this->assertSoftDeleted('kunjungan', [
            'kunjungan_id' => $kunjungan1->kunjungan_id,
        ]);
        $this->assertSoftDeleted('kunjungan', [
            'kunjungan_id' => $kunjungan2->kunjungan_id,
        ]);
    });
});
