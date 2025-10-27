<?php

namespace App\Http\Controllers\Tamu;

use App\Models\Kunjungan;
use App\Http\Controllers\Controller;

class KunjunganController extends Controller
{
    public function index()
    {
        return view('contents.tamu.pages.index');
    }

    public function eventOrNonEvent()
    {
        return view('contents.tamu.pages.event-or-non-event');
    }

    public function sukses($kunjunganId)
    {
        try {
            $kunjungan = Kunjungan::with('tamu')->findOrFail($kunjunganId);
            return view('contents.tamu.pages.success', compact('kunjungan'));
        } catch (\Exception $e) {
            return redirect()->route('tamu.home')->with('error', 'Kunjungan tidak ditemukan.');
        }
    }

    public function Checkout($kunjunganId)
    {
        try {
            $kunjungan = Kunjungan::with('tamu')->findOrFail($kunjunganId);

            if ($kunjungan->is_checkout) {
                return view('contents.tamu.pages.checkout-success', compact('kunjungan'));
            }
            return view('contents.tamu.pages.checkout-confirm', compact('kunjungan'));
        } catch (\Exception $e) {
            return redirect()->route('tamu.home')->with('error', 'Maaf, terjadi kesalahan saat memproses checkout');
        }
    }
}
