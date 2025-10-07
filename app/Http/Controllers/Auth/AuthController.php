<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGoogle($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the Google callback.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleCallback($provider)
    {
        try {
            $googleUser = Socialite::driver($provider)->user();

            $email = $googleUser->getEmail();
            if (! Str::endsWith($email, ['@pcr.ac.id', '@mahasiswa.pcr.ac.id'])) {
                return redirect()->route('login')->with(['error' => 'Hanya email @pcr.ac.id yang diizinkan.']);
            }

            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => bcrypt(uniqid()),
                ]);
            }

            if (Str::endsWith($email, '@pcr.ac.id')) {
                $user->assignRole('Staf');
            } elseif (Str::endsWith($email, '@mahasiswa.pcr.ac.id')) {
                $user->assignRole('Mahasiswa');
            }

            Auth::login($user, true);

            return redirect()->intended('/app/dashboard');
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Google login failed!'. $e);
        }
    }

    public function switchRole(\Illuminate\Http\Request $request)
    {
        $role = $request->input('role');
        $user = Auth::user();
        
        $userRoles = $user->roles->pluck('name')->toArray();
        $allowedRoles = ['Mahasiswa', 'Staf', 'Admin', 'Eksekutif'];
        
        if (!in_array($role, $userRoles) || !in_array($role, $allowedRoles)) {
            return response()->json([
                'status' => false, 
                'message' => 'Role tidak valid atau tidak dimiliki'
            ]);
        }
        
        session(['active_role' => $role]);
        
        return response()->json([
            'status' => true, 
            'message' => 'Role berhasil diubah ke ' . $role
        ]);
    }
}
