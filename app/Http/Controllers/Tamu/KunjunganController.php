<?php

namespace App\Http\Controllers\Tamu;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Kunjungan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

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

    public function eventType()
    {
        return view('contents.tamu.pages.event-type');
    }

    public function sukses($kunjunganId)
    {
        try {
            $kunjungan = $this->findKunjunganOrFail($kunjunganId, ['tamu', 'details']);

            return view('contents.tamu.pages.sukses', compact('kunjungan'));
        } catch (Throwable $exception) {
            $this->logException('Gagal memuat halaman sukses kunjungan', $exception);

            return redirect()->route('tamu.home')->with('error', 'Kunjungan tidak ditemukan');
        }
    }

    public function Checkout($kunjunganId)
    {
        try {
            $kunjungan = $this->findKunjunganOrFail($kunjunganId, ['tamu', 'feedback']);

            if ($kunjungan->is_checkout) {
                if ($kunjungan->feedback === null) {
                    return redirect()->route('tamu.feedback', $kunjunganId)
                        ->with('info', 'Checkout sudah dilakukan, mohon lengkapi feedback kunjungan Anda.');
                }

                return redirect()->route('tamu.home')
                    ->with('info', 'Anda telah menyelesaikan seluruh proses kunjungan.');
            }

            return view('contents.tamu.pages.checkout-konfirmasi', compact('kunjungan'));
        } catch (Throwable $exception) {
            $this->logException('Gagal memuat halaman checkout', $exception);

            return redirect()->route('tamu.home')->with('error', 'Maaf, terjadi kesalahan saat memproses checkout');
        }
    }

    public function storeCheckout($kunjunganId)
    {
        try {
            $kunjungan = $this->findKunjunganOrFail($kunjunganId);

            $kunjungan->update([
                'is_checkout' => true,
                'checkout_time' => now(),
            ]);

            return redirect()->route('tamu.feedback', $kunjunganId);
        } catch (Throwable $exception) {
            $this->logException('Gagal menyimpan checkout kunjungan', $exception);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan. Silahkan coba lagi');
        }
    }

    public function feedback($kunjunganId)
    {
        $kunjungan = $this->findKunjunganOrFail($kunjunganId, ['tamu', 'feedback']);

        if ($kunjungan->feedback === null) {
            return view(
                'contents.tamu.pages.feedback',
                [
                    'kunjunganId' => $kunjunganId,
                ]
            );
        }

        return redirect()->route('tamu.home')
            ->with('info', 'Anda telah menyelesaikan mengisi feedback.');
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

        try {
            Feedback::create([
                'kunjungan_id' => $this->decodeKunjunganId($kunjunganId),
                'rating' => $validator->validated()['rating'],
                'komentar' => $validator->validated()['komentar'] ?? null,
            ]);

            return redirect()->route('tamu.home')->with('success', 'Terima kasih atas penilaian Anda!');
        } catch (Throwable $exception) {
            $this->logException('Gagal menyimpan feedback', $exception);

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }

    private function findKunjunganOrFail(string $encodedKunjunganId, array $relations = []): Kunjungan
    {
        $query = Kunjungan::query();

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->findOrFail($this->decodeKunjunganId($encodedKunjunganId));
    }

    private function decodeKunjunganId(string $encodedKunjunganId): int
    {
        return (int) decid($encodedKunjunganId);
    }

    private function logException(string $message, Throwable $exception): void
    {
        Log::error($message.': '.$exception->getMessage());
    }
}
