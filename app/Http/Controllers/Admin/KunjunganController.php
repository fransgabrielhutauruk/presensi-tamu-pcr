<?php

namespace App\Http\Controllers\Admin;

use App\Enums\KategoriTujuanEnum;
use App\Http\Controllers\Controller;
use App\Models\Kunjungan;
use App\Models\MstOpsiKunjungan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Column;

class KunjunganController extends Controller
{
    public function index(Request $request)
    {
        $this->title = 'Kunjungan';
        $this->activeMenu = 'kunjungan';
        $this->breadCrump[] = ['title' => 'Kunjungan', 'link' => route('app.kunjungan.index')];

        $availableColumns = [
            'action' => [
                'title' => 'Aksi',
                'data' => 'action',
                'orderable' => false,
                'className' => 'text-nowrap text-center',
                'required' => true,
            ],
            'no' => [
                'title' => 'No',
                'data' => 'no',
                'orderable' => false,
                'required' => true,
                'className' => 'text-center',
            ],
            'waktu_kunjungan' => [
                'title' => 'Waktu Kunjungan',
                'data' => 'waktu_kunjungan',
                'orderable' => true,
            ],
            'identitas' => [
                'title' => 'Identitas',
                'data' => 'identitas',
                'orderable' => true,
            ],
            'nama' => ['title' => 'Nama Tamu', 'data' => 'nama', 'orderable' => true],
            'jenis_kelamin' => [
                'title' => 'Jenis Kelamin',
                'data' => 'jenis_kelamin',
                'orderable' => true,
            ],
            'email' => ['title' => 'Email', 'data' => 'email', 'orderable' => true],
            'nomor_telepon' => ['title' => 'No. Telepon', 'data' => 'nomor_telepon', 'orderable' => true],
            'jenis_kunjungan' => [
                'title' => 'Tujuan Kunjungan',
                'data' => 'jenis_kunjungan',
                'orderable' => true,
            ],
            'transportasi' => [
                'title' => 'Transportasi',
                'data' => 'transportasi',
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
        ];

        $defaultColumns = ['action', 'no', 'waktu_kunjungan', 'identitas', 'nama', 'jenis_kelamin',  'jenis_kunjungan'];

        $selectedColumns = $defaultColumns;

        if ($request->has('columns')) {
            $selectedColumns = $request->input('columns', []);
            if (! in_array('action', $selectedColumns)) {
                array_unshift($selectedColumns, 'action');
            }
            if (! in_array('no', $selectedColumns)) {
                array_unshift($selectedColumns, 'no');
            }
        }

        $columns = [];
        foreach ($selectedColumns as $columnKey) {
            if (isset($availableColumns[$columnKey])) {
                $columns[] = Column::make($availableColumns[$columnKey]);
            }
        }

        $builder = app('datatables.html');
        $dataTable = $builder->serverSide(true)->ajax(route('app.kunjungan.data').'/list')->columns($columns);

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
            $dataTable = $builder->serverSide(true)->ajax(route('app.kunjungan.data').'/opsi-list')->columns([
                Column::make([
                    'title' => 'Aksi',
                    'data' => 'action',
                    'orderable' => false,
                    'className' => 'text-nowrap text-center',
                ]),
                Column::make([
                    'title' => 'No',
                    'data' => 'no',
                    'orderable' => false,
                    'className' => 'text-center',
                ]),
                Column::make(['title' => 'Nama Opsi', 'data' => 'nama_opsi', 'orderable' => true]),
                Column::make([
                    'title' => 'Deskripsi',
                    'data' => 'deskripsi_opsi',
                    'orderable' => true,
                ]),
                Column::make(['title' => 'Nilai Opsi', 'data' => 'nilai_opsi', 'orderable' => false]),
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
                        if (! isset($item['id']) || ! isset($item['en'])) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Format data tidak valid. Setiap item harus memiliki id (Indonesia) '.
                                    'dan en (English).',
                            ], 422);
                        }

                        $item['id'] = trim($item['id']);
                        $item['en'] = trim($item['en']);

                        if (empty($item['id']) || empty($item['en'])) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Semua field (Indonesia, English) wajib diisi dan tidak boleh kosong.',
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
                    'message' => 'Data Opsi kunjungan berhasil disimpan',
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
                        if (! isset($item['id']) || ! isset($item['en'])) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Format data tidak valid. Setiap item harus memiliki id (Indonesia) '.
                                    'dan en (English).',
                            ], 422);
                        }

                        $item['id'] = trim($item['id']);
                        $item['en'] = trim($item['en']);

                        if (empty($item['id']) || empty($item['en'])) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Semua field (Indonesia, English) wajib diisi dan tidak boleh kosong.',
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
                    'message' => 'Opsi kunjungan berhasil diperbarui',
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
        } elseif ($param1 == 'opsi') {
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
            $filterJK = $req->input('filter_jenis_kelamin', '');
            $filterIdentitas = $req->input('filter_identitas', '');
            $filterJenisKunjungan = $req->input('filter_jenis_kunjungan', '');
            $filterDateFrom = $req->input('filter_date_from', '');
            $filterDateTo = $req->input('filter_date_to', '');

            $query = Kunjungan::select([
                'kunjungan.kunjungan_id',
                'kunjungan.tamu_id',
                'tamu.nama_tamu',
                'tamu.jenis_kelamin_tamu',
                'kunjungan.civitas_id',
                'civitas.nama_civitas',
                'civitas.jenis_kelamin',
                'kunjungan.event_id',
                'kunjungan.identitas',
                'kunjungan.kategori_tujuan',
                'kunjungan.transportasi',
                'kunjungan.waktu_keluar',
                'kunjungan.checkout_time',
                'kunjungan.is_checkout',
                'kunjungan.status_validasi',
                'kunjungan.is_vip',
                'kunjungan.created_at',
                'event.nama_event',
            ])
                ->leftJoin('tamu', function ($join) {
                    $join->on('kunjungan.tamu_id', '=', 'tamu.tamu_id')
                        ->whereNull('tamu.deleted_at');
                })
                ->leftJoin('civitas', function ($join) {
                    $join->on('kunjungan.civitas_id', '=', 'civitas.civitas_id')
                        ->whereNull('civitas.deleted_at');
                })
                ->leftJoin('event', function ($join) {
                    $join->on('kunjungan.event_id', '=', 'event.event_id')
                        ->whereNull('event.deleted_at');
                })
                ->leftJoin('event_kategori', function ($join) {
                    $join->on('event_kategori.eventkategori_id', '=', 'event.eventkategori_id')
                        ->whereNull('event_kategori.deleted_at');
                })
                ->where(function ($q) {
                    $q->whereNull('kunjungan.event_id')
                        ->orWhereNotNull('event.event_id');
                })
                ->where('kunjungan.status_validasi', true)
                ->when(! empty($filterJK), function ($q) use ($filterJK) {
                    $q->where(function ($q) use ($filterJK) {
                        $q->where('tamu.jenis_kelamin_tamu', $filterJK)
                            ->orWhere('civitas.jenis_kelamin', $filterJK);
                    });
                })
                ->when(! empty($filterIdentitas), function ($q) use ($filterIdentitas) {
                    if ($filterIdentitas === 'vip') {
                        $q->where('kunjungan.identitas', 'non-civitas')
                            ->where('kunjungan.is_vip', 1);
                    } elseif ($filterIdentitas === 'non-civitas') {
                        $q->where('kunjungan.identitas', 'non-civitas')
                            ->where('kunjungan.is_vip', 0);
                    } else {
                        $q->where('kunjungan.identitas', $filterIdentitas);
                    }
                })
                ->when(! empty($filterJenisKunjungan), function ($q) use ($filterJenisKunjungan) {
                    if ($filterJenisKunjungan === 'event') {
                        $q->whereNotNull('kunjungan.event_id');
                    } else {
                        $q->whereNull('kunjungan.event_id');
                    }
                })
                ->when(! empty($filterDateFrom), fn ($q) => $q->whereDate('kunjungan.created_at', '>=', $filterDateFrom))
                ->when(! empty($filterDateTo), fn ($q) => $q->whereDate('kunjungan.created_at', '<=', $filterDateTo));

            $start = (int) $req->input('start', 0);

            return DataTables::of($query)
                ->addColumn('no', function () use (&$start) {
                    return ++$start;
                })
                ->addColumn('waktu_kunjungan', function ($row) {
                    return $row->created_at ? tanggal($row->created_at).' '.Carbon::parse($row->created_at)->format('H:i') : '-';
                })
                ->addColumn('nama', function ($row) {
                    return $row->nama_tamu ?? $row->nama_civitas ?? '-';
                })
                ->addColumn('jenis_kelamin', function ($row) {
                    return $row->jenis_kelamin_tamu ?? $row->jenis_kelamin ?? '-';
                })
                ->addColumn('email', function ($row) {
                    return $row->tamu->email_tamu ?? $row->civitas->email ?? '-';
                })
                ->addColumn('nomor_telepon', function ($row) {
                    return $row->tamu->nomor_telepon_tamu ?? $row->civitas->nomor_telepon ?? '-';
                })
                ->addColumn('transportasi', function ($row) {
                    return $row->transportasi ?? '-';
                })
                ->addColumn('identitas', function ($row) {
                    return Kunjungan::getIdentitasBadge($row->identitas, $row->is_vip);
                })
                ->addColumn('waktu_keluar', function ($row) {
                    return $row->waktu_keluar ? Carbon::parse($row->waktu_keluar)->format('H:i') : '-';
                })
                ->addColumn('checkout_time', function ($row) {
                    return $row->checkout_time ? Carbon::parse($row->checkout_time)->format('H:i') : '-';
                })
                ->addColumn('jenis_kunjungan', function ($row) {
                    $detail = $row->event_id
                        ? ($row->event?->nama_event ?? $row->nama_event)
                        : (KategoriTujuanEnum::getDescription($row->kategori_tujuan?->value) ?? '-');
                    $badge = Kunjungan::getJenisKunjunganBadge($row->event_id);

                    return "{$detail}<br/>{$badge}";
                })
                ->addColumn('status_validasi', function ($row) {
                    return Kunjungan::getStatusValidasiBadge($row->status_validasi);
                })
                ->addColumn('is_checkout', function ($row) {
                    return Kunjungan::getStatusCheckoutBadge($row->is_checkout);
                })
                ->addColumn('action', function ($row) {
                    $id = encid($row->kunjungan_id);
                    $dataAction = [
                        'id' => $id,
                        'btn' => [
                            ['action' => 'detail', 'attr' => ['jf-detail' => $id]],
                            ['action' => 'delete', 'attr' => ['jf-delete' => $id]],
                        ],
                    ];

                    return Blade::render('<x-btn.actiontable :id="$id" :btn="$btn"/>', $dataAction);
                })
                ->rawColumns(['identitas', 'jenis_kunjungan', 'status_validasi', 'is_checkout', 'action'])
                ->orderColumn('waktu_kunjungan', 'created_at $1')
                ->orderColumn('identitas', 'kunjungan.identitas $1')
                ->orderColumn('nama', 'COALESCE(tamu.nama_tamu, civitas.nama_civitas) $1')
                ->orderColumn('jenis_kelamin', 'COALESCE(tamu.jenis_kelamin_tamu, civitas.jenis_kelamin) $1')
                ->orderColumn('email', 'COALESCE(tamu.email_tamu, civitas.email) $1')
                ->orderColumn('nomor_telepon', 'COALESCE(tamu.nomor_telepon_tamu, civitas.nomor_telepon) $1')
                ->orderColumn('jenis_kunjungan', 'kunjungan.event_id $1')
                ->orderColumn('jenis_kunjungan', 'kunjungan.event_id $1')
                ->orderColumn('is_checkout', 'is_checkout $1')
                ->orderColumn('transportasi', 'transportasi $1')
                ->orderColumn('waktu_keluar', 'waktu_keluar $1')
                ->orderColumn('checkout_time', 'checkout_time $1')
                ->filterColumn('waktu_kunjungan', fn ($query, $keyword) => dtFilterByDateKeyword($query, $keyword, 'kunjungan.created_at'))
                ->filterColumn('nama', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('tamu.nama_tamu', 'like', "%{$keyword}%")
                            ->orWhere('civitas.nama_civitas', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('jenis_kelamin', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('tamu.jenis_kelamin_tamu', 'like', "%{$keyword}%")
                            ->orWhere('civitas.jenis_kelamin', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('email', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('tamu.email_tamu', 'like', "%{$keyword}%")
                            ->orWhere('civitas.email', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('nomor_telepon', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('tamu.nomor_telepon_tamu', 'like', "%{$keyword}%")
                            ->orWhere('civitas.nomor_telepon', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('identitas', function ($query, $keyword) {
                    $query->where('kunjungan.identitas', 'like', "%{$keyword}%");
                })
                ->filterColumn('transportasi', function ($query, $keyword) {
                    $query->where('kunjungan.transportasi', 'like', "%{$keyword}%");
                })
                ->filterColumn('waktu_keluar', function ($query, $keyword) {
                    $query->where('kunjungan.waktu_keluar', 'like', "%{$keyword}%");
                })
                ->filterColumn('checkout_time', function ($query, $keyword) {
                    $query->where('kunjungan.checkout_time', 'like', "%{$keyword}%");
                })
                ->filterColumn('jenis_kunjungan', function ($query, $keyword) {
                    $matchedValues = [];
                    foreach (KategoriTujuanEnum::cases() as $case) {
                        if (stripos($case->description(), $keyword) !== false || stripos($case->value, $keyword) !== false) {
                            $matchedValues[] = $case->value;
                        }
                    }
                    $query->where(function ($q) use ($matchedValues, $keyword) {

                        if (! empty($matchedValues)) {
                            $q->whereIn('kunjungan.kategori_tujuan', $matchedValues);
                        } else {
                            $q->where('kunjungan.kategori_tujuan', 'like', "%{$keyword}%");
                        }

                        $q->orWhere('event.nama_event', 'like', "%{$keyword}%");

                        if (stripos('event', $keyword) !== false) {
                            $q->orWhereNotNull('kunjungan.event_id');
                        } elseif (stripos('non-event', $keyword) !== false) {
                            $q->orWhereNull('kunjungan.event_id');
                        }
                    });
                })
                ->filterColumn('is_checkout', function ($query, $keyword) {
                    $isChecked = stripos($keyword, 'sudah') !== false ? true
                        : (stripos($keyword, 'belum') !== false ? false : null);
                    if ($isChecked !== null) {
                        $query->where('kunjungan.is_checkout', $isChecked);
                    }
                })
                ->toJson();
        } elseif ($param1 == 'detail') {
            validate_and_response([
                'id' => ['Parameter data', 'required'],
            ]);

            $currData = Kunjungan::withTrashed()
                ->with(['tamu', 'civitas', 'details', 'event', 'event.eventKategori'])
                ->findOrFail(decid($req->input('id')));

            $detailData = [
                'kunjungan_id' => $currData->kunjungan_id,
                'id' => $req->input('id'),

                'nama' => $currData->tamu->nama_tamu ?? $currData->civitas->nama_civitas ?? '',
                'jenis_kelamin' => $currData->tamu->jenis_kelamin_tamu ?? $currData->civitas->jenis_kelamin ?? '',
                'email' => $currData->tamu->email_tamu ?? $currData->civitas->email ?? '',
                'nomor_telepon' => $currData->tamu->nomor_telepon_tamu ?? $currData->civitas->nomor_telepon ?? '',

                'jenis_kunjungan' => ! empty($currData->event_id) ? 'Event' : 'Non-Event',
                'kategori_tujuan' => KategoriTujuanEnum::getDescription($currData->kategori_tujuan?->value) ?? '-',
                'identitas' => match ($currData->identitas) {
                    'civitas' => 'Civitas PCR',
                    'non-civitas' => $currData->is_vip ? 'Non-Civitas (VIP)' : 'Non-Civitas',
                    default => $currData->identitas ?? '-',
                },
                'transportasi' => $currData->transportasi ?? '',
                'status_validasi' => $currData->status_validasi ? 'Sudah validasi' : 'Belum validasi',

                'tanggal_kunjungan' => $currData->created_at ? tanggal($currData->created_at) : '',
                'waktu_kunjungan' => $currData->created_at ? $currData->created_at->format('H:i') : '-',
                'waktu_estimasi_keluar' => $currData->waktu_keluar ? \Carbon\Carbon::parse($currData->waktu_keluar)
                    ->format('H:i') : '-',
                'waktu_checkout' => $currData->checkout_time ? $currData->checkout_time->format('H:i') : '-',

                'event_nama' => $currData->event->nama_event ?? '-',
                'event_kategori' => $currData->event->eventKategori->nama_kategori ?? '-',
                'event_kategori_lokasi' => match ($currData->event?->kategori_lokasi) {
                    'dalam_kampus' => 'Dalam Kampus',
                    'luar_kampus' => 'Luar Kampus',
                    default => '-',
                },
                'event_lokasi' => $currData->event?->lokasi_event ?? '-',
                'details' => [],
            ];

            foreach ($currData->details as $detail) {
                $detailData['details'][] = [
                    'kunci' => $detail->kunci,
                    'nilai' => $detail->nilai,
                    'urutan' => $detail->urutan,
                ];
            }

            return response()->json(['status' => true, 'message' => 'Data loaded', 'data' => $detailData]);
        } elseif ($param1 == 'opsi-list') {
            $filter = [];
            $data = DataTables::of(MstOpsiKunjungan::where($filter))->toArray();
            $start = $req->input('start');
            $resp = [];
            foreach ($data['data'] as $key => $value) {
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

                    $dt['nilai_opsi'] = '<span class="badge badge-light-primary fs-7">'.count($nilaiOpsi).
                        ' item</span><br><small class="text-muted">'.implode(', ', array_slice($labels, 0, 3)).
                        (count($labels) > 3 ? '...' : '').'</small>';
                } else {
                    $dt['nilai_opsi'] = '<span class="text-muted">Tidak ada item</span>';
                }

                $id = encid($value['opsikunjungan_id']);
                $dataAction = [
                    'id' => $id,
                    'btn' => [
                        ['action' => 'edit', 'attr' => ['jf-edit' => $id]],
                        // ['action' => 'delete', 'attr' => ['jf-delete' => $id]],
                    ],
                ];
                $dt['action'] = Blade::render('<x-btn.actiontable :id="$id" :btn="$btn"/>', $dataAction);
                $resp[] = $dt;
            }
            $data['data'] = $resp;

            return response()->json($data);
        } elseif ($param1 == 'opsi-detail') {
            validate_and_response([
                'id' => ['Parameter data', 'required'],
            ]);
            $currData = MstOpsiKunjungan::findOrFail(decid($req->input('id')));
            $currData->id = $req->input('id');

            return response()->json([
                'status' => true,
                'message' => 'Data loaded',
                'data' => $currData,
            ]);
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }
}
