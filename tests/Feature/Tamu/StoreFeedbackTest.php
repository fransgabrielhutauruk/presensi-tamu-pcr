<?php

use Database\Factories\KunjunganFactory;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('BBT-5 Pengisian Feedback', function () {
    it('menyimpan feedback lengkap lalu menampilkan pesan sukses dan masuk ke halaman penutup', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $kunjungan = KunjunganFactory::new()->create([
            'is_checkout' => true,
            'checkout_time' => now(),
        ]);

        $kunjunganIdHashed = encid((string) $kunjungan->kunjungan_id);

        $payload = [
            'rating' => 5,
            'komentar' => 'Pelayanan ramah dan proses cepat.',
        ];

        // Action
        $response = $this->post(route('tamu.feedback-store', $kunjunganIdHashed), $payload);

        // Assertion
        $response->assertRedirect(route('tamu.home'));
        $response->assertSessionHas('success', 'Terima kasih atas penilaian Anda!');

        $this->assertDatabaseHas('feedback', [
            'kunjungan_id' => $kunjungan->kunjungan_id,
            'rating' => 5,
            'komentar' => 'Pelayanan ramah dan proses cepat.',
        ]);
    });

    it('menyimpan feedback saat hanya rating diisi', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $kunjungan = KunjunganFactory::new()->create([
            'is_checkout' => true,
            'checkout_time' => now(),
        ]);

        $kunjunganIdHashed = encid((string) $kunjungan->kunjungan_id);

        $payload = [
            'rating' => 4,
            'komentar' => '',
        ];

        // Action
        $response = $this->post(route('tamu.feedback-store', $kunjunganIdHashed), $payload);

        // Assertion
        $response->assertRedirect(route('tamu.home'));
        $response->assertSessionHas('success', 'Terima kasih atas penilaian Anda!');

        $this->assertDatabaseHas('feedback', [
            'kunjungan_id' => $kunjungan->kunjungan_id,
            'rating' => 4,
            'komentar' => null,
        ]);
    });

    it('menolak feedback saat hanya komentar diisi tanpa rating', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $kunjungan = KunjunganFactory::new()->create([
            'is_checkout' => true,
            'checkout_time' => now(),
        ]);

        $kunjunganIdHashed = encid((string) $kunjungan->kunjungan_id);

        $payload = [
            'komentar' => 'Layanan sudah baik, pertahankan.',
        ];

        // Action
        $response = $this
            ->from(route('tamu.feedback', $kunjunganIdHashed))
            ->post(route('tamu.feedback-store', $kunjunganIdHashed), $payload);

        // Assertion
        $response->assertRedirect(route('tamu.feedback', $kunjunganIdHashed));
        $response->assertSessionHas('error', 'Terjadi kesalahan saat menyimpan data');

        $this->assertDatabaseCount('feedback', 0);
    });
});
