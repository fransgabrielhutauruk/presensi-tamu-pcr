<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

describe('BBT-12 Melihat Log Aktivitas', function () {
    it('menampilkan halaman log aktivitas dan memuat daftar log untuk admin', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin Log',
            'email' => 'admin-log@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        $subjectUser = User::factory()->create([
            'name' => 'Subjek Aktivitas',
            'email' => 'subjek-aktivitas@pcr.ac.id',
        ]);

        Activity::query()->create([
            'log_name' => 'default',
            'description' => 'Melihat log aktivitas pengujian',
            'subject_type' => User::class,
            'subject_id' => $subjectUser->id,
            'causer_type' => User::class,
            'causer_id' => $admin->id,
            'properties' => ['source' => 'feature-test'],
            'event' => 'updated',
        ]);

        // Action
        $pageResponse = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->get(route('app.log-aktivitas.index'));

        $dataResponse = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.log-aktivitas.data', ['param1' => 'list']), [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]);

        // Assertion
        $pageResponse->assertOk()->assertSee('Log Aktivitas');

        $dataResponse->assertOk();
        $dataResponse->assertJsonFragment([
            'description' => 'Melihat log aktivitas pengujian',
            'user' => 'Admin Log',
        ]);
    });

    it('memfilter daftar log berdasarkan user, event, dan subject dengan hasil yang sesuai', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin Filter Log',
            'email' => 'admin-filter-log@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        $operatorLain = User::factory()->create([
            'name' => 'Operator Lain',
            'email' => 'operator-lain@pcr.ac.id',
        ]);

        $subjectUser = User::factory()->create();

        Activity::query()->create([
            'log_name' => 'default',
            'description' => 'Aktivitas target untuk filter',
            'subject_type' => User::class,
            'subject_id' => $subjectUser->id,
            'causer_type' => User::class,
            'causer_id' => $admin->id,
            'properties' => ['source' => 'feature-test'],
            'event' => 'updated',
        ]);

        Activity::query()->create([
            'log_name' => 'default',
            'description' => 'Aktivitas pembanding',
            'subject_type' => User::class,
            'subject_id' => $subjectUser->id,
            'causer_type' => User::class,
            'causer_id' => $operatorLain->id,
            'properties' => ['source' => 'feature-test'],
            'event' => 'created',
        ]);

        // Action
        $response = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.log-aktivitas.data', ['param1' => 'list']), [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'filter_user' => $admin->id,
                'filter_event' => 'updated',
                'filter_subject' => User::class,
            ]);

        // Assertion
        $response->assertOk();
        $response->assertJsonFragment([
            'description' => 'Aktivitas target untuk filter',
            'user' => 'Admin Filter Log',
            'subject_type' => 'User',
        ]);
        $response->assertJsonMissing([
            'description' => 'Aktivitas pembanding',
        ]);
    });

    it('mengembalikan daftar kosong saat filter tanggal tidak memiliki kecocokan log', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin Tanggal Log',
            'email' => 'admin-tanggal-log@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        $subjectUser = User::factory()->create();

        Activity::query()->create([
            'log_name' => 'default',
            'description' => 'Log untuk pengujian tanggal',
            'subject_type' => User::class,
            'subject_id' => $subjectUser->id,
            'causer_type' => User::class,
            'causer_id' => $admin->id,
            'properties' => ['source' => 'feature-test'],
            'event' => 'updated',
        ]);

        // Action
        $response = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.log-aktivitas.data', ['param1' => 'list']), [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'filter_date_from' => now()->addDay()->toDateString(),
                'filter_date_to' => now()->addDay()->toDateString(),
            ]);

        // Assertion
        $response->assertOk();
        expect($response->json('data'))->toBeArray()->toHaveCount(0);
    });

    it('mengembalikan 404 ketika detail log diminta dengan id yang tidak ditemukan', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin Detail Log',
            'email' => 'admin-detail-log@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        // Action
        $response = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.log-aktivitas.data', ['param1' => 'detail']), [
                'id' => 999999,
            ]);

        // Assertion
        $response->assertNotFound()->assertJson([
            'status' => false,
            'message' => 'Log tidak ditemukan',
        ]);
    });
});
