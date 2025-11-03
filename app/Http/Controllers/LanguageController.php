<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    protected $availableLocales = ['id', 'en'];

    public function switch(Request $request, $locale)
    {
        if (!in_array($locale, $this->availableLocales)) {
            abort(404, 'Language not supported');
        }
        Session::put('locale', $locale);
        $previousUrl = $request->header('Referer') ?? route('tamu.index');
        $previousUrl = preg_replace('/[?&]lang=[^&]*/', '', $previousUrl);
        return redirect($previousUrl);
    }

    public function current()
    {
        $currentLocale = app()->getLocale();
        $locales = getAvailableLocales();

        return response()->json([
            'current' => $currentLocale,
            'info' => $locales[$currentLocale] ?? $locales['id'],
            'available' => $locales
        ]);
    }
}
