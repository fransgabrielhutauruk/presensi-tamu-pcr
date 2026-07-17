<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnableCypressMock
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('local')) {
            return $next($request);
        }

        $userAgent = $request->userAgent() ?? '';
        $isCypressRequest = str_contains($userAgent, 'Cypress')
            || $request->boolean('cy_mock')
            || $request->has('cy_scenario');

        if ($request->hasSession()) {
            if ($request->has('cy_mock')) {
                $request->session()->put('cy_mock', $request->boolean('cy_mock'));
            } elseif ($isCypressRequest) {
                $request->session()->put('cy_mock', true);
            }

            if ($request->has('cy_scenario')) {
                $request->session()->put('cy_scenario', (string) $request->query('cy_scenario'));
            }
        }

        $request->attributes->set('cy_mock', $isCypressRequest || ($request->hasSession() && $request->session()->get('cy_mock', false)));
        $request->attributes->set('cy_scenario', (string) ($request->query('cy_scenario')
            ?? ($request->hasSession() ? $request->session()->get('cy_scenario', '') : '')));

        return $next($request);
    }
}
