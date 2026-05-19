<?php

use App\Enums\UserRole;
use App\Models\Kunjungan;
use App\Models\Tamu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

describe('BBT-15 Monitoring Tamu Aktif', function () {
    it('eksekutif dapat membuka halaman monitoring kunjungan dan melihat daftar tamu hari ini', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::EKSEKUTIF->value,
            'guard_name' => 'web',
        ]);

        $eksekutif = User::factory()->create([
            'email' => 'eksekutif-monitoring@pcr.ac.id',
        ]);
        $eksekutif->assignRole(UserRole::EKSEKUTIF->value);

        $tamuHariIni = Tamu::factory()->create([
            'nama_tamu' => 'Tamu Aktif Hari Ini',
        ]);
        $tamuKemarin = Tamu::factory()->create([
            'nama_tamu' => 'Tamu Hari Kemarin',
        ]);

        Kunjungan::factory()->create([
            'tamu_id' => $tamuHariIni->tamu_id,
            'is_checkout' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Kunjungan::factory()->create([
            'tamu_id' => $tamuKemarin->tamu_id,
            'is_checkout' => false,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        // Action
        $pageResponse = $this
            ->actingAs($eksekutif)
            ->withSession(['active_role' => UserRole::EKSEKUTIF->value])
            ->get(route('app.kunjungan.monitoring'));

        $dataResponse = $this
            ->actingAs($eksekutif)
            ->withSession(['active_role' => UserRole::EKSEKUTIF->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post('/app/kunjungan/data/monitoring-hari-ini', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]);

        // Assertion
        $pageResponse->assertOk()->assertSee('Monitoring Kunjungan Hari Ini');

        $dataResponse->assertOk();
        $dataResponse->assertJsonFragment([
            'nama' => 'Tamu Aktif Hari Ini',
        ]);
        $dataResponse->assertJsonMissing([
            'nama' => 'Tamu Hari Kemarin',
        ]);
    });

    it('menolak akses monitoring untuk pengguna dengan role mahasiswa', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::MAHASISWA->value,
            'guard_name' => 'web',
        ]);

        $mahasiswa = User::factory()->create([
            'email' => 'mahasiswa-monitoring@pcr.ac.id',
        ]);
        $mahasiswa->assignRole(UserRole::MAHASISWA->value);

        // Action
        $response = $this
            ->actingAs($mahasiswa)
            ->withSession(['active_role' => UserRole::MAHASISWA->value])
            ->get(route('app.kunjungan.monitoring'));

        // Assertion
        $response->assertForbidden();
    });

    it('mengalihkan tamu ke halaman login saat mengakses monitoring kunjungan', function () {
        /** @var Tests\TestCase $this */
        // Setup

        // Action
        $response = $this->get(route('app.kunjungan.monitoring'));

        // Assertion
        $response->assertRedirect(route('login'));
    });

    it('mengembalikan data kosong dan statistik nol saat tidak ada kunjungan hari ini', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::EKSEKUTIF->value,
            'guard_name' => 'web',
        ]);

        $eksekutif = User::factory()->create([
            'email' => 'eksekutif-monitoring-kosong@pcr.ac.id',
        ]);
        $eksekutif->assignRole(UserRole::EKSEKUTIF->value);

        Kunjungan::factory()->create([
            'is_checkout' => false,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        // Action
        $dataResponse = $this
            ->actingAs($eksekutif)
            ->withSession(['active_role' => UserRole::EKSEKUTIF->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post('/app/kunjungan/data/monitoring-hari-ini', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]);

        $statsResponse = $this
            ->actingAs($eksekutif)
            ->withSession(['active_role' => UserRole::EKSEKUTIF->value])
            ->get(route('app.kunjungan.monitoring.stats'));

        // Assertion
        $dataResponse->assertOk();
        expect($dataResponse->json('data'))->toBeArray()->toHaveCount(0);

        $statsResponse->assertOk()->assertJson([
            'success' => true,
            'totalKunjunganHariIni' => 0,
            'kunjunganSudahCheckout' => 0,
        ]);
    });
});
