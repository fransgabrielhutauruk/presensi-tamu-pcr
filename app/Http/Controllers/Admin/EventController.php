<?php

namespace App\Http\Controllers\Admin;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Models\EventKategori;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Blade;

class EventController extends Controller
{
    protected $dataKategori;

    public function __construct()
    {
        $this->activeRoot = 'event';
        $this->breadCrump[] = ['title' => 'Event', 'link' => route('app.event.index')];

        $temp = [];
        foreach (EventKategori::all() as $row) {
            $temp[] = [
                'id' => encid($row->eventkategori_id),
                'text' => $row->nama_kategori,
            ];
        }
        $this->dataKategori = $temp;
    }

    public function index()
    {
        $this->title = 'Kelola Event';
        $this->activeMenu = 'event';
        $builder = app('datatables.html');
        $dataTable = $builder->serverSide(true)->ajax(route('app.event.data') . '/list')->columns([
            Column::make(['width' => '5%', 'title' => 'No', 'data' => 'no', 'orderable' => false, 'className' => 'text-center']),
            Column::make(['title' => 'Nama Event', 'data' => 'nama_event', 'orderable' => true]),
            Column::make(['title' => 'Kategori', 'data' => 'nama_kategori', 'orderable' => true]),
            Column::make(['title' => 'Tanggal Event', 'data' => 'tanggal_event', 'orderable' => true]),
            Column::make(['title' => 'Lokasi', 'data' => 'lokasi_event', 'orderable' => true]),
            Column::make(['width' => '15%', 'title' => 'Aksi', 'data' => 'action', 'orderable' => false, 'className' => 'text-nowrap text-center']),
        ]);

        $this->dataView([
            'dataTable' => $dataTable,
            'dataKategori' => $this->dataKategori,
        ]);

        return $this->view('admin.event.list');
    }

