<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $activeRole = getActiveRole();
        
        if (!$activeRole || !in_array($activeRole, $roles)) {
            abort(403, 'Anda tidak memiliki akses untuk halaman ini dengan role ' . ($activeRole ?? 'tanpa role'));
        }
        
        return $next($request);
    }
}
