<?php

namespace App\Http\Controllers\Tamu;

use App\Models\Tamu;
use App\Models\Event;
use App\Models\Kunjungan;
use Illuminate\Http\Request;
use App\Models\KunjunganDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class KunjunganEventController extends Controller
{
    public function listEvent(Request $request)
    {
        $currentDate = now()->format('Y-m-d');

        $events = Event::with('eventKategori')
            ->where('tanggal_event', '=', $currentDate)
            ->orderBy('waktu_mulai_event', 'asc')
            ->get();

        return view('contents.tamu.pages.event.list-event', compact('events'));
    }

    public function identitas(Request $request, $eventId)
    {
        try {
            $event = Event::with('eventKategori')->findOrFail(decid($eventId));
            $eventDate = $event->tanggal_event;
            $currentDate = now()->format('Y-m-d');
            if ($eventDate && $eventDate < $currentDate) {
                return redirect()->route('tamu.home')->with('error', 'Event ini sudah berakhir.');
            }
            return view('contents.tamu.pages.event.identitas', compact('event', 'eventId'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat halaman identitas' . $e->getMessage());
            return redirect()->route('tamu.home')->with('error', 'Event tidak ditemukan.');
        }
    }

    public function formPresensiLuar(Request $request, $eventId)
    {
        try {
            $event = Event::with('eventKategori')->findOrFail(decid($eventId));
            $eventDate = $event->tanggal_event;
            $currentDate = now()->format('Y-m-d');
            if ($eventDate && $eventDate < $currentDate) {
                return redirect()->route('tamu.home')->with('warning', 'Event ini sudah berakhir.');
            }

            return view('contents.tamu.pages.event.form-presensi', compact('event', 'eventId'));
        } catch (\Exception $e) {
            return redirect()->route('tamu.home')->with('warning', 'Event tidak ditemukan.');
        }
    }

    public function storePresensiLuar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required',
            'nama' => 'required',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'nomor_telepon' => 'required|max:20',
            'email' => 'required|email',
            'institusi' => 'required',
            'jabatan' => 'required',
            'transportasi' => 'required',
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
                'nama_tamu' => $request->nama,
                'jenis_kelamin_tamu' => $request->jenis_kelamin,
                'nomor_telepon_tamu' => $request->nomor_telepon,
                'email_tamu' => $request->email,
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

            $kunjunganIdHashed = encid($kunjungan->kunjungan_id);
            DB::commit();
            return redirect()->route('tamu.sukses', $kunjunganIdHashed);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan presensi luar: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan. Silahkan coba lagi.');
        }
    }

    public function formPresensiCivitas(Request $request, $eventId)
    {
        try {
            $event = Event::with('eventKategori')->findOrFail(decid($eventId));
            $eventDate = $event->tanggal_event;
            $currentDate = now()->format('Y-m-d');
            if ($eventDate && $eventDate < $currentDate) {
                return redirect()->route('tamu.home')->with('error', 'Event ini sudah berakhir.');
            }
            return view('contents.tamu.pages.event.form-presensi-civitas', compact('event', 'eventId'));
        } catch (\Exception $e) {
            return redirect()->route('tamu.home')->with('error', 'Event tidak ditemukan.');
        }
    }

    public function storePresensiCivitas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required',
            'nama' => 'required',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'email' => 'required|email',
            'nomor_telepon' => 'required|string|max:20',
            'nim_nip' => 'required|string|max:20',
            'transportasi' => 'required',
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
                'nama_tamu' => $request->nama,
                'jenis_kelamin_tamu' => $request->jenis_kelamin,
                'email_tamu' => $request->email,
                'nomor_telepon_tamu' => $request->nomor_telepon,
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
                'nim_nip' => $request->nim_nip,
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
            $kunjunganIdHashed = encid($kunjungan->kunjungan_id);
            DB::commit();
            return redirect()->route('tamu.sukses', $kunjunganIdHashed)
                ->with('success', 'Presensi event civitas PCR berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan presensi event civitas: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan. Silahkan coba lagi.');
        }
    }
}
