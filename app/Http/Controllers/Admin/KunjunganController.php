<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tamu;
use App\Models\Kunjungan;
use Illuminate\Http\Request;
use App\Models\KunjunganDetail;
use App\Models\MstOpsiKunjungan;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Blade;

class KunjunganController extends Controller
{
    public function __construct()
    {
        $this->activeRoot = 'kunjungan';
        $this->breadCrump[] = ['title' => 'Kunjungan', 'link' => route('app.kunjungan.index')];
    }

    public function index()
    {
        $this->title = 'Kelola Kunjungan';
        $this->activeMenu = 'kunjungan';
        $builder = app('datatables.html');
        $dataTable = $builder->serverSide(true)->ajax(route('app.kunjungan.data') . '/list')->columns([
            Column::make(['width' => '5%', 'title' => 'No', 'data' => 'no', 'orderable' => false, 'className' => 'text-center']),
            Column::make(['title' => 'Nama Tamu', 'data' => 'nama', 'orderable' => true]),
            Column::make(['title' => 'Jenis Kunjungan', 'data' => 'jenis_kunjungan', 'orderable' => true, 'className' => 'text-center']),
            Column::make(['title' => 'Tanggal Kunjungan', 'data' => 'tanggal_kunjungan', 'orderable' => true, 'className' => 'text-center']),
            Column::make(['title' => 'Waktu Kunjungan', 'data' => 'waktu_kunjungan', 'orderable' => true, 'className' => 'text-center']),
            Column::make(['title' => 'Status Validasi', 'data' => 'status_validasi', 'orderable' => true, 'className' => 'text-center']),
            Column::make(['width' => '15%', 'title' => 'Aksi', 'data' => 'action', 'orderable' => false, 'className' => 'text-nowrap text-center']),
        ]);

        $this->dataView([
            'dataTable' => $dataTable,
        ]);

        return $this->view('admin.kunjungan.index');
    }

