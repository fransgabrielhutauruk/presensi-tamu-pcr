<?php

namespace App\Http\Controllers\Admin;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Blade;

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
                Column::make(['width' => '5%', 'title' => 'No', 'data' => 'no', 'orderable' => false, 'className' => 'text-center']),
                Column::make(['title' => 'Nama Tamu', 'data' => 'nama_tamu', 'orderable' => true]),
                Column::make(['width' => '10%', 'title' => 'Rating', 'data' => 'rating', 'orderable' => true, 'className' => 'text-center']),
                Column::make(['title' => 'Komentar', 'data' => 'komentar', 'orderable' => true]),
                Column::make(['title' => 'Event', 'data' => 'nama_event', 'orderable' => true]),
                Column::make(['title' => 'Waktu Kunjungan', 'data' => 'waktu_kunjungan', 'orderable' => true]),
                Column::make(['title' => 'Dikirim Pada', 'data' => 'feedback_created_at', 'orderable' => true]),
                Column::make(['width' => '12%', 'title' => 'Aksi', 'data' => 'action', 'orderable' => false, 'className' => 'text-nowrap text-center']),
            ]);

        $this->dataView([
            'dataTable' => $dataTable,
        ]);

        return $this->view('admin.feedback.list');
    }

    public function data(Request $req, $param1 = ''): JsonResponse
    {
        if ($param1 == 'list') {
            $query = DB::table('feedback as f')
                ->leftJoin('kunjungan as k', 'f.kunjungan_id', '=', 'k.kunjungan_id')
                ->leftJoin('tamu as t', 'k.tamu_id', '=', 't.tamu_id')
                ->leftJoin('event as e', 'k.event_id', '=', 'e.event_id')
                ->whereNull('f.deleted_at')
                ->whereNull('k.deleted_at')
                ->select([
                    'f.feedback_id',
                    'f.kunjungan_id',
                    'f.rating',
                    'f.komentar',
                    'f.created_at as feedback_created_at',
                    't.tamu_id',
                    't.nama_tamu',
                    't.email_tamu',
                    't.nomor_telepon_tamu',
                    't.jenis_kelamin_tamu',
                    'k.identitas',
                    'k.created_at as waktu_kunjungan',
                    'e.nama_event',
                ])
                ->orderBy('f.created_at', 'desc')
                ->get();

            $data = DataTables::of($query)->toArray();

            $start = $req->input('start');
            $resp = [];
            foreach ($data['data'] as $key => $value) {
                $dt = [];
                $dt['no'] = ++$start;
                $dt['feedback_id'] = $value['feedback_id'] ?? '-';
                $dt['nama_tamu'] = $value['nama_tamu'] ?? '-';
                $dt['rating'] = $value['rating'] ?? 0;
                $komentar = $value['komentar'] ?? '-';

                if (strlen($komentar) > 100) {
                    $dt['komentar'] = substr($komentar, 0, 100) . '...';
                } else {
                    $dt['komentar'] = $komentar;
                }

                if (!empty($value['nama_event'])) {
                    $dt['nama_event'] = $value['nama_event'];
                } else {
                    $dt['nama_event'] = '<span class="badge badge-secondary">Non-Event</span>';
                }

                $dt['waktu_kunjungan'] = $value['waktu_kunjungan'] ?
                    date('d/m/Y H:i', strtotime($value['waktu_kunjungan'])) : '-';

                $dt['feedback_created_at'] = $value['feedback_created_at'] ?
                    date('d/m/Y H:i', strtotime($value['feedback_created_at'])) : '-';

                $id = encid($value['feedback_id']);

                $dataAction = [
                    'id' => $id,
                    'btn' => [
                        ['action' => 'detail', 'attr' => ['jf-detail' => $id]],
                    ]
                ];

                $dt['action'] = Blade::render('<x-btn.actiontable :id="$id" :btn="$btn"/>', $dataAction);
                $resp[] = $dt;
            }

            $data['data'] = $resp;

            return response()->json($data);
        } else if ($param1 == 'detail') {
            validate_and_response([
                'id' => ['Parameter data', 'required'],
            ]);

            $feedbackId = decid($req->input('id'));
            $feedback = Feedback::with(['kunjungan.tamu', 'kunjungan.details', 'kunjungan.event', 'kunjungan.event.eventKategori'])
                ->findOrFail($feedbackId);

            $currData = $feedback->kunjungan;

            $detailData = [
                'feedback_id' => $feedback->feedback_id,
                'kunjungan_id' => $currData->kunjungan_id,
                'id' => $req->input('id'),
                'rating' => $feedback->rating,
                'komentar' => $feedback->komentar ?? '-',

                'nama' => $currData->tamu->nama_tamu ?? '',
                'jenis_kelamin' => $currData->tamu->jenis_kelamin_tamu ?? '',
                'email' => $currData->tamu->email_tamu ?? '',
                'nomor_telepon' => $currData->tamu->nomor_telepon_tamu ?? '',

                'jenis_kunjungan' => !empty($currData->event_id) ? 'Event' : 'Non-Event',
                'kategori_tujuan' => \App\Enums\KategoriTujuanEnum::getDescription($currData->kategori_tujuan?->value) ?? '-',
                'identitas' => $currData->identitas == 'tamu_luar' ? 'Tamu Luar'
                    : ($currData->identitas == 'civitas_pcr' ? 'Civitas PCR' : ($currData->identitas ?? '')),
                'jumlah_rombongan' => $currData->jumlah_rombongan ?? '-',
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
}
