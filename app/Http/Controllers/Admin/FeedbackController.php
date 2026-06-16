<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Html\Column;

class FeedbackController extends Controller
{
    public function __construct()
    {
        $this->activeRoot = 'feedback';
        $this->breadCrump[] = ['title' => 'Feedback', 'link' => route('app.feedback.index')];
    }

    public function index()
    {
        $this->title = 'Kelola Feedback';
        $this->activeMenu = 'feedback';

        $builder = app('datatables.html');
        $dataTable = $builder->serverSide(true)
            ->ajax(route('app.feedback.data') . '/list')
            ->columns([
                Column::make(['title' => 'Aksi', 'data' => 'action', 'orderable' => false, 'className' => 'text-nowrap text-center']),
                Column::make(['title' => 'No', 'data' => 'no', 'orderable' => false, 'className' => 'text-center']),
                Column::make(['title' => 'Dikirim Pada', 'data' => 'feedback_created_at', 'orderable' => true]),
                Column::make(['title' => 'Nama Tamu', 'data' => 'nama_tamu', 'orderable' => true]),
                Column::make(['title' => 'Rating', 'data' => 'rating', 'orderable' => true, 'className' => 'text-center']),
                Column::make(['title' => 'Komentar', 'data' => 'komentar', 'orderable' => false]),
                Column::make(['title' => 'Event', 'data' => 'nama_event', 'orderable' => true]),
            ]);

        $this->dataView([
            'dataTable' => $dataTable,
        ]);

        return $this->view('admin.feedback.list');
    }