    public function show($param1 = '', $param2 = '')
    {
        if ($param1 == 'opsi') {
            $this->title = 'Kelola Opsi Kunjungan';
            $this->activeMenu = 'kelola-opsi';
            $this->breadCrump[] = ['title' => 'Kelola Opsi', 'link' => url()->current()];

            $builder = app('datatables.html');
            $dataTable = $builder->serverSide(true)->ajax(route('app.kunjungan.data') . '/opsi-list')->columns([
                Column::make(['width' => '5%', 'title' => 'No', 'data' => 'no', 'orderable' => false, 'className' => 'text-center']),
                Column::make(['width' => '30%', 'title' => 'Nama Opsi', 'data' => 'nama_opsi', 'orderable' => true]),
                Column::make(['width' => '35%', 'title' => 'Deskripsi', 'data' => 'deskripsi_opsi', 'orderable' => true]),
                Column::make(['width' => '30%', 'title' => 'Nilai Opsi', 'data' => 'nilai_opsi', 'orderable' => true]),
                Column::make(['width' => '10%', 'title' => 'Aksi', 'data' => 'action', 'orderable' => false, 'className' => 'text-nowrap text-center']),
            ]);

            $this->dataView([
                'dataTable' => $dataTable,
            ]);

            return $this->view('admin.kunjungan.opsi');
        } else if ($param1 == 'validasi') {
            $this->title = 'Validasi Kunjungan';
            $this->activeMenu = 'validasi-kunjungan';
            $this->breadCrump[] = ['title' => 'Validasi Kunjungan', 'link' => route('app.kunjungan.validasi')];

            $builder = app('datatables.html');
            $dataTable = $builder->serverSide(true)->ajax(route('app.kunjungan.data') . '/validasi-list')->columns([
                Column::make(['width' => '5%', 'title' => 'No', 'data' => 'no', 'orderable' => false, 'className' => 'text-center']),
                Column::make(['title' => 'Nama Tamu', 'data' => 'nama', 'orderable' => true]),
                Column::make(['title' => 'Jenis Kunjungan', 'data' => 'jenis_kunjungan', 'orderable' => true, 'className' => 'text-center']),
                Column::make(['title' => 'Tanggal Kunjungan', 'data' => 'tanggal_kunjungan', 'orderable' => true, 'className' => 'text-center']),
                Column::make(['title' => 'Waktu Kunjungan', 'data' => 'waktu_kunjungan', 'orderable' => true, 'className' => 'text-center']),
                // Column::make(['width' => '15%', 'title' => 'Aksi', 'data' => 'action', 'orderable' => false, 'className' => 'text-nowrap text-center']),
            ]);

            $this->dataView([
                'dataTable' => $dataTable,
            ]);

            return $this->view('admin.kunjungan.validasi');
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }

    public function store(Request $req, $param1 = ''): JsonResponse
    {
        if ($param1 == '') {
            validate_and_response([
                'nama' => ['Nama Tamu', 'required'],
                'jenis_kelamin' => ['Jenis Kelamin', 'required'],
                'email' => ['Email', 'required|email'],
                'nomor_telepon' => ['Nomor Telepon', 'required'],
                'kategori_tujuan' => ['Kategori Tujuan', 'required'],
                'transportasi' => ['Transportasi', 'required'],
            ]);

            DB::beginTransaction();
            try {
                $tamuData = [
                    'nama_tamu' => clean_post('nama'),
                    'jenis_kelamin_tamu' => clean_post('jenis_kelamin'),
                    'email_tamu' => clean_post('email'),
                    'nomor_telepon_tamu' => clean_post('nomor_telepon'),
                ];
                $tamu = Tamu::create($tamuData);

                $kunjunganData = [
                    'tamu_id' => $tamu->tamu_id,
                    'kategori_tujuan' => clean_post('kategori_tujuan'),
                    'identitas' => 'tamu_luar',
                    'transportasi' => clean_post('transportasi'),
                    'status_validasi' => false,
                    'is_checkout' => false,
                ];
                $kunjungan = Kunjungan::create($kunjunganData);

                $keperluan = clean_post('keperluan');
                if (!empty($keperluan)) {
                    KunjunganDetail::create([
                        'kunjungan_id' => $kunjungan->kunjungan_id,
                        'kunci' => 'keperluan',
                        'nilai' => $keperluan,
                        'urutan' => 1,
                    ]);
                }

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Data kunjungan berhasil disimpan'
                ]);
            } catch (\Throwable $th) {
                DB::rollBack();
                abort(500, 'Tambah data gagal, kesalahan database');
            }
        } else if ($param1 == 'opsi') {
            validate_and_response([
                'nama_opsi' => ['Nama Opsi', 'required'],
                'nilai_opsi' => ['Nilai Opsi', 'required'],
            ]);
            DB::beginTransaction();
            try {
                $nilaiOpsi = clean_post('nilai_opsi');
                if (is_string($nilaiOpsi)) {
                    $nilaiOpsi = json_decode($nilaiOpsi, true);
                }

                if (is_array($nilaiOpsi)) {
                    foreach ($nilaiOpsi as &$item) {
                        if (!isset($item['id']) || !isset($item['en'])) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Format data tidak valid. Setiap item harus memiliki id (Indonesia) dan en (English).'
                            ], 422);
                        }
                        
                        $item['id'] = trim($item['id']);
                        $item['en'] = trim($item['en']);
                        
                        if (empty($item['id']) || empty($item['en'])) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Semua field (Indonesia, English) wajib diisi dan tidak boleh kosong.'
                            ], 422);
                        }
                    }
                }

                $opsiData = [
                    'nama_opsi' => clean_post('nama_opsi'),
                    'deskripsi_opsi' => clean_post('deskripsi_opsi'),
                    'nilai_opsi' => $nilaiOpsi,
                ];
                MstOpsiKunjungan::create($opsiData);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Data Opsi kunjungan berhasil disimpan'
                ]);
            } catch (\Throwable $th) {
                DB::rollBack();
                abort(500, 'Tambah opsi gagal, kesalahan database');
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
                'nama' => ['Nama Tamu', 'required'],
                'jenis_kelamin' => ['Jenis Kelamin', 'required'],
                'email' => ['Email', 'required|email'],
                'nomor_telepon' => ['Nomor Telepon', 'required'],
                'kategori_tujuan' => ['Kategori Tujuan', 'required'],
                'transportasi' => ['Transportasi', 'required'],
            ]);

            $currData = Kunjungan::with('tamu')->findOrFail(decid($req->input('id')));

            DB::beginTransaction();
            try {
                $currData->tamu->update([
                    'nama_tamu' => clean_post('nama'),
                    'jenis_kelamin_tamu' => clean_post('jenis_kelamin'),
                    'email_tamu' => clean_post('email'),
                    'nomor_telepon_tamu' => clean_post('nomor_telepon'),
                ]);

                $currData->update([
                    'kategori_tujuan' => clean_post('kategori_tujuan'),
                    'transportasi' => clean_post('transportasi'),
                ]);

                $keperluan = clean_post('keperluan');
                $existingDetail = KunjunganDetail::where('kunjungan_id', $currData->kunjungan_id)
                    ->where('kunci', 'keperluan')
                    ->first();

                if (!empty($keperluan)) {
                    if ($existingDetail) {
                        $existingDetail->update(['nilai' => $keperluan]);
                    } else {
                        KunjunganDetail::create([
                            'kunjungan_id' => $currData->kunjungan_id,
                            'kunci' => 'keperluan',
                            'nilai' => $keperluan,
                            'urutan' => 1,
                        ]);
                    }
                } elseif ($existingDetail) {
                    $existingDetail->delete();
                }

                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Data kunjungan berhasil diperbarui'
                ]);
            } catch (\Throwable $th) {
                DB::rollBack();
                abort(500, 'Gagal memperbarui data, kesalahan database');
            }
        } else if ($param1 == 'validasi') {
            validate_and_response([
                'id' => ['Parameter data', 'required'],
            ]);

            $currData = Kunjungan::findOrFail(decid($req->input('id')));

            DB::beginTransaction();
            try {
                $currData->update(['status_validasi' => true]);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Kunjungan berhasil divalidasi'
                ]);
            } catch (\Throwable $th) {
                DB::rollBack();
                abort(500, 'Gagal memvalidasi kunjungan, kesalahan database');
            }
        } else if ($param1 == 'opsi') {
            validate_and_response([
                'id' => ['Parameter data', 'required'],
                'nama_opsi' => ['Nama Opsi', 'required'],
                'nilai_opsi' => ['Nilai Opsi', 'required'],
            ]);
            $currData = MstOpsiKunjungan::findOrFail(decid($req->input('id')));
            DB::beginTransaction();
            try {
                $nilaiOpsi = clean_post('nilai_opsi');
                if (is_string($nilaiOpsi)) {
                    $nilaiOpsi = json_decode($nilaiOpsi, true);
                }

                if (is_array($nilaiOpsi)) {
                    foreach ($nilaiOpsi as &$item) {
                        if (!isset($item['id']) || !isset($item['en'])) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Format data tidak valid. Setiap item harus memiliki id (Indonesia) dan en (English).'
                            ], 422);
                        }
                        
                        $item['id'] = trim($item['id']);
                        $item['en'] = trim($item['en']);
                        
                        if (empty($item['id']) || empty($item['en'])) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Semua field (Indonesia, English) wajib diisi dan tidak boleh kosong.'
                            ], 422);
                        }
                    }
                }

                $data = [
                    'nama_opsi' => clean_post('nama_opsi'),
                    'deskripsi_opsi' => clean_post('deskripsi_opsi'),
                    'nilai_opsi' => $nilaiOpsi,
                ];
                $currData->update($data);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Opsi kunjungan berhasil diperbarui'
                ]);
            } catch (\Throwable $th) {
                DB::rollBack();
                abort(500, 'Gagal memperbarui opsi, kesalahan database');
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

            $currData = Kunjungan::findOrFail(decid($req->input('id')));

            DB::beginTransaction();
            try {
                $currData->delete();
                DB::commit();
                return response()->json(['status' => true, 'message' => 'Data kunjungan berhasil dihapus']);
            } catch (\Throwable $th) {
                DB::rollBack();
                abort(500, 'Gagal menghapus data, kesalahan database');
            }
        } else if ($param1 == 'opsi') {
            validate_and_response([
                'id' => ['Parameter data', 'required'],
            ]);
            $currData = MstOpsiKunjungan::findOrFail(decid($req->input('id')));
            DB::beginTransaction();
            try {
                $currData->delete();
                DB::commit();
                return response()->json(['status' => true, 'message' => 'Opsi kunjungan berhasil dihapus']);
            } catch (\Throwable $th) {
                DB::rollBack();
                abort(500, 'Gagal menghapus opsi, kesalahan database');
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

            $currData = Kunjungan::with(['tamu', 'details', 'event', 'event.eventKategori'])->findOrFail(decid($req->input('id')));

            $detailData = [
                'kunjungan_id' => $currData->kunjungan_id,

                'nama' => $currData->tamu->nama_tamu ?? '',
                'jenis_kelamin' => $currData->tamu->jenis_kelamin_tamu ?? '',
                'email' => $currData->tamu->email_tamu ?? '',
                'nomor_telepon' => $currData->tamu->nomor_telepon_tamu ?? '',

                'jenis_kunjungan' => !empty($currData->event_id) ? 'Event' : 'Non-Event',
                'kategori_tujuan' => $currData->kategori_tujuan ?? '',
                'identitas' => $currData->identitas == 'tamu_luar' ? 'Tamu Luar' : ($currData->identitas == 'civitas_pcr' ? 'Civitas PCR' : ($currData->identitas ?? '')),
                'transportasi' => $currData->transportasi ?? '',
                'status_validasi' => $currData->status_validasi ? 'Tervalidasi' : 'Belum Validasi',
                'is_checkout' => $currData->is_checkout ? 'Sudah Checkout' : 'Belum Checkout',

                'tanggal_kunjungan' => $currData->created_at ? tanggal($currData->created_at) : '',
                'waktu_kunjungan' => $currData->created_at ? $currData->created_at->format('H:i') : '-',
                'waktu_keluar' => $currData->waktu_keluar ? \Carbon\Carbon::parse($currData->waktu_keluar)->format('H:i') : '-',
                'checkout_time' => $currData->checkout_time ? $currData->checkout_time->format('H:i') : '-',

                'event_nama' => $currData->event->nama_event ?? '-',
                'event_kategori' => $currData->event->eventKategori->nama_kategori_event ?? '-',
                'event_tanggal' => $currData->event ? \Carbon\Carbon::parse($currData->event->tanggal_event)->format('d/m/Y') : '-',

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
        } else if ($param1 == 'edit-data') {
            validate_and_response([
                'id' => ['Parameter data', 'required'],
            ]);

            $currData = Kunjungan::with(['tamu', 'details'])->findOrFail(decid($req->input('id')));

            $formData = [
                'kunjungan_id' => $currData->kunjungan_id,
                'nama' => $currData->tamu->nama_tamu ?? '',
                'jenis_kelamin' => $currData->tamu->jenis_kelamin_tamu ?? '',
                'email' => $currData->tamu->email_tamu ?? '',
                'nomor_telepon' => $currData->tamu->nomor_telepon_tamu ?? '',
                'kategori_tujuan' => $currData->kategori_tujuan ?? '',
                'transportasi' => $currData->transportasi ?? '',
                'keperluan' => '',
            ];

            foreach ($currData->details as $detail) {
                if ($detail->kunci == 'keperluan') {
                    $formData['keperluan'] = $detail->nilai;
                    break;
                }
            }

            $formData['id'] = $req->input('id');

            return response()->json(['status' => true, 'message' => 'Data loaded', 'data' => $formData]);
        } else if ($param1 == 'list') {
            $filter = [];
            $data = DataTables::of(Kunjungan::with(['tamu', 'details', 'event'])->where($filter))->toArray();

            $start = $req->input('start');
            $resp = [];
            foreach ($data['data'] as $key => $value) {
                $dt = [];

                $dt['no'] = ++$start;
                $dt['nama'] = $value['tamu']['nama_tamu'] ?? '-';
                $dt['tanggal_kunjungan'] = $value['created_at'] ? tanggal($value['created_at']) : '-';
                $dt['waktu_kunjungan'] = $value['created_at'] ? \Carbon\Carbon::parse($value['created_at'])->setTimezone(config('app.timezone'))->format('H:i') : '-';

                if (!empty($value['event_id'])) {
                    $dt['jenis_kunjungan'] = '<span class="badge badge-info">Event</span>';
                } else {
                    $dt['jenis_kunjungan'] = '<span class="badge badge-secondary">Non-Event</span>';
                }

                if ($value['status_validasi']) {
                    $dt['status_validasi'] = '<span class="badge badge-success">Tervalidasi</span>';
                } else {
                    $dt['status_validasi'] = '<span class="badge badge-warning">Belum Validasi</span>';
                }

                $id = encid($value['kunjungan_id']);

                $dataAction = [
                    'id'  => $id,
                    'btn' => [
                        ['action' => 'detail', 'attr' => ['jf-detail' => $id]],
                        ['action' => 'edit', 'attr' => ['jf-edit' => $id]],
                        ['action' => 'delete', 'attr' => ['jf-delete' => $id]],
                    ]
                ];

                $dt['action'] = Blade::render('<x-btn.actiontable :id="$id" :btn="$btn"/>', $dataAction);
                $resp[] = $dt;
            }
            $data['data'] = $resp;

            return response()->json($data);
        } else if ($param1 == 'validasi-list') {
            $filter = ['status_validasi' => false];
            $data = DataTables::of(Kunjungan::with(['tamu', 'details', 'event'])->where($filter))->toArray();

            $start = $req->input('start');
            $resp = [];
            foreach ($data['data'] as $key => $value) {
                $dt = [];
                $dt['no'] = ++$start;
                $dt['nama'] = $value['tamu']['nama_tamu'] ?? '-';

                if (!empty($value['event_id'])) {
                    $dt['jenis_kunjungan'] = '<span class="badge badge-info">Event</span>';
                } else {
                    $dt['jenis_kunjungan'] = '<span class="badge badge-secondary">Non-Event</span>';
                }

                if ($value['created_at']) {
                    $createdAt = \Carbon\Carbon::parse($value['created_at']);
                    $dt['tanggal_kunjungan'] = $createdAt->format('d/m/Y');
                    $dt['waktu_kunjungan'] = $createdAt->format('H:i');
                } else {
                    $dt['tanggal_kunjungan'] = '-';
                    $dt['waktu_kunjungan'] = '-';
                }

                $id = encid($value['kunjungan_id']);

                $dataAction = [
                    'id' => $id,
                    'btn' => [
                        ['action' => 'view', 'title' => 'Lihat Detail', 'attr' => ['jf-detail' => $id]],
                        ['action' => 'check', 'title' => 'Validasi Kunjungan', 'attr' => ['jf-validate' => $id]],
                    ]
                ];

                $dt['action'] = Blade::render('<x-btn.actiontable :id="$id" :btn="$btn"/>', $dataAction);

                $resp[] = $dt;
            }

            $data['data'] = $resp;

            return response()->json($data);
        } else if ($param1 == 'opsi-list') {
            $filter = [];
            $data = DataTables::of(MstOpsiKunjungan::where($filter))->toArray();
            $start = $req->input('start');
            $resp = [];
            foreach ($data['data']  as $key => $value) {
                $dt = [];
                $dt['no'] = ++$start;
                $dt['opsikunjungan_id'] = $value['opsikunjungan_id'] ?? '-';
                $dt['nama_opsi'] = $value['nama_opsi'] ?? '-';
                $dt['deskripsi_opsi'] = $value['deskripsi_opsi'] ?? '-';

                $nilaiOpsi = $value['nilai_opsi'] ?? [];
                if (is_string($nilaiOpsi)) {
                    $nilaiOpsi = json_decode($nilaiOpsi, true);
                }

                if (is_array($nilaiOpsi) && count($nilaiOpsi) > 0) {
                    $isMultiLanguage = isset($nilaiOpsi[0]['id']) && isset($nilaiOpsi[0]['en']);
                    
                    if ($isMultiLanguage) {
                        $labels = array_map(function ($item) {
                            return $item['id'] ?? '-';
                        }, $nilaiOpsi);
                    } else {
                        $labels = array_map(function ($item) {
                            return $item['label'] ?? '-';
                        }, $nilaiOpsi);
                    }
                    
                    $dt['nilai_opsi'] = '<span class="badge badge-light-primary fs-7">' . count($nilaiOpsi) . ' item</span><br>' .
                        '<small class="text-muted">' . implode(', ', array_slice($labels, 0, 3)) .
                        (count($labels) > 3 ? '...' : '') . '</small>';
                } else {
                    $dt['nilai_opsi'] = '<span class="text-muted">Tidak ada item</span>';
                }

                $id = encid($value['opsikunjungan_id']);
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
        } else if ($param1 == 'opsi-detail') {
            validate_and_response([
                'id' => ['Parameter data', 'required'],
            ]);
            $currData = MstOpsiKunjungan::findOrFail(decid($req->input('id')));
            $currData->id = $req->input('id');
            return response()->json([
                'status' => true,
                'message' => 'Data loaded',
                'data' => $currData
            ]);
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }

    public function validasi()
    {
        return $this->show('validasi');
    }

    public function updateValidasi($id)
    {
        $request = new Request(['id' => $id]);
        return $this->update($request, 'validasi');
    }
}
