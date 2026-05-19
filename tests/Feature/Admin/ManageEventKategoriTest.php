<?php

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\EventKategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

describe('BBT-17 Mengelola Kategori Event', function () {
    it('admin dapat membuka halaman kategori event dan menambahkan kategori baru', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-kategori-event@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        // Action
        $pageResponse = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->get(route('app.event.show', ['param1' => 'kategori']));

        $storeResponse = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.event.store', ['param1' => 'kategori']), [
                'nama_kategori' => 'Kategori Uji Integrasi',
                'deskripsi_kategori' => 'Kategori untuk pengujian feature test.',
            ]);

        // Assertion
        $pageResponse->assertOk()->assertSee('Kelola Kategori Event');

        $storeResponse->assertOk()->assertJson([
            'status' => true,
            'message' => 'Data kategori berhasil disimpan',
        ]);

        $this->assertDatabaseHas('event_kategori', [
            'nama_kategori' => 'Kategori Uji Integrasi',
            'deskripsi_kategori' => 'Kategori untuk pengujian feature test.',
        ]);
    });

    it('admin dapat mengedit data kategori event', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-edit-kategori@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        $kategori = EventKategori::factory()->create([
            'nama_kategori' => 'Kategori Lama',
            'deskripsi_kategori' => 'Deskripsi lama',
        ]);

        // Action
        $response = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.event.update', ['param1' => 'kategori']), [
                'id' => encid((string) $kategori->eventkategori_id),
                'nama_kategori' => 'Kategori Baru',
                'deskripsi_kategori' => 'Deskripsi baru kategori event',
            ]);

        // Assertion
        $response->assertOk()->assertJson([
            'status' => true,
            'message' => 'Data kategori berhasil diperbarui',
        ]);

        $this->assertDatabaseHas('event_kategori', [
            'eventkategori_id' => $kategori->eventkategori_id,
            'nama_kategori' => 'Kategori Baru',
            'deskripsi_kategori' => 'Deskripsi baru kategori event',
        ]);
    });

    it('admin dapat menghapus kategori event yang tidak digunakan', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-delete-kategori@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        $kategori = EventKategori::factory()->create();

        // Action
        $response = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.event.destroy', ['param1' => 'kategori']), [
                'id' => encid((string) $kategori->eventkategori_id),
            ]);

        // Assertion
        $response->assertOk()->assertJson([
            'status' => true,
            'message' => 'Data kategori berhasil dihapus',
        ]);

        $this->assertSoftDeleted('event_kategori', [
            'eventkategori_id' => $kategori->eventkategori_id,
        ]);
    });

    it('menolak penghapusan kategori event yang masih digunakan oleh event', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-delete-kategori-used@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        $kategori = EventKategori::factory()->create();
        Event::factory()->create([
            'eventkategori_id' => $kategori->eventkategori_id,
        ]);

        // Action
        $response = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.event.destroy', ['param1' => 'kategori']), [
                'id' => encid((string) $kategori->eventkategori_id),
            ]);

        // Assertion
        $response->assertStatus(422);
        $response->assertJsonPath('status', false);
        $response->assertJsonPath('message', 'Kategori tidak dapat dihapus karena masih digunakan oleh 1 event');
    });

    it('mengembalikan 404 ketika mengedit kategori event dengan id tidak valid', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-edit-kategori-invalid@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        // Action
        $response = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.event.update', ['param1' => 'kategori']), [
                'id' => encid('999999'),
                'nama_kategori' => 'Kategori Tidak Ada',
                'deskripsi_kategori' => 'Tidak ada data',
            ]);

        // Assertion
        $response->assertNotFound();
    });
});
