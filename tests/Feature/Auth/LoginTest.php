<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

describe('BBT-1 Login', function () {
    it('login sukses google dengan akun kampus satu role lalu masuk ke dashboard role', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::create([
            'name' => UserRole::STAF->value,
            'guard_name' => 'web',
        ]);

        $googleUser = mock(SocialiteUserContract::class);
        $googleUser->shouldReceive('getEmail')->andReturn('staf@pcr.ac.id');
        $googleUser->shouldReceive('getName')->andReturn('Staf PCR');

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturnSelf();
        Socialite::shouldReceive('user')
            ->once()
            ->andReturn($googleUser);

        // Action
        $response = $this->get(route('login.google.callback', ['provider' => 'google']));

        // Assertion
        $response->assertRedirect('/app/event');
        $this->assertAuthenticated();

        $user = User::query()->where('email', 'staf@pcr.ac.id')->first();
        expect($user)->not->toBeNull();
        expect($user->hasRole(UserRole::STAF->value))->toBeTrue();
        expect($user->roles()->count())->toBe(1);
    });

    it('login sukses untuk akun kampus yang memiliki multi role', function () {
        /** @var Tests\TestCase $this */
        // Setup
        Role::create([
            'name' => UserRole::STAF->value,
            'guard_name' => 'web',
        ]);
        Role::create([
            'name' => UserRole::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'name' => 'Multi Role User',
            'email' => 'multi@pcr.ac.id',
        ]);
        $user->assignRole([UserRole::STAF->value, UserRole::ADMIN->value]);

        $googleUser = mock(SocialiteUserContract::class);
        $googleUser->shouldReceive('getEmail')->andReturn('multi@pcr.ac.id');
        $googleUser->shouldReceive('getName')->andReturn('Multi Role User');

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturnSelf();
        Socialite::shouldReceive('user')
            ->once()
            ->andReturn($googleUser);

        // Action
        $response = $this->get(route('login.google.callback', ['provider' => 'google']));

        // Assertion
        $response->assertRedirect('/app/event');
        $this->assertAuthenticatedAs($user);
        expect($user->fresh()->roles()->count())->toBeGreaterThanOrEqual(2);
    });

    it('menolak login untuk akun non kampus', function () {
        /** @var Tests\TestCase $this */
        // Setup
        $googleUser = mock(SocialiteUserContract::class);
        $googleUser->shouldReceive('getEmail')->andReturn('user@gmail.com');
        $googleUser->shouldReceive('getName')->andReturn('User Gmail');

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturnSelf();
        Socialite::shouldReceive('user')
            ->once()
            ->andReturn($googleUser);

        // Action
        $response = $this->get(route('login.google.callback', ['provider' => 'google']));

        // Assertion
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Akses ditolak. Gunakan email @pcr.ac.id atau gunakan email yang sudah didaftarkan oleh Admin.');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'email' => 'user@gmail.com',
        ]);
    });
});
