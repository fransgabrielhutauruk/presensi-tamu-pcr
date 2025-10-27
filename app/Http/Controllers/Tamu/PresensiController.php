<?php

namespace App\Http\Controllers\Tamu;

use App\Models\Tamu;
use App\Models\Event;
use App\Models\Feedback;
use App\Models\Kunjungan;
use Illuminate\Http\Request;
use App\Models\KunjunganDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StorePresensiRequest;

class PresensiController extends Controller
{
    public function event()
    {
        return view('contents.tamu.pages.tujuan');
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

    public function storeCheckout($kunjunganId)
    {
        try {
            $kunjungan = Kunjungan::with('tamu')->findOrFail($kunjunganId);
            $kunjungan->update([
                'is_checkout' => true,
                'checkout_time' => now()
            ]);
            return redirect()->route('tamu.feedback', $kunjunganId)
                ->with('success', 'Waktu checkout Anda berhasil terkirim!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan. Silahkan coba lagi');
        }
    }

    public function feedback($kunjunganId)
    {
        $kunjungan = Kunjungan::with('tamu')->findOrFail($kunjunganId);
        return view(
            'contents.tamu.pages.feedback',
            [
                'kunjunganId' => $kunjunganId
            ]
        );
    }

    public function storeFeedback(Request $request, $kunjunganId)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data');
        }

