<?php

use App\Enums\UserRole;
use App\Models\Kunjungan;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

describe('BBT-13 Memvalidasi Presensi Tamu Non-Event', function () {
    it('admin memvalidasi kunjungan non-event dari belum divalidasi menjadi tervalidasi', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-validasi-nonevent@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        $kunjungan = Kunjungan::factory()->create([
            'event_id' => null,
            'identitas' => 'non-civitas',
            'kategori_tujuan' => 'instansi',
            'status_validasi' => false,
        ]);

        // Action
        $pageResponse = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->get(route('app.kunjungan-validasi'));

        $validateResponse = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->post(route('app.kunjungan.validate-single', encid((string) $kunjungan->kunjungan_id)));

        // Assertion
        $pageResponse->assertOk()->assertSee('Validasi Kunjungan');

        $validateResponse->assertOk()->assertJson([
            'status' => true,
            'message' => 'Kunjungan berhasil divalidasi',
        ]);

        $kunjungan->refresh();
        expect($kunjungan->status_validasi)->toBeTrue();

        $this->assertDatabaseHas('kunjungan', [
            'kunjungan_id' => $kunjungan->kunjungan_id,
            'event_id' => null,
            'status_validasi' => true,
        ]);
    });

    it('admin menolak presensi non-event dan data kunjungan terhapus secara soft delete', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-tolak-nonevent@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        $kunjungan = Kunjungan::factory()->create([
            'event_id' => null,
            'identitas' => 'non-civitas',
            'kategori_tujuan' => 'instansi',
            'status_validasi' => false,
        ]);

        // Action
        $response = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
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

    it('mengembalikan 404 saat validasi non-event dilakukan dengan id kunjungan tidak valid', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-validasi-nonevent-invalid@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        // Action
        $response = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->post(route('app.kunjungan.validate-single', encid('999999')));

        // Assertion
        $response->assertNotFound();
    });

    it('mengembalikan 404 saat tolak non-event dilakukan dengan id kunjungan tidak valid', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-tolak-nonevent-invalid@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        // Action
        $response = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->post(route('app.kunjungan.reject-single', encid('999999')));

        // Assertion
        $response->assertNotFound();
    });

    it('admin memvalidasi beberapa kunjungan non-event sekaligus melalui bulk validasi', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-bulk-validate-nonevent@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        $kunjungan1 = Kunjungan::factory()->create([
            'event_id' => null,
            'identitas' => 'non-civitas',
            'kategori_tujuan' => 'instansi',
            'status_validasi' => false,
        ]);
        $kunjungan2 = Kunjungan::factory()->create([
            'event_id' => null,
            'identitas' => 'non-civitas',
            'kategori_tujuan' => 'instansi',
            'status_validasi' => false,
        ]);

        // Action
        $response = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->post(route('app.kunjungan.bulk-validasi'), [
                'ids' => [
                    encid((string) $kunjungan1->kunjungan_id),
                    encid((string) $kunjungan2->kunjungan_id),
                ],
                'action' => 'validate',
            ]);

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

    it('admin menolak beberapa kunjungan non-event sekaligus melalui bulk validasi', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-bulk-reject-nonevent@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        $kunjungan1 = Kunjungan::factory()->create([
            'event_id' => null,
            'identitas' => 'non-civitas',
            'kategori_tujuan' => 'instansi',
            'status_validasi' => false,
        ]);
        $kunjungan2 = Kunjungan::factory()->create([
            'event_id' => null,
            'identitas' => 'non-civitas',
            'kategori_tujuan' => 'instansi',
            'status_validasi' => false,
        ]);

        // Action
        $response = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->post(route('app.kunjungan.bulk-validasi'), [
                'ids' => [
                    encid((string) $kunjungan1->kunjungan_id),
                    encid((string) $kunjungan2->kunjungan_id),
                ],
                'action' => 'reject',
            ]);

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
