<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dimension\DmPegawai;
use App\Services\PegawaiSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Column;

class MasterController extends Controller
{
    protected $sosial_medias = [];
    function __construct()
    {
        $this->activeRoot   = 'master';
        $this->breadCrump[] = ['title' => 'Master', 'link' => url('')];
    }

    function index()
    {

    }

    public function show($param1 = '', $param2 = '')
    {
        if ($param1 === 'pegawai') {
            $this->title        = 'Kelola Data Pegawai';
            $this->activeMenu   = 'pegawai';
            $this->breadCrump[] = ['title' => 'Pegawai', 'link' => url()->current()];

            $builder   = app('datatables.html');
            $dataTable = $builder->serverSide(true)->ajax(route('app.master.data') . '/pegawai-list')->columns([
                Column::make(['width' => '', 'title' => '', 'data' => 'action', 'orderable' => false, 'className' => 'text-nowrap text-end']),
                Column::make(['width' => '5%', 'title' => 'No', 'data' => 'no', 'orderable' => false, 'className' => 'text-center']),
                Column::make(['width' => '15%', 'title' => 'NIP', 'data' => 'nip']),
                Column::make(['width' => '40%', 'title' => 'Nama Pegawai', 'data' => 'nama']),
                Column::make(['width' => '35%', 'title' => 'Email', 'data' => 'email']),
            ]);

            $this->dataView([
                'dataTable' => $dataTable,
            ]);

            return $this->view('admin.master.pegawai');
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }

    function store(Request $req, $param1 = ''): JsonResponse
    {
        if ($param1 === 'sync-pegawai') {
            try {
                $syncService = new PegawaiSyncService();
                $result = $syncService->syncPegawai();

                if ($result['success']) {
                    return response()->json([
                        'status'  => true,
                        'message' => $result['message'],
                        'data'    => [
                            'synced' => $result['synced'],
                            'failed' => $result['failed'],
                            'total'  => $result['total'] ?? 0,
                        ]
                    ]);
                } else {
                    return response()->json([
                        'status'  => false,
                        'message' => $result['message']
                    ], 500);
                }
            } catch (\Exception $e) {
                Log::error('Pegawai sync error in controller', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
        }

        else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }

    function data(Request $req, $param1 = '', $param2 = ''): JsonResponse
    {
        if ($param1 === 'pegawai-list') {
            $filter = [];

            $data = DataTables::of(DmPegawai::getDataDetail($filter, get: true))->toArray();

            $start = (int) $req->input('start', 0);
            $resp  = [];

            foreach ($data['data'] as $key => $value) {
                $dt = [];

                $dt['no']       = ++$start;
                $dt['nip']      = $value['nip'];
                $dt['nama']     = $value['nama'];
                $dt['email']    = $value['email'] ?? '-';

                $id = encid($value['pegawai_id']);

                $dataAction = [
                    'id'  => $id,
                    'btn' => []
                ];

                $dt['action'] = (string) Blade::render('<x-btn.actiontable :id="$id" :btn="$btn"/>', $dataAction);

                $resp[] = $dt;
            }
            $data['data'] = $resp;

            return response()->json($data);
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }
}
