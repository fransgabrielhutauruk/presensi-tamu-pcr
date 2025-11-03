<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tamu;
use App\Models\Kunjungan;
use Illuminate\Http\Request;
use App\Models\KunjunganDetail;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Blade;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    public function __construct()
    {
        $this->activeRoot = 'monitoring';
        $this->breadCrump[] = ['title' => 'Monitoring', 'link' => route('app.monitoring.kunjungan')];
    }

    public function kunjungan()
    {
        $this->title = 'Monitoring Kunjungan Hari Ini';
        $this->activeMenu = 'monitoring-kunjungan';
        $this->breadCrump[] = ['title' => 'Monitoring Kunjungan', 'link' => url()->current()];

        // Statistics for today
        $today = Carbon::today();
        $totalKunjunganHariIni = Kunjungan::whereDate('created_at', $today)->count();
        $kunjunganTervalidasi = Kunjungan::whereDate('created_at', $today)
            ->where('status_validasi', true)->count();
        $kunjunganBelumValidasi = Kunjungan::whereDate('created_at', $today)
            ->where('status_validasi', false)->count();
        $kunjunganSudahCheckout = Kunjungan::whereDate('created_at', $today)
            ->where('is_checkout', true)->count();

        $builder = app('datatables.html');
        $dataTable = $builder->serverSide(true)
            ->ajax(route('app.monitoring.data') . '/kunjungan-hari-ini')
            ->columns([
                Column::make(['width' => '5%', 'title' => 'No', 'data' => 'no', 'orderable' => false, 'className' => 'text-center']),
                Column::make(['title' => 'Waktu Kunjungan', 'data' => 'waktu_kunjungan', 'orderable' => true, 'className' => 'text-center']),
                Column::make(['title' => 'Nama Tamu', 'data' => 'nama', 'orderable' => true]),
                Column::make(['title' => 'Jenis Kunjungan', 'data' => 'jenis_kunjungan', 'orderable' => true, 'className' => 'text-center']),
                Column::make(['title' => 'Status Validasi', 'data' => 'status_validasi', 'orderable' => true, 'className' => 'text-center']),
                Column::make(['title' => 'Status Checkout', 'data' => 'status_checkout', 'orderable' => true, 'className' => 'text-center']),
                Column::make(['width' => '15%', 'title' => 'Aksi', 'data' => 'action', 'orderable' => false, 'className' => 'text-nowrap text-center']),
            ]);

        $this->dataView([
            'dataTable' => $dataTable,
            'totalKunjunganHariIni' => $totalKunjunganHariIni,
            'kunjunganTervalidasi' => $kunjunganTervalidasi,
            'kunjunganBelumValidasi' => $kunjunganBelumValidasi,
            'kunjunganSudahCheckout' => $kunjunganSudahCheckout,
            'tanggalHariIni' => $today->format('d F Y'),
        ]);

        return $this->view('admin.monitoring.kunjungan');
    }

    public function data(Request $req, $param1 = ''): JsonResponse
    {
        if ($param1 == 'kunjungan-hari-ini') {
            $today = Carbon::today();
            
            $query = Kunjungan::with(['tamu', 'details', 'event'])
                ->whereDate('created_at', $today);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('no', function ($data) use ($req) {
                    static $start = null;
                    if ($start === null) {
                        $start = $req->input('start', 0);
                    }
                    return ++$start;
                })
                ->addColumn('waktu_kunjungan', function ($data) {
                    if ($data->created_at) {
                        return \Carbon\Carbon::parse($data->created_at)->format('H:i');
                    }
                    return '-';
                })
                ->addColumn('nama', function ($data) {
                    return $data->tamu->nama_tamu ?? '-';
                })
                ->addColumn('jenis_kunjungan', function ($data) {
                    if (!empty($data->event_id)) {
                        return '<span class="badge badge-info">Event</span>';
                    } else {
                        return '<span class="badge badge-secondary">Non-Event</span>';
                    }
                })
                ->addColumn('status_validasi', function ($data) {
                    if ($data->status_validasi) {
                        return '<span class="badge badge-success">Tervalidasi</span>';
                    } else {
                        return '<span class="badge badge-warning">Belum Validasi</span>';
                    }
                })
                ->addColumn('status_checkout', function ($data) {
                    if ($data->is_checkout) {
                        return '<span class="badge badge-primary">Sudah Checkout</span>';
                    } else {
                        return '<span class="badge badge-light-dark">Belum Checkout</span>';
                    }
                })
                ->addColumn('action', function ($data) {
                    $id = encid($data->kunjungan_id);
                    
                    $dataAction = [
                        'id'  => $id,
                        'btn' => [
                            ['action' => 'view', 'title' => 'Lihat Detail', 'attr' => ['jf-detail' => $id]],
                        ]
                    ];

                    // Add validation button if not validated yet
                    // if (!$data->status_validasi) {
                    //     $dataAction['btn'][] = ['action' => 'check', 'title' => 'Validasi Kunjungan', 'attr' => ['jf-validate' => $id]];
                    // }

                    return Blade::render('<x-btn.actiontable :id="$id" :btn="$btn"/>', $dataAction);
                })
                ->orderColumn('waktu_kunjungan', 'created_at $1')
                ->orderColumn('status_validasi', 'status_validasi $1')
                ->orderColumn('status_checkout', 'is_checkout $1')
                ->order(function ($query) {
                    $query->orderBy('created_at', 'desc')->orderBy('status_validasi', 'asc');
                })
                ->rawColumns(['jenis_kunjungan', 'status_validasi', 'status_checkout', 'action'])
                ->make(true);
        } elseif ($param1 == 'detail') {
            // Handle detail request - reuse from KunjunganController
            $id = decid($req->input('id'));
            $data = Kunjungan::with(['tamu', 'details', 'event'])->findOrFail($id);
            
            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }
}