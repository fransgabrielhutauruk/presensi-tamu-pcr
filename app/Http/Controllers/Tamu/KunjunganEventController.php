<?php

namespace App\Http\Controllers\Tamu;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class KunjunganEventController extends Controller
{
    public function ListEvent(Request $request)
    {
        $currentDate = now()->format('Y-m-d');

        $events = Event::with('eventKategori')
            ->where('tanggal_event', '=', $currentDate)
            ->orderBy('tanggal_event', 'asc')
            ->orderBy('waktu_mulai_event', 'asc')
            ->get();

        return view('contents.tamu.pages.event-list', compact('events'));
    }

    // public function identitas(Request $request)
    // {
    //     $eventId = $request->get('event_id');

    //     if (!$eventId) {
    //         return redirect()->route('tamu.home')->with('error', 'Event tidak ditemukan.');
    //     }

    //     try {
    //         $event = Event::with('eventKategori')->findOrFail(decid($eventId));

    //         $eventDate = $event->tanggal_event;
    //         $currentDate = now()->format('Y-m-d');

    //         if ($eventDate && $eventDate < $currentDate) {
    //             return redirect()->route('tamu.home')->with('error', 'Event ini sudah berakhir.');
    //         }

    //         return view('contents.tamu.pages.event-identity-selection', compact('event', 'eventId'));
    //     } catch (\Exception $e) {
    //         return redirect()->route('tamu.home')->with('error', 'Event tidak ditemukan.');
    //     }
    // }


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
}
