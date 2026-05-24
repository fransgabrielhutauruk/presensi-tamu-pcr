<?php

namespace App\Http\Controllers\Admin;

use App\Models\Kunjungan;
use Illuminate\Http\Request;
use App\Models\MstOpsiKunjungan;
use Yajra\DataTables\DataTables;
use App\Enums\KategoriTujuanEnum;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Blade;

class KunjunganController extends Controller
{
    public function index(Request $request)
    {
        $this->title = 'Kelola Kunjungan';
        $this->activeMenu = 'kunjungan';
        $this->breadCrump[] = ['title' => 'Kunjungan', 'link' => route('app.kunjungan.index')];

        $availableColumns = [
            'no' => [
                'width' => '5%',
                'title' => 'No',
                'data' => 'no',
                'orderable' => false,
                'required' => true,
                'className' => 'text-center'
            ],
            'nama' => ['title' => 'Nama Tamu', 'data' => 'nama', 'orderable' => true],
            'jenis_kelamin' => [
                'title' => 'Jenis Kelamin',
                'data' => 'jenis_kelamin',
                'orderable' => true,
            ],
            'identitas' => [
                'title' => 'Identitas',
                'data' => 'identitas',
                'orderable' => true,
            ],
            'email' => ['title' => 'Email', 'data' => 'email', 'orderable' => true],
            'nomor_telepon' => ['title' => 'No. Telepon', 'data' => 'nomor_telepon', 'orderable' => true],
            'jenis_kunjungan' => [
                'title' => 'Jenis Kunjungan',
                'data' => 'jenis_kunjungan',
                'orderable' => true,
            ],
            'kategori_tujuan' => ['title' => 'Kategori Tujuan', 'data' => 'kategori_tujuan', 'orderable' => true],
            'transportasi' => [
                'title' => 'Transportasi',
                'data' => 'transportasi',
                'orderable' => true,
            ],
            'waktu_kunjungan' => [
                'title' => 'Waktu Kunjungan',
                'data' => 'waktu_kunjungan',
                'orderable' => true,
            ],
            'waktu_keluar' => [
                'title' => 'Waktu Keluar (Estimasi)',
                'data' => 'waktu_keluar',
                'orderable' => true,
            ],
            'checkout_time' => [
                'title' => 'Waktu Checkout',
                'data' => 'checkout_time',
                'orderable' => true,
            ],
            'is_checkout' => [
                'title' => 'Status Checkout',
                'data' => 'is_checkout',
                'orderable' => true,
            ],
            'event_nama' => ['title' => 'Nama Event', 'data' => 'event_nama', 'orderable' => true],
            'event_kategori' => ['title' => 'Kategori Event', 'data' => 'event_kategori', 'orderable' => true],
            'action' => [
                'width' => '12%',
                'title' => 'Aksi',
                'data' => 'action',
                'orderable' => false,
                'className' => 'text-nowrap text-center',
                'required' => true
            ],
        ];

        $defaultColumns = ['no', 'nama', 'jenis_kelamin', 'identitas', 'jenis_kunjungan', 'waktu_kunjungan', 'action'];

        $selectedColumns = $defaultColumns;

        if ($request->has('columns')) {
            $selectedColumns = $request->input('columns', []);
            if (!in_array('no', $selectedColumns)) {
                array_unshift($selectedColumns, 'no');
            }
            if (!in_array('action', $selectedColumns)) {
                $selectedColumns[] = 'action';
            }
        }

        $columns = [];
        foreach ($selectedColumns as $columnKey) {
            if (isset($availableColumns[$columnKey])) {
                $columns[] = Column::make($availableColumns[$columnKey]);
            }
        }

        $builder = app('datatables.html');
        $dataTable = $builder->serverSide(true)->ajax(route('app.kunjungan.data') . '/list')->columns($columns);

        $this->dataView([
            'dataTable' => $dataTable,
            'availableColumns' => $availableColumns,
            'selectedColumns' => $selectedColumns,
        ]);

        return $this->view('admin.kunjungan.list');
    }

