<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

describe('BBT-11 Mengelola Akses Pengguna', function () {
    it('memberikan hak akses baru kepada pengguna dan menyimpan perubahan role', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);
        Role::firstOrCreate([
            'name' => UserRole::EKSEKUTIF->value,
            'guard_name' => 'web',
        ]);
        Role::firstOrCreate([
            'name' => UserRole::MAHASISWA->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-manage-access@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        $targetUser = User::factory()->create([
            'name' => 'Pengguna Uji Akses',
            'email' => 'pengguna.akses@pcr.ac.id',
        ]);
        $targetUser->assignRole(UserRole::MAHASISWA->value);

        // Action
        $response = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.user.update'), [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $targetUser->email,
                'roles' => [UserRole::EKSEKUTIF->value],
            ]);

        // Assertion
        $response->assertOk()->assertJson([
            'status' => true,
            'message' => 'Update data berhasil.',
        ]);

        $targetUser->refresh();
        expect($targetUser->hasRole(UserRole::EKSEKUTIF->value))->toBeTrue();
        expect($targetUser->hasRole(UserRole::MAHASISWA->value))->toBeTrue();
    });

    it('mencabut hak akses eksekutif dari pengguna dan memperbarui role menjadi admin', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);
        Role::firstOrCreate([
            'name' => UserRole::EKSEKUTIF->value,
            'guard_name' => 'web',
        ]);
        Role::firstOrCreate([
            'name' => UserRole::MAHASISWA->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-revoke-access@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        $targetUser = User::factory()->create([
            'name' => 'Pengguna Eksekutif',
            'email' => 'pengguna.eksekutif@pcr.ac.id',
        ]);
        $targetUser->assignRole([UserRole::MAHASISWA->value, UserRole::EKSEKUTIF->value]);

        // Action
        $response = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.user.update'), [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $targetUser->email,
                'roles' => [UserRole::ADMIN->value],
            ]);

        // Assertion
        $response->assertOk()->assertJson([
            'status' => true,
            'message' => 'Update data berhasil.',
        ]);

        $targetUser->refresh();
        expect($targetUser->hasRole(UserRole::EKSEKUTIF->value))->toBeFalse();
        expect($targetUser->hasRole(UserRole::ADMIN->value))->toBeTrue();
        expect($targetUser->hasRole(UserRole::MAHASISWA->value))->toBeTrue();
    });

    it('menolak pembaruan akses ketika role yang dikirim tidak valid', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);
        Role::firstOrCreate([
            'name' => UserRole::MAHASISWA->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-invalid-role@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        $targetUser = User::factory()->create([
            'name' => 'Pengguna Role Invalid',
            'email' => 'pengguna.role.invalid@pcr.ac.id',
        ]);
        $targetUser->assignRole(UserRole::MAHASISWA->value);

        // Action
        $response = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->from(route('app.user.index'))
            ->post(route('app.user.update'), [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $targetUser->email,
                'roles' => ['SuperAdmin'],
            ]);

        // Assertion
        $response->assertRedirect(route('app.user.index'));
        $response->assertSessionHasErrors(['roles.0']);

        $targetUser->refresh();
        expect($targetUser->hasRole(UserRole::MAHASISWA->value))->toBeTrue();
        expect($targetUser->hasAnyRole([UserRole::ADMIN->value, UserRole::EKSEKUTIF->value, UserRole::SECURITY->value]))
            ->toBeFalse();
    });

    it('mengembalikan 404 ketika memperbarui akses dengan id pengguna yang tidak valid', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-invalid-user-id@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        // Action
        $response = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.user.update'), [
                'id' => 999999,
                'name' => 'User Tidak Ada',
                'email' => 'tidak.ada@pcr.ac.id',
                'roles' => [UserRole::ADMIN->value],
            ]);

        // Assertion
        $response->assertNotFound();
    });
});
