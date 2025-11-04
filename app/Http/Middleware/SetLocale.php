<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    protected $availableLocales = ['id', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        if (Session::has('locale') && in_array(Session::get('locale'), $this->availableLocales)) {
            $locale = Session::get('locale');
        } else {
            $locale = config('app.locale');
            Session::put('locale', $locale);
        }

        App::setLocale($locale);

        return $next($request);
    }
}