    public function show($param1 = '', $param2 = '')
    {
        if ($param1 == 'kategori') {
            $this->title = 'Kelola Kategori Event';
            $this->activeMenu = 'event-kategori';
            $this->breadCrump[] = ['title' => 'Kategori', 'link' => url()->current()];

            $builder = app('datatables.html');
            $dataTable = $builder->serverSide(true)->ajax(route('app.event.data') . '/kategori-list')->columns([
                Column::make(['width' => '5%', 'title' => 'No', 'data' => 'no', 'orderable' => false, 'className' => 'text-center']),
                Column::make(['width' => '30%', 'title' => 'Nama Kategori', 'data' => 'nama_kategori', 'orderable' => true]),
                Column::make(['width' => '35%', 'title' => 'Deskripsi', 'data' => 'deskripsi_kategori', 'orderable' => true]),
                Column::make(['width' => '10%', 'title' => 'Aksi', 'data' => 'action', 'orderable' => false, 'className' => 'text-nowrap text-center']),
            ]);

            $this->dataView([
                'dataKategori' => $this->dataKategori,
                'dataTable' => $dataTable
            ]);

            return $this->view('admin.event.event-kategori');
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }

    public function store(Request $req, $param1 = ''): JsonResponse
    {
        if ($param1 == '') {
            validate_and_response([
                'nama_event' => ['Nama Event', 'required'],
                'eventkategori_id' => ['Kategori Event', 'required'],
                'tanggal_event' => ['Tanggal Event', 'required|date'],
            ]);

            $data = [
                'eventkategori_id' => decid($req->post('eventkategori_id')),
                'nama_event' => clean_post('nama_event'),
                'deskripsi_event' => clean_post('deskripsi_event'),
                'tanggal_event' => clean_post('tanggal_event'),
                'waktu_mulai_event' => clean_post('waktu_mulai_event'),
                'waktu_selesai_event' => clean_post('waktu_selesai_event'),
                'lokasi_event' => clean_post('lokasi_event'),
            ];

            DB::beginTransaction();
            try {
                Event::create($data);
                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Data event berhasil disimpan'
                ]);
            } catch (\Throwable $th) {
                DB::rollBack();
                abort(500, 'Tambah data gagal, kesalahan database');
            }
        } else if ($param1 == 'kategori') {
            validate_and_response([
                'nama_kategori' => ['Nama Kategori', 'required'],
                'deskripsi_kategori' => ['Deskripsi Kategori', 'nullable'],
            ]);

            $data = [
                'nama_kategori' => clean_post('nama_kategori'),
                'deskripsi_kategori' => clean_post('deskripsi_kategori'),
            ];

            DB::beginTransaction();
            try {
                EventKategori::create($data);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Data kategori berhasil disimpan'
                ]);
            } catch (\Throwable $th) {
                DB::rollBack();
                abort(500, 'Tambah data gagal, kesalahan database');
            }
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }

    public function update(Request $req, $param1 = '', $param2 = ''): JsonResponse
    {
        if ($param1 == '') {
            validate_and_response([
                'id' => ['Parameter data', 'required'],
                'nama_event' => ['Nama Event', 'required'],
                'eventkategori_id' => ['Kategori Event', 'required'],
                'tanggal_event' => ['Tanggal Event', 'required|date'],
            ]);

            $currData = Event::findOrFail(decid($req->input('id')));

            $data = [
                'eventkategori_id' => decid($req->post('eventkategori_id')),
                'nama_event' => clean_post('nama_event'),
                'deskripsi_event' => clean_post('deskripsi_event'),
                'tanggal_event' => clean_post('tanggal_event'),
                'waktu_mulai_event' => clean_post('waktu_mulai_event'),
                'waktu_selesai_event' => clean_post('waktu_selesai_event'),
                'lokasi_event' => clean_post('lokasi_event'),
            ];

            DB::beginTransaction();
            try {
                $currData->update($data);

                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Data event berhasil diperbarui'
                ]);
            } catch (\Throwable $th) {
                DB::rollBack();
                abort(500, 'Gagal memperbarui data, kesalahan database');
            }
        } else if ($param1 == 'kategori') {
            validate_and_response([
                'id' => ['Parameter data', 'required'],
                'nama_kategori' => ['Nama Kategori', 'required'],
                'deskripsi_kategori' => ['Deskripsi Kategori', 'nullable'],
            ]);

            $currData = EventKategori::findOrFail(decid($req->input('id')));

            $data = [
                'nama_kategori' => clean_post('nama_kategori'),
                'deskripsi_kategori' => clean_post('deskripsi_kategori'),
            ];

            DB::beginTransaction();
            try {
                $currData->update($data);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Data kategori berhasil diperbarui'
                ]);
            } catch (\Throwable $th) {
                DB::rollBack();
                abort(500, 'Gagal memperbarui data, kesalahan database');
            }
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

            $currData = Event::findOrFail(decid($req->input('id')));

            DB::beginTransaction();
            try {
                $currData->delete();
                DB::commit();
                return response()->json(['status' => true, 'message' => 'Data event berhasil dihapus']);
            } catch (\Throwable $th) {
                DB::rollBack();
                abort(500, 'Gagal menghapus data, kesalahan database');
            }
        } else if ($param1 == 'kategori') {
            validate_and_response([
                'id' => ['Parameter data', 'required'],
            ]);

            $currData = EventKategori::findOrFail(decid($req->input('id')));

            $eventCount = Event::where('eventkategori_id', $currData->eventkategori_id)->count();
            if ($eventCount > 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Kategori tidak dapat dihapus karena masih digunakan oleh {$eventCount} event"
                ], 422);
            }
            DB::beginTransaction();
            try {
                $currData->delete();
                DB::commit();
                return response()->json(['status' => true, 'message' => 'Data kategori berhasil dihapus']);
            } catch (\Throwable $th) {
                DB::rollBack();
                abort(500, 'Gagal menghapus data, kesalahan database');
            }
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }

    public function data(Request $req, $param1 = '', $param2 = ''): JsonResponse
    {
        if ($param1 == 'detail') {
            validate_and_response([
                'id' => ['Parameter data', 'required'],
            ]);

            $currData = Event::findOrFail(decid($req->input('id')))->makeHidden(Event::$exceptEdit);

            $currData->id = $req->input('id');
            $currData->eventkategori_id = encid($currData->eventkategori_id);

            return response()->json(['status' => true, 'message' => 'Data loaded', 'data' => $currData]);
        } else if ($param1 == 'kategori-detail') {
            validate_and_response([
                'id' => ['Parameter data', 'required'],
            ]);

            $currData = EventKategori::findOrFail(decid($req->input('id')))->makeHidden(EventKategori::$exceptEdit);

            $currData->id = $req->input('id');

            return response()->json(['status' => true, 'message' => 'Data loaded', 'data' => $currData]);
        } else if ($param1 == 'list') {
            $filter = [];
            $data = DataTables::of(Event::getDataDetail($filter, get: false))->toArray();

            $start = $req->input('start');
            $resp = [];
            foreach ($data['data'] as $key => $value) {
                $dt = [];

                $dt['no'] = ++$start;
                $dt['event_id'] = $value['event_id'] ?? '-';
                $dt['nama_event'] = $value['nama_event'] ?? '-';
                $dt['deskripsi_event'] = $value['deskripsi_event'] ? substr($value['deskripsi_event'], 0, 100) . '...' : '-';
                $dt['tanggal_event'] = $value['tanggal_event'] ? date('d/m/Y', strtotime($value['tanggal_event'])) : '-';
                $dt['waktu_mulai_event'] = $value['waktu_mulai_event'] ? date('H:i', strtotime($value['waktu_mulai_event'])) : '-';
                $dt['waktu_selesai_event'] = $value['waktu_selesai_event']  ? date('H:i', strtotime($value['waktu_selesai_event'])) : '-';
                $dt['lokasi_event'] = $value['lokasi_event'] ?? '-';
                $dt['nama_kategori'] = $value['nama_kategori'] ?? '-';

                $id = encid($value['event_id']);

                $dataAction = [
                    'id'  => $id,
                    'btn' => [
                        ['action' => 'edit', 'attr' => ['jf-edit' => $id]],
                        ['action' => 'delete', 'attr' => ['jf-delete' => $id]],
                    ]
                ];

                $dt['action'] = Blade::render('<x-btn.actiontable :id="$id" :btn="$btn"/>', $dataAction);
                $resp[] = $dt;
            }
            $data['data'] = $resp;

            return response()->json($data);
        } else if ($param1 == 'kategori-list') {
            $filter = [];

            $data = DataTables::of(EventKategori::where($filter))->toArray();

            $start = $req->input('start');
            $resp = [];
            foreach ($data['data'] as $key => $value) {
                $dt = [];
                $dt['no'] = ++$start;
                $dt['eventkategori_id'] = $value['eventkategori_id'] ?? '-';
                $dt['kode_kategori'] = $value['kode_kategori'] ?? '-';
                $dt['nama_kategori'] = $value['nama_kategori'] ?? '-';
                $dt['deskripsi_kategori'] = $value['deskripsi_kategori'] ?? '-';

                $id = encid($value['eventkategori_id']);

                $dataAction = [
                    'id' => $id,
                    'btn' => [
                        ['action' => 'edit', 'attr' => ['jf-edit' => $id]],
                        ['action' => 'delete', 'attr' => ['jf-delete' => $id]],
                    ]
                ];

                $dt['action'] = Blade::render('<x-btn.actiontable :id="$id" :btn="$btn"/>', $dataAction);

                $resp[] = $dt;
            }

            $data['data'] = $resp;

            return response()->json($data);
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }
}
