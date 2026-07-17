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

    public function __construct()
    {
        $this->activeRoot = 'master';
        $this->breadCrump[] = ['title' => 'Master', 'link' => url('')];
    }

    public function index() {}

    public function show($param1 = '', $param2 = '')
    {
        if ($param1 === 'pegawai') {
            $this->title = 'Kelola Data Pegawai';
            $this->activeMenu = 'pegawai';
            $this->breadCrump[] = ['title' => 'Pegawai', 'link' => url()->current()];

            $builder = app('datatables.html');
            $dataTable = $builder->serverSide(true)->ajax(route('app.master.data').'/pegawai-list')->columns([
                Column::make(['width' => '5%', 'title' => 'No', 'data' => 'no', 'orderable' => false, 'searchable' => false, 'className' => 'text-center']),
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

    public function store(Request $req, $param1 = ''): JsonResponse
    {
        if ($param1 === 'sync-pegawai') {
            try {
                $syncService = new PegawaiSyncService;
                $result = $syncService->syncPegawai();

                if ($result['success']) {
                    return response()->json([
                        'status' => true,
                        'message' => $result['message'],
                        'data' => [
                            'synced' => $result['synced'],
                            'failed' => $result['failed'],
                            'total' => $result['total'] ?? 0,
                        ],
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => $result['message'],
                    ], 500);
                }
            } catch (\Exception $e) {
                Log::error('Pegawai sync error in controller', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Terjadi kesalahan: '.$e->getMessage(),
                ], 500);
            }
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }

    public function data(Request $req, $param1 = '', $param2 = ''): JsonResponse
    {
        if ($param1 === 'pegawai-list') {
            $query = DmPegawai::select('dm_pegawai.*');

            $start = (int) $req->input('start', 0);

            return DataTables::of($query)
                ->addColumn('no', function () use (&$start) {
                    return ++$start;
                })
                ->addColumn('action', function ($row) {
                    $id = encid($row->pegawai_id);
                    $dataAction = [
                        'id' => $id,
                        'btn' => [],
                    ];

                    return (string) Blade::render('<x-btn.actiontable :id="$id" :btn="$btn"/>', $dataAction);
                })
                ->rawColumns(['action'])
                ->orderColumn('nip', 'dm_pegawai.nip $1')
                ->orderColumn('nama', 'dm_pegawai.nama $1')
                ->orderColumn('email', 'dm_pegawai.email $1')
                ->filterColumn('nip', function ($query, $keyword) {
                    $query->where('dm_pegawai.nip', 'like', "%{$keyword}%");
                })
                ->filterColumn('nama', function ($query, $keyword) {
                    $query->where('dm_pegawai.nama', 'like', "%{$keyword}%");
                })
                ->filterColumn('email', function ($query, $keyword) {
                    $query->where('dm_pegawai.email', 'like', "%{$keyword}%");
                })
                ->toJson();
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }
}
