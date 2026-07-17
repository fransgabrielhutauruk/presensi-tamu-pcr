<?php

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

describe('BBT-18 Melihat QR Code Presensi Event', function () {
    it('staf dapat membuka halaman qr code event dan melihat qr beserta tautan presensi', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::STAF->value,
            'guard_name' => 'web',
        ]);

        $staf = User::factory()->create([
            'email' => 'staf-qrcode-event@pcr.ac.id',
        ]);
        $staf->assignRole(UserRole::STAF->value);

        $event = Event::factory()->create([
            'nama_event' => 'Seminar AI PCR',
            'tanggal_event' => now()->toDateString(),
            'waktu_mulai_event' => '09:00:00',
            'waktu_selesai_event' => '11:00:00',
        ]);

        $eventIdEnc = encid((string) $event->event_id);
        $presensiUrl = route('tamu.event.identitas', $eventIdEnc);

        // Action
        $response = $this
            ->actingAs($staf)
            ->withSession(['active_role' => UserRole::STAF->value])
            ->get(route('app.event.qr-code', $eventIdEnc));

        // Assertion
        $response->assertOk();
        $response->assertSee('QR Code Event - Seminar AI PCR');
        $response->assertSee('id="qr-code"', false);
        $response->assertSee('<svg', false);
        $response->assertSee($presensiUrl, false);
    });

    it('mengembalikan qr code svg ketika parameter generate bernilai true', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-generate-qrcode@pcr.ac.id',
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        $event = Event::factory()->create();
        $eventIdEnc = encid((string) $event->event_id);

        // Action
        $response = $this
            ->actingAs($admin)
            ->withSession(['active_role' => UserRole::ADMIN->value])
            ->get(route('app.event.qr-code', $eventIdEnc).'?generate=true');

        // Assertion
        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/svg+xml');
        $response->assertSee('<svg', false);
    });

    it('mengembalikan 404 ketika qr code event diakses dengan id event tidak valid', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::firstOrCreate([
            'name' => UserRole::STAF->value,
            'guard_name' => 'web',
        ]);

        $staf = User::factory()->create([
            'email' => 'staf-invalid-qrcode@pcr.ac.id',
        ]);
        $staf->assignRole(UserRole::STAF->value);

        // Action
        $response = $this
            ->actingAs($staf)
            ->withSession(['active_role' => UserRole::STAF->value])
            ->get(route('app.event.qr-code', encid('999999')));

        // Assertion
        $response->assertNotFound();
    });

    it('mengalihkan pengguna tamu ke halaman login saat mengakses qr code event', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $event = Event::factory()->create();
        $eventIdEnc = encid((string) $event->event_id);

        // Action
        $response = $this->get(route('app.event.qr-code', $eventIdEnc));

        // Assertion
        $response->assertRedirect(route('login'));
    });
});
