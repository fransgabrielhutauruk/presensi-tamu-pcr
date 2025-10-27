<?php

namespace App\Http\Controllers\Tamu;

use App\Models\Feedback;
use App\Models\Kunjungan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

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
            $kunjungan = Kunjungan::with('tamu')->findOrFail(decid($kunjunganId));
            return view('contents.tamu.pages.sukses', compact('kunjungan'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat halaman sukses kunjungan' . $e->getMessage());
            return redirect()->route('tamu.home')->with('error', 'Kunjungan tidak ditemukan');
        }
    }

    public function Checkout($kunjunganId)
    {
        try {
            $kunjungan = Kunjungan::with(['tamu', 'feedback'])->findOrFail(decid($kunjunganId));

            if ($kunjungan->is_checkout) {
                if ($kunjungan->feedback === null) {
                    return redirect()->route('tamu.feedback', $kunjunganId)
                        ->with('info', 'Checkout sudah dilakukan, mohon lengkapi feedback kunjungan Anda.');
                }
                return redirect()->route('tamu.home')
                    ->with('info', 'Anda telah menyelesaikan seluruh proses kunjungan.');
            }
            return view('contents.tamu.pages.checkout-konfirmasi', compact('kunjungan'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat halaman checkout' . $e->getMessage());
            return redirect()->route('tamu.home')->with('error', 'Maaf, terjadi kesalahan saat memproses checkout');
        }
    }

    public function storeCheckout($kunjunganId)
    {
        try {
            $kunjungan = Kunjungan::with('tamu')->findOrFail(decid($kunjunganId));
            $kunjungan->update([
                'is_checkout' => true,
                'checkout_time' => now()
            ]);
            return redirect()->route('tamu.feedback', $kunjunganId)->with('success', 'Waktu checkout berhasil disimpan');
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan checkout kunjungan' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan. Silahkan coba lagi');
        }
    }

    public function feedback($kunjunganId)
    {
        $kunjungan = Kunjungan::with(['tamu', 'feedback'])->findOrFail(decid($kunjunganId));
        if ($kunjungan->feedback === null) {
            return view(
                'contents.tamu.pages.feedback',
                [
                    'kunjunganId' => $kunjunganId
                ]
            );
        } else {
            return redirect()->route('tamu.home')
                ->with('info', 'Anda telah menyelesaikan mengisi feedback.');
        }
    }

    public function storeFeedback(Request $request, $kunjunganId)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data');
        }

        $kunjunganId = decid($kunjunganId);

        try {
            Feedback::create([
                'kunjungan_id' => $kunjunganId,
                'rating' => $request->rating,
                'komentar' => $request->komentar,
            ]);
            return redirect()->route('tamu.home')->with('success', 'Terima kasih atas penilaian Anda!');
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan feedback ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }
}
