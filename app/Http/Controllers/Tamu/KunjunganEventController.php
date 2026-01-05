<?php

namespace App\Http\Controllers\Tamu;

use App\Models\Tamu;
use App\Models\Event;
use App\Models\Civitas;
use App\Models\Kunjungan;
use Illuminate\Http\Request;
use App\Models\KunjunganDetail;
use App\Enums\KategoriTujuanEnum;
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

    public function formPresensiNonCivitas(Request $request, $eventId)
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

    public function storePresensiNonCivitas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required',
            'nama' => 'required',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'nomor_telepon' => 'required|max:20',
            'email' => 'required|email',
            'institusi' => 'required',
            'jabatan' => 'required',
            'jumlah_rombongan' => 'required|integer|min:1|max:50',
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
                'kategori_tujuan' => KategoriTujuanEnum::EVENT->value,
                'identitas' => 'non-civitas',
                'event_id' => $event->event_id,
                'waktu_keluar' => $event->waktu_selesai_event,
                'transportasi' => $request->transportasi,
                'status_validasi' => false,
                'is_checkout' => false,
            ];
            $kunjungan = Kunjungan::create($kunjunganData);
            $detailData = [
                'institusi' => $request->institusi,
                'jabatan' => $request->jabatan,
                'jumlah_rombongan' => $request->jumlah_rombongan,
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
            'nim_nip' => 'required|string|max:20',
            'nama' => 'required',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'nomor_telepon' => 'required|string|max:20',
            'email' => 'required|email',
            'jabatan' => 'required'
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
            
            $nimNip = $request->nim_nip;
            $length = strlen($nimNip);
            
            $civitasData = [
                'nama_civitas' => $request->nama,
                'nim' => $length == 10 ? $nimNip : null,
                'nip' => $length == 6 ? $nimNip : null,
                'jenis_kelamin' => $request->jenis_kelamin,
                'nomor_telepon' => $request->nomor_telepon,
                'email' => $request->email,
            ];
            $civitas = Civitas::create($civitasData);
            
            $kunjunganData = [
                'civitas_id' => $civitas->civitas_id,
                'kategori_tujuan' => KategoriTujuanEnum::EVENT->value,
                'identitas' => 'civitas',
                'event_id' => $event->event_id,
                'waktu_keluar' => $event->waktu_selesai_event,
                'status_validasi' => false,
                'is_checkout' => false,
            ];
            $kunjungan = Kunjungan::create($kunjunganData);
            
            KunjunganDetail::create([
                'kunjungan_id' => $kunjungan->kunjungan_id,
                'kunci' => 'jabatan',
                'nilai' => $request->jabatan,
                'urutan' => 1,
            ]);
            
            $kunjunganIdHashed = encid($kunjungan->kunjungan_id);
            DB::commit();
            return redirect()->route('tamu.sukses', $kunjunganIdHashed);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Gagal menyimpan presensi event civitas: ' . $th->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan. Silahkan coba lagi.');
        }
    }
}