    public function data(Request $req, $param1 = ''): JsonResponse
    {
        if ($param1 == 'list') {
            $start       = (int) $req->input('start', 0);
            $filterRating = $req->input('filter_rating', '');

            $query = Feedback::select([
                'feedback.feedback_id',
                'feedback.kunjungan_id',
                'feedback.rating',
                'feedback.komentar',
                'feedback.created_at',
                'kunjungan.created_at as kunjungan_created_at',
                'kunjungan.event_id',
                'tamu.nama_tamu',
                'civitas.nama_civitas',
                'event.nama_event',
            ])
                ->join('kunjungan', function ($join) {
                    $join->on('feedback.kunjungan_id', '=', 'kunjungan.kunjungan_id')
                        ->whereNull('kunjungan.deleted_at');
                })
                ->leftJoin('tamu', function ($join) {
                    $join->on('kunjungan.tamu_id', '=', 'tamu.tamu_id')
                        ->whereNull('tamu.deleted_at');
                })
                ->leftJoin('civitas', function ($join) {
                    $join->on('kunjungan.civitas_id', '=', 'civitas.civitas_id')
                        ->whereNull('civitas.deleted_at');
                })
                ->leftJoin('event', 'kunjungan.event_id', '=', 'event.event_id')
                ->whereNull('feedback.deleted_at')
                ->when(!empty($filterRating), fn($q) => $q->where('feedback.rating', (int) $filterRating));

            return DataTables::of($query)
                ->addColumn('no', function () use (&$start) {
                    return ++$start;
                })
                ->addColumn('nama_tamu', fn($row) => $row->nama_tamu ?? $row->nama_civitas ?? '-')
                ->addColumn('rating', fn($row) => $row->rating ?? 0)
                ->addColumn('komentar', function ($row) {
                    $komentar = $row->komentar ?? '-';
                    return strlen($komentar) > 100 ? substr($komentar, 0, 100) . '...' : $komentar;
                })
                ->addColumn('nama_event', function ($row) {
                    return !empty($row->nama_event)
                        ? $row->nama_event
                        : '<span class="badge badge-secondary">Non-Event</span>';
                })
                ->addColumn('feedback_created_at', function ($row) {
                    return $row->created_at
                        ? tanggal($row->created_at) . ' ' . Carbon::parse($row->created_at)->setTimezone(config('app.timezone'))->format('H:i')
                        : '-';
                })
                ->addColumn('action', function ($row) {
                    $id = encid($row->feedback_id);
                    $dataAction = [
                        'id' => $id,
                        'btn' => [
                            ['action' => 'detail', 'attr' => ['jf-detail' => $id]],
                            ['action' => 'delete', 'attr' => ['jf-delete' => $id]],
                        ]
                    ];
                    return Blade::render('<x-btn.actiontable :id="$id" :btn="$btn"/>', $dataAction);
                })
                ->rawColumns(['nama_event', 'action'])
                ->orderColumn('nama_tamu', 'COALESCE(tamu.nama_tamu, civitas.nama_civitas) $1')
                ->orderColumn('rating', 'feedback.rating $1')
                ->orderColumn('nama_event', 'event.nama_event $1')
                ->orderColumn('feedback_created_at', 'feedback.created_at $1')
                ->filterColumn('nama_tamu', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('tamu.nama_tamu',      'like', "%{$keyword}%")
                            ->orWhere('civitas.nama_civitas', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('rating', fn($query, $keyword) =>
                $query->where('feedback.rating', 'like', "%{$keyword}%"))
                ->filterColumn('komentar', fn($query, $keyword) =>
                $query->where('feedback.komentar', 'like', "%{$keyword}%"))
                ->filterColumn('nama_event', fn($query, $keyword) =>
                $query->where('event.nama_event', 'like', "%{$keyword}%"))
                ->filterColumn('feedback_created_at', fn($query, $keyword) =>
                dtFilterByDateKeyword($query, $keyword, 'feedback.created_at'))
                ->toJson();
        } else if ($param1 == 'detail') {
            validate_and_response([
                'id' => ['Parameter data', 'required'],
            ]);

            $feedbackId = decid($req->input('id'));
            $feedback = Feedback::with(['kunjungan.tamu', 'kunjungan.civitas', 'kunjungan.details', 'kunjungan.event', 'kunjungan.event.eventKategori'])
                ->findOrFail($feedbackId);

            $currData = $feedback->kunjungan;

            $detailData = [
                'feedback_id' => $feedback->feedback_id,
                'kunjungan_id' => $currData->kunjungan_id,
                'id' => $req->input('id'),
                'rating' => $feedback->rating,
                'komentar' => $feedback->komentar ?? '-',

                'nama' => $currData->tamu->nama_tamu ?? $currData->civitas->nama_civitas ?? '-',
                'jenis_kelamin' => $currData->tamu->jenis_kelamin_tamu ?? $currData->civitas->jenis_kelamin ?? '-',
                'email' => $currData->tamu->email_tamu ?? $currData->civitas->email ?? '-',
                'nomor_telepon' => $currData->tamu->nomor_telepon_tamu ?? $currData->civitas->nomor_telepon ?? '-',

                'jenis_kunjungan' => !empty($currData->event_id) ? 'Event' : 'Non-Event',
                'kategori_tujuan' => \App\Enums\KategoriTujuanEnum::getDescription($currData->kategori_tujuan?->value) ?? '-',
                'identitas' => $currData->identitas == 'tamu_luar' ? 'Tamu Luar'
                    : ($currData->identitas == 'civitas_pcr' ? 'Civitas PCR' : ($currData->identitas ?? '')),
                'transportasi' => $currData->transportasi ?? '',
                'status_validasi' => (bool) $currData->status_validasi,
                'is_checkout' => (bool) $currData->is_checkout,

                'tanggal_kunjungan' => $currData->created_at ? tanggal($currData->created_at) : '',
                'waktu_kunjungan' => $currData->created_at ? $currData->created_at->format('H:i') : '-',
                'waktu_keluar' => $currData->waktu_keluar ? \Carbon\Carbon::parse($currData->waktu_keluar)
                    ->format('H:i') : '-',
                'checkout_time' => $currData->checkout_time ? $currData->checkout_time->format('H:i') : '-',

                'event_nama' => $currData->event->nama_event ?? '-',
                'event_kategori' => $currData->event->eventKategori->nama_kategori ?? '-',
                'details' => []
            ];

            foreach ($currData->details as $detail) {
                $detailData['details'][] = [
                    'kunci' => $detail->kunci,
                    'nilai' => $detail->nilai,
                    'urutan' => $detail->urutan
                ];
            }

            return response()->json(['status' => true, 'message' => 'Data loaded', 'data' => $detailData]);
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }

    public function destroy(Request $req, $param1 = ''): JsonResponse
    {
        if ($param1 == '') {
            validate_and_response([
                'id' => ['Parameter data', 'required'],
            ]);

            $currData = Feedback::findOrFail(decid($req->input('id')));

            DB::beginTransaction();
            try {
                $currData->delete();
                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Data feedback berhasil dihapus'
                ]);
            } catch (\Throwable $th) {
                DB::rollBack();
                abort(500, 'Gagal menghapus data, kesalahan database');
            }
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }
}
