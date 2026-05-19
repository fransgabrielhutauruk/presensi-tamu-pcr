<?php

use App\Enums\UserRole;
use App\Models\Kunjungan;
use App\Models\Tamu;
use App\Models\User;
use Database\Factories\FeedbackFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

describe('BBT-16 Melihat Feedback', function () {
    it('admin dapat membuka detail feedback dan melihat rating serta komentar lengkap', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-feedback@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        $tamu = Tamu::factory()->create([
            'nama_tamu' => 'Tamu Feedback Lengkap',
        ]);

        $kunjungan = Kunjungan::factory()->create([
            'tamu_id' => $tamu->tamu_id,
            'event_id' => null,
            'identitas' => 'non-civitas',
            'kategori_tujuan' => 'instansi',
        ]);

        $feedback = FeedbackFactory::new()->create([
            'kunjungan_id' => $kunjungan->kunjungan_id,
            'rating' => 5,
            'komentar' => 'Pelayanan sangat baik dan proses presensi berjalan lancar.',
        ]);

        // Action
        $pageResponse = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->get(route('app.feedback.index'));

        $detailResponse = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.feedback.data', ['param1' => 'detail']), [
                'id' => encid((string) $feedback->feedback_id),
            ]);

        // Assertion
        $pageResponse->assertOk()->assertSee('Kelola Feedback');

        $detailResponse->assertOk()->assertJson([
            'status' => true,
            'message' => 'Data loaded',
        ]);
        $detailResponse->assertJsonPath('data.rating', 5);
        $detailResponse->assertJsonPath('data.komentar', 'Pelayanan sangat baik dan proses presensi berjalan lancar.');
    });

    it('admin dapat menghapus feedback dan data tidak lagi muncul di daftar', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-delete-feedback@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        $kunjungan = Kunjungan::factory()->create([
            'event_id' => null,
            'identitas' => 'non-civitas',
            'kategori_tujuan' => 'instansi',
        ]);

        $feedback = FeedbackFactory::new()->create([
            'kunjungan_id' => $kunjungan->kunjungan_id,
            'rating' => 4,
            'komentar' => 'Feedback untuk dihapus',
        ]);

        // Action
        $destroyResponse = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.feedback.destroy'), [
                'id' => encid((string) $feedback->feedback_id),
            ]);

        $listResponse = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.feedback.data', ['param1' => 'list']), [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]);

        // Assertion
        $destroyResponse->assertOk()->assertJson([
            'status' => true,
            'message' => 'Data feedback berhasil dihapus',
        ]);

        $this->assertSoftDeleted('feedback', [
            'feedback_id' => $feedback->feedback_id,
        ]);

        $listResponse->assertOk();
        $listResponse->assertJsonMissing([
            'feedback_id' => $feedback->feedback_id,
        ]);
    });

    it('mengembalikan 404 ketika melihat detail feedback dengan id tidak valid', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-detail-feedback-invalid@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        // Action
        $response = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.feedback.data', ['param1' => 'detail']), [
                'id' => encid('999999'),
            ]);

        // Assertion
        $response->assertNotFound();
    });

    it('mengembalikan 404 ketika menghapus feedback dengan id tidak valid', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-delete-feedback-invalid@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        // Action
        $response = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('app.feedback.destroy'), [
                'id' => encid('999999'),
            ]);

        // Assertion
        $response->assertNotFound();
    });
});
