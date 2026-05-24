<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Enums\UserRole;
use App\Services\CypressTestingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function __construct(private CypressTestingService $cypressTestingService)
    {
    }

    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGoogle(Request $request, $provider)
    {
        if ($this->cypressTestingService->shouldUseMockCallback($request)) {
            return redirect()->route('login.google.callback', [
                'provider' => $provider,
                'cy_mock' => 1,
                'cy_scenario' => $this->cypressTestingService->getScenario($request, 'single-role'),
            ]);
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the Google callback.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleCallback(Request $request, $provider)
    {
        try {
            [$email, $name, $mockRoles] = $this->resolveGoogleIdentity($request, $provider);
            if (! Str::endsWith($email, ['@pcr.ac.id', '@mahasiswa.pcr.ac.id'])) {
                return redirect()->route('login')->with(['error' => 'Hanya email @pcr.ac.id yang diizinkan.']);
            }

            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => bcrypt(uniqid()),
                ]);
            }

            $this->assignRoles($user, $email, $mockRoles);

            Auth::login($user, true);

            activity()
                ->causedBy($user)
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_agent' => request()->header('User-Agent')
                ])
                ->log('Login ke sistem');

            request()->session()->regenerate();
            $this->setMockActiveRoleIfNeeded($request);

            return redirect()->intended('/app/event');
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Google login failed!');
        }
    }

    public function switchRole(Request $request)
    {
        $role = $request->input('role');
        $user = Auth::user();

        $userRoles = $user->roles->pluck('name')->toArray();
        $allowedRoles = UserRole::getAllRoles();

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

    private function resolveGoogleIdentity(Request $request, string $provider): array
    {
        if ($this->cypressTestingService->isMockEnabled($request)
            && ($request->boolean('cy_mock') || $request->attributes->get('cy_mock', false) === true)) {
            return $this->cypressTestingService->resolveMockIdentity($request);
        }

        $googleUser = Socialite::driver($provider)->user();

        return [
            $googleUser->getEmail(),
            $googleUser->getName(),
            [],
        ];
    }

    private function assignRoles(User $user, string $email, array $mockRoles = []): void
    {
        if (!empty($mockRoles)) {
            foreach ($mockRoles as $roleName) {
                Role::firstOrCreate([
                    'name' => $roleName,
                    'guard_name' => 'web',
                ]);
            }

            $user->syncRoles($mockRoles);
            return;
        }

        if (Str::endsWith($email, '@pcr.ac.id')) {
            Role::firstOrCreate([
                'name' => UserRole::STAF->value,
                'guard_name' => 'web',
            ]);
            $user->assignRole(UserRole::STAF->value);
        } elseif (Str::endsWith($email, '@mahasiswa.pcr.ac.id')) {
            Role::firstOrCreate([
                'name' => UserRole::MAHASISWA->value,
                'guard_name' => 'web',
            ]);
            $user->assignRole(UserRole::MAHASISWA->value);
        }
    }

    private function setMockActiveRoleIfNeeded(Request $request): void
    {
        $this->cypressTestingService->setMockActiveRole($request);
    }
}
