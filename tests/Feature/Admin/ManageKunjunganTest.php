<?php

use App\Enums\UserRole;
use App\Models\Kunjungan;
use App\Models\Tamu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

describe('BBT-10 Mengelola Data Kunjungan', function () {
    it('menampilkan halaman daftar kunjungan dan memuat data kunjungan tervalidasi untuk admin', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'email' => 'admin-kunjungan-list@pcr.ac.id',
        ]);
        $user->assignRole(UserRole::ADMIN->value);

        $tamu = Tamu::factory()->create([
            'nama_tamu' => 'Budi Santoso',
            'email_tamu' => 'budi.santoso@example.com',
        ]);

        Kunjungan::factory()->create([
            'tamu_id' => $tamu->tamu_id,
            'identitas' => 'non-civitas',
            'status_validasi' => true,
            'is_checkout' => false,
        ]);

        // Action
        $pageResponse = $this
            ->actingAs($user)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->get(route('app.kunjungan.index'));

        $dataResponse = $this
            ->actingAs($user)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.kunjungan.data', ['param1' => 'list']), [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]);

        // Assertion
        $pageResponse->assertOk()->assertSee('Kelola Kunjungan');

        $dataResponse->assertOk();
        $dataResponse->assertJsonFragment([
            'nama' => 'Budi Santoso',
        ]);
    });

    it('tidak menampilkan data kunjungan yang belum divalidasi pada daftar kunjungan', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'email' => 'admin-kunjungan-filter-validasi@pcr.ac.id',
        ]);
        $user->assignRole(UserRole::ADMIN->value);

        $tamuValid = Tamu::factory()->create([
            'nama_tamu' => 'Tamu Validasi',
        ]);
        $tamuBelumValid = Tamu::factory()->create([
            'nama_tamu' => 'Tamu Belum Validasi',
        ]);

        Kunjungan::factory()->create([
            'tamu_id' => $tamuValid->tamu_id,
            'status_validasi' => true,
        ]);
        Kunjungan::factory()->create([
            'tamu_id' => $tamuBelumValid->tamu_id,
            'status_validasi' => false,
        ]);

        // Action
        $response = $this
            ->actingAs($user)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.kunjungan.data', ['param1' => 'list']), [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]);

        // Assertion
        $response->assertOk();
        $response->assertJsonFragment([
            'nama' => 'Tamu Validasi',
        ]);
        $response->assertJsonMissing([
            'nama' => 'Tamu Belum Validasi',
        ]);
    });

    it('mengembalikan data kosong saat pencarian kunjungan tidak memiliki kecocokan', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'email' => 'admin-kunjungan-search@pcr.ac.id',
        ]);
        $user->assignRole(UserRole::ADMIN->value);

        $tamu = Tamu::factory()->create([
            'nama_tamu' => 'Pencarian Kunjungan',
        ]);

        Kunjungan::factory()->create([
            'tamu_id' => $tamu->tamu_id,
            'status_validasi' => true,
        ]);

        // Action
        $response = $this
            ->actingAs($user)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.kunjungan.data', ['param1' => 'list']), [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'search' => [
                    'value' => 'Nama Tidak Ditemukan',
                    'regex' => 'false',
                ],
            ]);

        // Assertion
        $response->assertOk();
        expect($response->json('data'))->toBeArray()->toHaveCount(0);
    });

    it('mengembalikan 404 ketika menghapus kunjungan dengan id yang tidak valid', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'email' => 'admin-kunjungan-delete-invalid@pcr.ac.id',
        ]);
        $user->assignRole(UserRole::ADMIN->value);

        // Action
        $response = $this
            ->actingAs($user)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.kunjungan.destroy'), [
                'id' => encid('999999'),
            ]);

        // Assertion
        $response->assertNotFound();
    });
});
