<?php

use App\Models\Kunjungan;
use Database\Factories\FeedbackFactory;
use Database\Factories\KunjunganFactory;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('BBT-4 Konfirmasi Checkout', function () {
    it('melakukan checkout normal dari tautan valid lalu mengarahkan ke halaman feedback', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $kunjungan = KunjunganFactory::new()->create([
            'identitas' => 'non-civitas',
            'is_checkout' => false,
            'checkout_time' => null,
        ]);

        $kunjunganIdHashed = encid((string) $kunjungan->kunjungan_id);

        // Action
        $response = $this->post(route('tamu.checkout-store', $kunjunganIdHashed));

        // Assertion
        $response->assertRedirect(route('tamu.feedback', $kunjunganIdHashed));

        $kunjungan->refresh();

        expect($kunjungan->is_checkout)->toBeTrue();
        expect($kunjungan->checkout_time)->not->toBeNull();

        $this->assertDatabaseHas('kunjungan', [
            'kunjungan_id' => $kunjungan->kunjungan_id,
            'is_checkout' => true,
        ]);

        $this->get(route('tamu.feedback', $kunjunganIdHashed))
            ->assertOk()
            ->assertSee(__('visitor.visitor_feedback'));
    });

    it('mengalihkan ke halaman feedback saat tautan checkout dipakai ulang dan feedback belum diisi', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $kunjungan = KunjunganFactory::new()->create([
            'identitas' => 'non-civitas',
            'is_checkout' => true,
            'checkout_time' => now(),
        ]);

        $kunjunganIdHashed = encid((string) $kunjungan->kunjungan_id);

        // Action
        $response = $this->get(route('tamu.checkout', $kunjunganIdHashed));

        // Assertion
        $response->assertRedirect(route('tamu.feedback', $kunjunganIdHashed));
        $response->assertSessionHas('info', 'Checkout sudah dilakukan, mohon lengkapi feedback kunjungan Anda.');
    });

    it('mengalihkan ke halaman home saat tautan checkout dipakai ulang dan feedback sudah diisi', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $kunjungan = KunjunganFactory::new()->create([
            'identitas' => 'non-civitas',
            'is_checkout' => true,
            'checkout_time' => now(),
        ]);

        FeedbackFactory::new()->create([
            'kunjungan_id' => $kunjungan->kunjungan_id,
            'rating' => 5,
            'komentar' => 'Pelayanan sangat baik',
        ]);

        $kunjunganIdHashed = encid((string) $kunjungan->kunjungan_id);

        // Action
        $response = $this->get(route('tamu.checkout', $kunjunganIdHashed));

        // Assertion
        $response->assertRedirect(route('tamu.home'));
        $response->assertSessionHas('info', 'Anda telah menyelesaikan seluruh proses kunjungan.');
    });
});
