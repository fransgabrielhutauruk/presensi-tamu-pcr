<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ['Mahasiswa', 'Staf', 'Eksekutif', 'Admin'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Create test user
        $user = User::firstOrCreate(
            ['email' => 'frans22si@mahasiswa.pcr.ac.id'],
            [
                'name' => 'Frans',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        if ($user->wasRecentlyCreated || !$user->hasAnyRole($roles)) {
            $user->assignRole($roles);
        }
    }
}