    public function show($param1 = '', $param2 = '')
    {
        if ($param1 == 'opsi') {
            $this->title = 'Kelola Opsi Kunjungan';
            $this->activeMenu = 'kelola-opsi';
            $this->breadCrump[] = ['title' => 'Kelola Opsi', 'link' => url()->current()];

            $builder = app('datatables.html');
            $dataTable = $builder->serverSide(true)->ajax(route('app.kunjungan.data') . '/opsi-list')->columns([
                Column::make([
                    'width' => '5%',
                    'title' => 'No',
                    'data' => 'no',
                    'orderable' => false,
                    'className' => 'text-center'
                ]),
                Column::make(['width' => '30%', 'title' => 'Nama Opsi', 'data' => 'nama_opsi', 'orderable' => true]),
                Column::make([
                    'width' => '35%',
                    'title' => 'Deskripsi',
                    'data' => 'deskripsi_opsi',
                    'orderable' => true
                ]),
                Column::make(['width' => '30%', 'title' => 'Nilai Opsi', 'data' => 'nilai_opsi', 'orderable' => false]),
                Column::make([
                    'width' => '10%',
                    'title' => 'Aksi',
                    'data' => 'action',
                    'orderable' => false,
                    'className' => 'text-nowrap text-center'
                ]),
            ]);

            $this->dataView([
                'dataTable' => $dataTable,
            ]);

            return $this->view('admin.kunjungan.opsi');
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }

    public function store(Request $req, $param1 = ''): JsonResponse
    {
        if ($param1 == 'opsi') {
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
                                'message' => 'Format data tidak valid. Setiap item harus memiliki id (Indonesia) ' .
                                    'dan en (English).'
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
        if ($param1 == 'opsi') {
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
                                'message' => 'Format data tidak valid. Setiap item harus memiliki id (Indonesia) ' .
                                    'dan en (English).'
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
        if ($param1 == 'list') {
            $filter = ['status_validasi' => true];
            $query = Kunjungan::with(['tamu', 'civitas', 'details', 'event', 'event.eventKategori'])
                ->where($filter)
                ->latest()
                ->get();
            $data = DataTables::of($query)->toArray();

            $start = $req->input('start');
            $resp = [];
            foreach ($data['data'] as $key => $value) {
                $dt = [];

                $dt['no'] = ++$start;
                $dt['nama'] = $value['tamu']['nama_tamu'] ?? $value['civitas']['nama_civitas'] ?? '-';
                $dt['jenis_kelamin'] = $value['tamu']['jenis_kelamin_tamu'] ?? $value['civitas']['jenis_kelamin'] ?? '-';
                $dt['email'] = $value['tamu']['email_tamu'] ?? $value['civitas']['email'] ?? '-';
                $dt['nomor_telepon'] = $value['tamu']['nomor_telepon_tamu'] ?? $value['civitas']['nomor_telepon'] ?? '-';

                $dt['kategori_tujuan'] = KategoriTujuanEnum::getDescription($value['kategori_tujuan']) ?? '-';
                $dt['transportasi'] = $value['transportasi'] ?? '-';
                $dt['identitas'] = Kunjungan::getIdentitasBadge($value['identitas'], $value['is_vip']);

                $dt['waktu_kunjungan'] = $value['created_at'] ? tanggal($value['created_at']) . ' ' .
                    \Carbon\Carbon::parse($value['created_at'])->setTimezone(config('app.timezone'))
                    ->format('H:i') : '-';
                $dt['waktu_keluar'] = $value['waktu_keluar'] ? \Carbon\Carbon::parse($value['waktu_keluar'])
                    ->format('H:i') : '-';
                $dt['checkout_time'] = $value['checkout_time'] ? \Carbon\Carbon::parse($value['checkout_time'])
                    ->setTimezone(config('app.timezone'))->format('H:i') : '-';

                $dt['jenis_kunjungan'] = Kunjungan::getJenisKunjunganBadge($value['event_id']);
                $dt['status_validasi'] = Kunjungan::getStatusValidasiBadge($value['status_validasi']);
                $dt['is_checkout'] = Kunjungan::getStatusCheckoutBadge($value['is_checkout']);

                $dt['event_nama'] = $value['event']['nama_event'] ?? '-';
                $dt['event_kategori'] = $value['event']['event_kategori']['nama_kategori'] ?? '-';

                $id = encid($value['kunjungan_id']);

                $dataAction = [
                    'id'  => $id,
                    'btn' => [
                        ['action' => 'detail', 'attr' => ['jf-detail' => $id]],
                        ['action' => 'delete', 'attr' => ['jf-delete' => $id]],
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

            $currData = Kunjungan::with(['tamu', 'civitas', 'details', 'event', 'event.eventKategori'])
                ->findOrFail(decid($req->input('id')));

            $detailData = [
                'kunjungan_id' => $currData->kunjungan_id,
                'id' => $req->input('id'),

                'nama' => $currData->tamu->nama_tamu ?? $currData->civitas->nama_civitas ?? '',
                'jenis_kelamin' => $currData->tamu->jenis_kelamin_tamu ?? $currData->civitas->jenis_kelamin ?? '',
                'email' => $currData->tamu->email_tamu ?? $currData->civitas->email ?? '',
                'nomor_telepon' => $currData->tamu->nomor_telepon_tamu ?? $currData->civitas->nomor_telepon ?? '',

                'jenis_kunjungan' => !empty($currData->event_id) ? 'Event' : 'Non-Event',
                'kategori_tujuan' => KategoriTujuanEnum::getDescription($currData->kategori_tujuan?->value) ?? '-',
                'identitas' => $currData->identitas == 'tamu_luar' ? 'Tamu Luar'
                    : ($currData->identitas == 'civitas_pcr' ? 'Civitas PCR' : ($currData->identitas ?? '')),
                'transportasi' => $currData->transportasi ?? '',
                'status_validasi' => $currData->status_validasi ? 'Sudah validasi' : 'Belum validasi',
                'is_checkout' => $currData->is_checkout ? 'Sudah checkout' : 'Belum checkout',

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

                    $dt['nilai_opsi'] = '<span class="badge badge-light-primary fs-7">' . count($nilaiOpsi) .
                        ' item</span><br><small class="text-muted">' . implode(', ', array_slice($labels, 0, 3)) .
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
}
