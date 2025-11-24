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
            'jumlah_rombongan' => [
                'title' => 'Jumlah Rombongan',
                'data' => 'jumlah_rombongan',
                'orderable' => true,
            ],
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
        } else if ($param1 == 'validasi') {
            $this->title = 'Validasi Kunjungan';
            $this->activeMenu = 'validasi-kunjungan';
            $this->breadCrump[] = ['title' => 'Validasi Kunjungan', 'link' => route('app.kunjungan.validasi')];

            $builder = app('datatables.html');
            $dataTable = $builder->serverSide(true)
                ->ajax(route('app.kunjungan.data') . '/validasi-list')
                ->columns([
                    Column::make([
                        'width' => '3%',
                        'title' => '<div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" id="checkAllValidasi"></div>',
                        'data' => 'checkbox',
                        'orderable' => false,
                        'className' => 'text-center',
                        'searchable' => false
                    ]),
                    Column::make([
                        'width' => '5%',
                        'title' => 'No',
                        'data' => 'no',
                        'orderable' => false,
                        'className' => 'text-center'
                    ]),
                    Column::make(['title' => 'Nama Tamu', 'data' => 'nama', 'orderable' => true]),
                    Column::make([
                        'title' => 'Jenis Kelamin',
                        'data' => 'jenis_kelamin',
                        'orderable' => true,
                        'className' => 'text-center'
                    ]),
                    Column::make(['title' => 'Email', 'data' => 'email', 'orderable' => true]),
                    Column::make(['title' => 'No. Telepon', 'data' => 'nomor_telepon', 'orderable' => true]),
                    Column::make([
                        'title' => 'Identitas',
                        'data' => 'identitas',
                        'orderable' => true,
                    ]),
                    Column::make([
                        'title' => 'Jenis Kunjungan',
                        'data' => 'jenis_kunjungan',
                        'orderable' => true,
                        'className' => 'text-center'
                    ]),
                    Column::make([
                        'title' => 'Waktu Kunjungan',
                        'data' => 'waktu_kunjungan',
                        'orderable' => true,
                        'className' => 'text-center'
                    ]),
                    Column::make([
                        'width' => '12%',
                        'title' => 'Aksi',
                        'data' => 'action',
                        'orderable' => false,
                        'className' => 'text-nowrap text-center'
                    ]),
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
        if ($param1 == 'validasi') {
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
            $query = Kunjungan::with(['tamu', 'details', 'event', 'event.eventKategori'])
                ->where($filter)
                ->latest()
                ->get();
            $data = DataTables::of($query)->toArray();

            $start = $req->input('start');
            $resp = [];
            foreach ($data['data'] as $key => $value) {
                $dt = [];

                $dt['no'] = ++$start;
                $dt['nama'] = $value['tamu']['nama_tamu'] ?? '-';
                $dt['jenis_kelamin'] = $value['tamu']['jenis_kelamin_tamu'] ?? '-';
                $dt['email'] = $value['tamu']['email_tamu'] ?? '-';
                $dt['nomor_telepon'] = $value['tamu']['nomor_telepon_tamu'] ?? '-';

                $dt['kategori_tujuan'] = KategoriTujuanEnum::getDescription($value['kategori_tujuan']) ?? '-';
                $dt['jumlah_rombongan'] = $value['jumlah_rombongan'] ?? '-';
                $dt['transportasi'] = $value['transportasi'] ?? '-';
                $dt['identitas'] = Kunjungan::getIdentitasBadge($value['identitas']);

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

            $currData = Kunjungan::with(['tamu', 'details', 'event', 'event.eventKategori'])
                ->findOrFail(decid($req->input('id')));

            $detailData = [
                'kunjungan_id' => $currData->kunjungan_id,
                'id' => $req->input('id'),

                'nama' => $currData->tamu->nama_tamu ?? '',
                'jenis_kelamin' => $currData->tamu->jenis_kelamin_tamu ?? '',
                'email' => $currData->tamu->email_tamu ?? '',
                'nomor_telepon' => $currData->tamu->nomor_telepon_tamu ?? '',

                'jenis_kunjungan' => !empty($currData->event_id) ? 'Event' : 'Non-Event',
                'kategori_tujuan' => KategoriTujuanEnum::getDescription($currData->kategori_tujuan?->value) ?? '-',
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
        } else if ($param1 == 'validasi-list') {
            $filter = ['status_validasi' => false];
            $query = Kunjungan::with(['tamu', 'details', 'event'])
                ->where($filter)
                ->latest()
                ->get();
            $data = DataTables::of($query)->toArray();

            $start = $req->input('start');
            $resp = [];
            foreach ($data['data'] as $key => $value) {
                $dt = [];

                $id = encid($value['kunjungan_id']);

                $dt['checkbox'] = '<div class="form-check form-check-sm form-check-custom form-check-solid">
                    <input class="form-check-input row-checkbox" type="checkbox" value="' . $id . '" data-id="' .
                    $id . '">
                </div>';

                $dt['no'] = ++$start;
                $dt['nama'] = $value['tamu']['nama_tamu'] ?? '-';
                $dt['jenis_kelamin'] = $value['tamu']['jenis_kelamin_tamu'] ?? '-';
                $dt['email'] = $value['tamu']['email_tamu'] ?? '-';
                $dt['nomor_telepon'] = $value['tamu']['nomor_telepon_tamu'] ?? '-';
                $dt['kategori_tujuan'] = $value['kategori_tujuan'] ?? '-';
                $dt['transportasi'] = $value['transportasi'] ?? '-';
                $dt['identitas'] = Kunjungan::getIdentitasBadge($value['identitas']);
                $dt['jenis_kunjungan'] = Kunjungan::getJenisKunjunganBadge($value['event_id']);

                $dt['waktu_kunjungan'] = $value['created_at'] ? tanggal($value['created_at']) . ' ' .
                    \Carbon\Carbon::parse($value['created_at'])->setTimezone(config('app.timezone'))
                    ->format('H:i') : '-';

                $dataAction = [
                    'id' => $id,
                    'btn' => [
                        ['action' => 'detail', 'title' => 'Lihat Detail', 'attr' => ['jf-detail' => $id]],
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
        } else if ($param1 == 'monitoring-hari-ini') {
            $today = \Carbon\Carbon::today();
            $filter = [];
            $query = Kunjungan::with(['tamu', 'details', 'event', 'event.eventKategori'])
                ->whereDate('created_at', $today)
                ->where($filter)
                ->latest()
                ->get();
            $data = DataTables::of($query)->toArray();

            $start = $req->input('start');
            $resp = [];
            foreach ($data['data'] as $key => $value) {
                $dt = [];
                $dt['no'] = ++$start;
                $dt['waktu_kunjungan'] = $value['created_at'] ? \Carbon\Carbon::parse($value['created_at'])
                    ->setTimezone(config('app.timezone'))->format('H:i') : '-';
                $dt['nama'] = $value['tamu']['nama_tamu'] ?? '-';

                $dt['identitas'] = Kunjungan::getIdentitasBadge($value['identitas']);
                $dt['jenis_kunjungan'] = Kunjungan::getJenisKunjunganBadge($value['event_id']);
                $dt['waktu_keluar'] = $value['waktu_keluar'] ? \Carbon\Carbon::parse($value['waktu_keluar'])
                    ->format('H:i') : '-';
                $dt['status_checkout'] = Kunjungan::getStatusCheckoutBadge($value['is_checkout']);

                $id = encid($value['kunjungan_id']);
                $dataAction = [
                    'id' => $id,
                    'btn' => [
                        ['action' => 'detail', 'title' => 'Lihat Detail', 'attr' => ['jf-detail' => $id]],
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

    public function validasi()
    {
        return $this->show('validasi');
    }

    public function monitoring()
    {
        $this->title = 'Monitoring Kunjungan Hari Ini';
        $this->activeMenu = 'monitoring-kunjungan';
        $this->breadCrump[] = ['title' => 'Monitoring Kunjungan', 'link' => url()->current()];

        $today = \Carbon\Carbon::today();
        $totalKunjunganHariIni = Kunjungan::whereDate('created_at', $today)->count();
        $kunjunganSudahCheckout = Kunjungan::whereDate('created_at', $today)
            ->where('is_checkout', true)->count();

        $builder = app('datatables.html');
        $dataTable = $builder->serverSide(true)
            ->ajax(route('app.kunjungan.data') . '/monitoring-hari-ini')
            ->columns([
                Column::make([
                    'width' => '5%',
                    'title' => 'No',
                    'data' => 'no',
                    'orderable' => false,
                    'className' => 'text-center'
                ]),
                Column::make(['title' => 'Nama Tamu', 'data' => 'nama', 'orderable' => true]),
                Column::make([
                    'title' => 'Identitas',
                    'data' => 'identitas',
                    'orderable' => true,
                    'className' => 'text-center'
                ]),
                Column::make([
                    'title' => 'Jenis Kunjungan',
                    'data' => 'jenis_kunjungan',
                    'orderable' => true,
                    'className' => 'text-center'
                ]),
                Column::make([
                    'title' => 'Waktu Kunjungan',
                    'data' => 'waktu_kunjungan',
                    'orderable' => true,
                    'className' => 'text-center'
                ]),
                Column::make([
                    'title' => 'Waktu Keluar (Estimasi)',
                    'data' => 'waktu_keluar',
                    'orderable' => true,
                    'className' => 'text-center'
                ]),
                Column::make([
                    'title' => 'Status Checkout',
                    'data' => 'status_checkout',
                    'orderable' => true,
                    'className' => 'text-center'
                ]),
                Column::make([
                    'width' => '15%',
                    'title' => 'Aksi',
                    'data' => 'action',
                    'orderable' => false,
                    'className' => 'text-nowrap text-center'
                ]),
            ]);

        $this->dataView([
            'dataTable' => $dataTable,
            'totalKunjunganHariIni' => $totalKunjunganHariIni,
            'kunjunganSudahCheckout' => $kunjunganSudahCheckout,
            'tanggalHariIni' => $today->format('d F Y'),
        ]);

        return $this->view('admin.kunjungan.monitoring');
    }

    public function validateSingle(Request $request, $id): JsonResponse
    {
        $currData = Kunjungan::findOrFail(decid($id));

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
    }

    public function rejectSingle(Request $request, $id): JsonResponse
    {
        $currData = Kunjungan::findOrFail(decid($id));

        DB::beginTransaction();
        try {
            $currData->delete();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Kunjungan berhasil dihapus'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            abort(500, 'Gagal menghapus kunjungan, kesalahan database');
        }
    }

    public function bulkValidasi(Request $request): JsonResponse
    {
        validate_and_response([
            'ids' => ['Parameter data', 'required|array'],
            'action' => ['Aksi', 'required|in:validate,reject'],
        ]);

        $ids = $request->input('ids');
        $action = $request->input('action');

        DB::beginTransaction();
        try {
            $decodedIds = array_map('decid', $ids);
            $kunjungans = Kunjungan::whereIn('kunjungan_id', $decodedIds)->get();

            if ($action === 'validate') {
                Kunjungan::whereIn('kunjungan_id', $decodedIds)->update(['status_validasi' => true]);
                $message = count($kunjungans) . ' kunjungan berhasil divalidasi';
            } else {
                Kunjungan::whereIn('kunjungan_id', $decodedIds)->delete();
                $message = count($kunjungans) . ' kunjungan berhasil dihapus';
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => $message
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            abort(500, 'Gagal melakukan bulk action, kesalahan database');
        }
    }
}
