<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

describe('BBT-14 Melihat Dashboard BI', function () {
    it('eksekutif dapat mengakses halaman dashboard dan melihat visualisasi Power BI', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::EKSEKUTIF->value,
            'guard_name' => 'web',
        ]);

        $eksekutif = User::factory()->create([
            'email' => 'eksekutif-dashboard@pcr.ac.id',
        ]);
        $eksekutif->assignRole(UserRole::EKSEKUTIF->value);

        // Action
        $response = $this
            ->actingAs($eksekutif)
            ->withSession(['active_role' => UserRole::EKSEKUTIF->value])
            ->get(route('app.dashboard.index'));

        // Assertion
        $response->assertOk();
        $response->assertSee('Dashboard Eksekutif');
        $response->assertSee('dashboard-power-bi-presensi-tamu', false);
        $response->assertSee('app.powerbi.com/view', false);
    });

    it('menolak akses dashboard untuk pengguna dengan role security', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::SECURITY->value,
            'guard_name' => 'web',
        ]);

        $security = User::factory()->create([
            'email' => 'security-dashboard@pcr.ac.id',
        ]);
        $security->assignRole(UserRole::SECURITY->value);

        // Action
        $response = $this
            ->actingAs($security)
            ->withSession(['active_role' => UserRole::SECURITY->value])
            ->get(route('app.dashboard.index'));

        // Assertion
        $response->assertForbidden();
    });

    it('menolak akses dashboard untuk pengguna dengan role mahasiswa', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::MAHASISWA->value,
            'guard_name' => 'web',
        ]);

        $mahasiswa = User::factory()->create([
            'email' => 'mahasiswa-dashboard@pcr.ac.id',
        ]);
        $mahasiswa->assignRole(UserRole::MAHASISWA->value);

        // Action
        $response = $this
            ->actingAs($mahasiswa)
            ->withSession(['active_role' => UserRole::MAHASISWA->value])
            ->get(route('app.dashboard.index'));

        // Assertion
        $response->assertForbidden();
    });

    it('mengalihkan pengguna tamu ke halaman login saat mengakses dashboard', function () {
        /** @var Tests\TestCase $this */
        // Setup

        // Action
        $response = $this->get(route('app.dashboard.index'));

        // Assertion
        $response->assertRedirect(route('login'));
    });
});