        try {
            Feedback::create([
                'kunjungan_id' => $kunjunganId,
                'rating' => $request->rating,
                'komentar' => $request->komentar,
            ]);
            return redirect()->route('tamu.home')->with('success', 'Terima kasih atas penilaian Anda!');
        } catch (\Exception $e) {
            Log::error('Failed ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }

    public function eventList(Request $request)
    {
        $currentDate = now()->format('Y-m-d');

        $events = Event::with('eventKategori')
            ->where('tanggal_event', '=', $currentDate)
            ->orderBy('tanggal_event', 'asc')
            ->orderBy('waktu_mulai_event', 'asc')
            ->get();

        return view('contents.tamu.pages.event-list', compact('events'));
    }

    public function eventIdentitySelection(Request $request)
    {
        $eventId = $request->get('event_id');

        if (!$eventId) {
            return redirect()->route('tamu.home')->with('error', 'Event tidak ditemukan.');
        }

        try {
            $event = Event::with('eventKategori')->findOrFail(decid($eventId));

            $eventDate = $event->tanggal_event;
            $currentDate = now()->format('Y-m-d');

            if ($eventDate && $eventDate < $currentDate) {
                return redirect()->route('tamu.home')->with('error', 'Event ini sudah berakhir.');
            }

            return view('contents.tamu.pages.event-identity-selection', compact('event', 'eventId'));
        } catch (\Exception $e) {
            return redirect()->route('tamu.home')->with('error', 'Event tidak ditemukan.');
        }
    }

    public function eventForm(Request $request)
    {
        $eventId = $request->get('event_id');
        $identitas = $request->get('identitas', 'tamu_luar');

        if (!$eventId) {
            return redirect()->route('tamu.home')->with('error', 'Event tidak ditemukan.');
        }

        try {
            $event = Event::with('eventKategori')->findOrFail(decid($eventId));

            $eventDate = $event->tanggal_event;
            $currentDate = now()->format('Y-m-d');

            if ($eventDate && $eventDate < $currentDate) {
                return redirect()->route('tamu.home')->with('error', 'Event ini sudah berakhir.');
            }

            return view('contents.tamu.pages.event-form', compact('event', 'eventId', 'identitas'));
        } catch (\Exception $e) {
            return redirect()->route('tamu.home')->with('error', 'Event tidak ditemukan.');
        }
    }

    public function eventCivitasForm(Request $request)
    {
        $eventId = $request->get('event_id');
        $identitas = $request->get('identitas', 'civitas_pcr');

        if (!$eventId) {
            return redirect()->route('tamu.home')->with('error', 'Event tidak ditemukan.');
        }

        try {
            $event = Event::with('eventKategori')->findOrFail(decid($eventId));

            $eventDate = $event->tanggal_event;
            $currentDate = now()->format('Y-m-d');

            if ($eventDate && $eventDate < $currentDate) {
                return redirect()->route('tamu.home')->with('error', 'Event ini sudah berakhir.');
            }

            return view('contents.tamu.pages.event-civitas-form', compact('event', 'eventId', 'identitas'));
        } catch (\Exception $e) {
            return redirect()->route('tamu.home')->with('error', 'Event tidak ditemukan.');
        }
    }

    public function storeEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|string',
            'name' => 'required|string',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'email' => 'required|email',
            'phone_number' => 'required|string|max:20',
            'instansi' => 'nullable|string',
            'jabatan' => 'nullable|string',
            'transportasi' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Mohon periksa kembali data yang Anda masukkan.');
        }

        try {
            $event = Event::findOrFail(decid($request->event_id));

            DB::beginTransaction();

            $tamuData = [
                'name' => $request->name,
                'gender' => $request->gender,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'created_by' => 'system'
            ];

            $tamu = Tamu::create($tamuData);

            $kunjunganData = [
                'tamu_id' => $tamu->tamu_id,
                'kategori_tujuan' => 'event',
                'identitas' => 'tamu_luar',
                'event_id' => $event->event_id,
                'waktu_keluar' => $event->waktu_selesai_event,
                'transportasi' => $request->transportasi,
                'status_validasi' => false,
                'is_checkout' => false,
            ];

            $kunjungan = Kunjungan::create($kunjunganData);

            $detailData = [
                'nama_event' => $event->nama_event,
                'kategori_event' => $event->eventKategori->nama_kategori ?? '',
                'instansi' => $request->instansi,
                'jabatan' => $request->jabatan,
            ];

            $urutan = 1;
            foreach ($detailData as $key => $value) {
                if (!empty($value)) {
                    KunjunganDetail::create([
                        'kunjungan_id' => $kunjungan->kunjungan_id,
                        'kunci' => $key,
                        'nilai' => $value,
                        'urutan' => $urutan++,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('tamu.sukses', $kunjungan->kunjungan_id)
                ->with('success', 'Presensi event berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Event presensi failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan. Silahkan coba lagi.');
        }
    }

    public function storeCivitasEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|string',
            'identitas' => 'required|in:civitas_pcr',
            'jenis_civitas' => 'required|in:dosen,staff,mahasiswa',
            'nim_nip' => 'required|string|max:20',
            'nama_lengkap' => 'required|string|max:255',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'email' => 'required|email|max:255',
            'phone_number' => 'required|string|max:20',
            'prodi_unit' => 'nullable|string|max:255',
            'transportasi' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Mohon periksa kembali data yang Anda masukkan.');
        }

        try {
            $event = Event::findOrFail(decid($request->event_id));

            DB::beginTransaction();

            $tamuData = [
                'name' => $request->nama_lengkap,
                'gender' => $request->gender,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'created_by' => 'system'
            ];

            $tamu = Tamu::create($tamuData);

            $kunjunganData = [
                'tamu_id' => $tamu->tamu_id,
                'kategori_tujuan' => 'event',
                'identitas' => 'civitas_pcr',
                'event_id' => $event->event_id,
                'waktu_keluar' => $event->waktu_selesai_event,
                'transportasi' => $request->transportasi,
                'status_validasi' => false,
                'is_checkout' => false,
            ];

            $kunjungan = Kunjungan::create($kunjunganData);

            $detailData = [
                'nama_event' => $event->nama_event,
                'kategori_event' => $event->eventKategori->nama_kategori ?? '',
                'jenis_civitas' => $request->jenis_civitas,
                'nim_nip' => $request->nim_nip,
                'prodi_unit' => $request->prodi_unit,
            ];

            $urutan = 1;
            foreach ($detailData as $key => $value) {
                if (!empty($value)) {
                    KunjunganDetail::create([
                        'kunjungan_id' => $kunjungan->kunjungan_id,
                        'kunci' => $key,
                        'nilai' => $value,
                        'urutan' => $urutan++,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('tamu.sukses', $kunjungan->kunjungan_id)
                ->with('success', 'Presensi event civitas PCR berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Civitas event presensi failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan. Silahkan coba lagi.');
        }
    }
}
