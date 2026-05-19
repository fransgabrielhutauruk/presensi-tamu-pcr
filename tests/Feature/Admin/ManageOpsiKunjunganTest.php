<?php

use App\Enums\UserRole;
use App\Models\MstOpsiKunjungan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

describe('BBT-19 Mengelola Opsi Kunjungan', function () {
    it('admin dapat membuka halaman opsi kunjungan, menambah opsi baru, dan opsi muncul di form presensi tamu', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-opsi-kunjungan@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        $nilaiOpsi = [
            ['id' => 'Direktur PCR', 'en' => 'PCR Director'],
            ['id' => 'Wakil Direktur', 'en' => 'Vice Director'],
        ];

        // Action
        $pageResponse = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->get(route('app.kunjungan.show', ['param1' => 'opsi']));

        $storeResponse = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.kunjungan.store', ['param1' => 'opsi']), [
                'nama_opsi' => 'pihak_dituju',
                'deskripsi_opsi' => 'Daftar pihak yang dapat dituju tamu.',
                'nilai_opsi' => json_encode($nilaiOpsi),
            ]);

        $formResponse = $this->get(route('tamu.non-event.form-presensi', ['tujuan' => 'instansi']));

        // Assertion
        $pageResponse->assertOk()->assertSee('Kelola Opsi Kunjungan');

        $storeResponse->assertOk()->assertJson([
            'status' => true,
            'message' => 'Data Opsi kunjungan berhasil disimpan',
        ]);

        $this->assertDatabaseHas('mst_opsi_kunjungan', [
            'nama_opsi' => 'pihak_dituju',
            'deskripsi_opsi' => 'Daftar pihak yang dapat dituju tamu.',
        ]);

        $formResponse->assertOk();
        $formResponse->assertSee('Direktur PCR');
    });

    it('admin dapat mengedit opsi kunjungan dan perubahan muncul di form presensi tamu', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-update-opsi-kunjungan@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        $opsi = MstOpsiKunjungan::create([
            'nama_opsi' => 'pihak_dituju',
            'deskripsi_opsi' => 'Opsional awal',
            'nilai_opsi' => [
                ['id' => 'Direktur Lama', 'en' => 'Old Director'],
            ],
        ]);

        $payload = [
            'id' => encid((string) $opsi->opsikunjungan_id),
            'nama_opsi' => 'pihak_dituju',
            'deskripsi_opsi' => 'Opsional update',
            'nilai_opsi' => json_encode([
                ['id' => 'Direktur Baru', 'en' => 'New Director'],
            ]),
        ];

        // Action
        $updateResponse = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.kunjungan.update', ['param1' => 'opsi']), $payload);

        $formResponse = $this->get(route('tamu.non-event.form-presensi', ['tujuan' => 'instansi']));

        // Assertion
        $updateResponse->assertOk()->assertJson([
            'status' => true,
            'message' => 'Opsi kunjungan berhasil diperbarui',
        ]);

        $this->assertDatabaseHas('mst_opsi_kunjungan', [
            'opsikunjungan_id' => $opsi->opsikunjungan_id,
            'nama_opsi' => 'pihak_dituju',
            'deskripsi_opsi' => 'Opsional update',
        ]);

        $formResponse->assertOk();
        $formResponse->assertSee('Direktur Baru');
        $formResponse->assertDontSee('Direktur Lama');
    });

    it('admin dapat menghapus opsi kunjungan dan item tidak lagi muncul di form presensi tamu', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-delete-opsi-kunjungan@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        $opsi = MstOpsiKunjungan::create([
            'nama_opsi' => 'pihak_dituju',
            'deskripsi_opsi' => 'Hapus opsi ini',
            'nilai_opsi' => [
                ['id' => 'Option Hapus', 'en' => 'Delete Option'],
            ],
        ]);

        // Action
        $destroyResponse = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.kunjungan.destroy', ['param1' => 'opsi']), [
                'id' => encid((string) $opsi->opsikunjungan_id),
            ]);

        $formResponse = $this->get(route('tamu.non-event.form-presensi', ['tujuan' => 'instansi']));

        // Assertion
        $destroyResponse->assertOk()->assertJson([
            'status' => true,
            'message' => 'Opsi kunjungan berhasil dihapus',
        ]);

        $this->assertSoftDeleted('mst_opsi_kunjungan', [
            'opsikunjungan_id' => $opsi->opsikunjungan_id,
        ]);

        $formResponse->assertOk();
        $formResponse->assertDontSee('Option Hapus');
    });

    it('menolak penyimpanan opsi kunjungan ketika format nilai opsi tidak valid', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-invalid-format-opsi@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        // Action
        $response = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.kunjungan.store', ['param1' => 'opsi']), [
                'nama_opsi' => 'pihak_dituju',
                'deskripsi_opsi' => 'Format tidak valid',
                'nilai_opsi' => json_encode([
                    ['id' => 'Hanya Indonesia'],
                ]),
            ]);

        // Assertion
        $response->assertStatus(422)->assertJson([
            'status' => false,
            'message' => 'Format data tidak valid. Setiap item harus memiliki id (Indonesia) dan en (English).',
        ]);

        while (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
    });

    it('mengembalikan 404 ketika menghapus opsi kunjungan dengan id tidak valid', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-invalid-id-delete-opsi@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        // Action
        $response = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.kunjungan.destroy', ['param1' => 'opsi']), [
                'id' => encid('999999'),
            ]);

        // Assertion
        $response->assertNotFound();
    });
});
