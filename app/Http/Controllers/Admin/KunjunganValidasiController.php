<?php

namespace App\Http\Controllers\Admin;

use App\Enums\KategoriTujuanEnum;
use App\Http\Controllers\Controller;
use App\Models\Kunjungan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Column;

class KunjunganValidasiController extends Controller
{
    public function index()
    {
        $this->title = 'Validasi Kunjungan';
        $this->activeMenu = 'validasi-kunjungan';
        $this->breadCrump[] = ['title' => 'Validasi Kunjungan', 'link' => route('app.kunjungan-validasi.index')];

        $builder = app('datatables.html');
        $dataTable = $builder->serverSide(true)
            ->ajax(route('app.kunjungan-validasi.data') . '/validasi-list')
            ->columns([
                Column::make([
                    'width' => '3%',
                    'title' => '<div class="form-check form-check-sm form-check-custom form-check-solid">
                    <input class="form-check-input" type="checkbox" id="checkAllValidasi" data-cy="checkbox-check-all-validasi-kunjungan"></div>',
                    'data' => 'checkbox',
                    'orderable' => false,
                    'className' => 'text-center',
                    'searchable' => false
                ]),
                Column::make([
                    'title' => 'Aksi',
                    'data' => 'action',
                    'orderable' => false,
                    'className' => 'text-nowrap text-center'
                ]),
                Column::make([
                    'title' => 'Waktu Kunjungan',
                    'data' => 'waktu_kunjungan',
                    'orderable' => true,
                ]),
                Column::make([
                    'width' => '5%',
                    'title' => 'No',
                    'data' => 'no',
                    'orderable' => false,
                    'className' => 'text-center'
                ]),
                Column::make([
                    'title' => 'Identitas',
                    'data' => 'identitas',
                    'orderable' => true,
                ]),
                Column::make(['title' => 'Nama Tamu', 'data' => 'nama', 'orderable' => true]),
                Column::make([
                    'title' => 'Jenis Kelamin',
                    'data' => 'jenis_kelamin',
                    'orderable' => true,
                ]),
                Column::make(['title' => 'Email', 'data' => 'email', 'orderable' => true]),
                Column::make(['title' => 'No. Telepon', 'data' => 'nomor_telepon', 'orderable' => true]),
                Column::make([
                    'title' => 'Tujuan Kunjungan',
                    'data' => 'jenis_kunjungan',
                    'orderable' => true,
                ]),
            ]);

        $this->dataView([
            'dataTable' => $dataTable,
        ]);

        return $this->view('admin.validasi-kunjungan.list');
    }

    public function show($param1 = '', $param2 = '')
    {
        if ($param1 == 'dihapus') {
            $this->title = 'Kunjungan yang Dihapus';
            $this->activeMenu = 'validasi-kunjungan-dihapus';
            $this->breadCrump[] = ['title' => 'Kunjungan yang Dihapus', 'link' =>  url()->current()];

            $countValidasi = Kunjungan::where('status_validasi', false)->whereNull('deleted_at')->count();
            $countDihapus = Kunjungan::where('status_validasi', false)->onlyTrashed()->count();

            $builder = app('datatables.html');
            $dataTable = $builder->serverSide(true)
                ->ajax(route('app.kunjungan-validasi.data') . '/validasi-dihapus-list')
                ->columns([
                    Column::make([
                        'width' => '3%',
                        'title' => '<div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" id="checkAllValidasi" data-cy="checkbox-check-all-validasi-kunjungan"></div>',
                        'data' => 'checkbox',
                        'orderable' => false,
                        'className' => 'text-center',
                        'searchable' => false
                    ]),
                    Column::make([
                        'title' => 'Aksi',
                        'data' => 'action',
                        'orderable' => false,
                        'className' => 'text-nowrap text-center'
                    ]),
                    Column::make([
                        'title' => 'Waktu Kunjungan',
                        'data' => 'waktu_kunjungan',
                        'orderable' => true,
                    ]),
                    Column::make([
                        'width' => '5%',
                        'title' => 'No',
                        'data' => 'no',
                        'orderable' => false,
                        'className' => 'text-center'
                    ]),
                    Column::make([
                        'title' => 'Identitas',
                        'data' => 'identitas',
                        'orderable' => true,
                    ]),
                    Column::make(['title' => 'Nama Tamu', 'data' => 'nama', 'orderable' => true]),
                    Column::make([
                        'title' => 'Jenis Kelamin',
                        'data' => 'jenis_kelamin',
                        'orderable' => true,
                    ]),
                    Column::make(['title' => 'Email', 'data' => 'email', 'orderable' => true]),
                    Column::make(['title' => 'No. Telepon', 'data' => 'nomor_telepon', 'orderable' => true]),
                    Column::make([
                        'title' => 'Tujuan Kunjungan',
                        'data' => 'jenis_kunjungan',
                        'orderable' => true,
                    ]),
                ]);

            $this->dataView([
                'dataTable' => $dataTable,
                'countValidasi' => $countValidasi,
                'countDihapus' => $countDihapus,
            ]);

            return $this->view('admin.validasi-kunjungan.dihapus');
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }

    public function data(Request $req, $param1 = '', $param2 = ''): JsonResponse
    {
        if ($param1 !== '' && !in_array($param1, ['validasi-list', 'validasi-dihapus-list'])) {
            abort(404, 'Halaman tidak ditemukan');
        }

        $filterJK             = $req->input('filter_jenis_kelamin', '');
        $filterIdentitas      = $req->input('filter_identitas', '');
        $filterJenisKunjungan = $req->input('filter_jenis_kunjungan', '');
        $filterDateFrom       = $req->input('filter_date_from', '');
        $filterDateTo         = $req->input('filter_date_to', '');

        $query = Kunjungan::withTrashed()->select([
            'kunjungan.kunjungan_id',
            'kunjungan.tamu_id',
            'tamu.nama_tamu',
            'tamu.email_tamu',
            'tamu.jenis_kelamin_tamu',
            'tamu.nomor_telepon_tamu',
            'kunjungan.civitas_id',
            'civitas.nama_civitas',
            'civitas.jenis_kelamin',
            'civitas.email',
            'civitas.nomor_telepon',
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
            ->where('kunjungan.status_validasi', false)
            ->when(!empty($filterJK), function ($q) use ($filterJK) {
                $q->where(function ($q) use ($filterJK) {
                    $q->where('tamu.jenis_kelamin_tamu', $filterJK)
                        ->orWhere('civitas.jenis_kelamin', $filterJK);
                });
            })
            ->when(!empty($filterIdentitas), function ($q) use ($filterIdentitas) {
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
            ->when(!empty($filterJenisKunjungan), function ($q) use ($filterJenisKunjungan) {
                if ($filterJenisKunjungan === 'event') {
                    $q->whereNotNull('kunjungan.event_id');
                } else {
                    $q->whereNull('kunjungan.event_id');
                }
            })
            ->when(!empty($filterDateFrom), fn($q) => $q->whereDate('kunjungan.created_at', '>=', $filterDateFrom))
            ->when(!empty($filterDateTo), fn($q) => $q->whereDate('kunjungan.created_at', '<=', $filterDateTo));

        if ($param1 === 'validasi-dihapus-list') {
            $query->whereNotNull('kunjungan.deleted_at');
        } else {
            $query->whereNull('kunjungan.deleted_at');
        }

        $start = (int) $req->input('start', 0);

        return DataTables::of($query)
            ->addColumn('checkbox', function ($row) {
                $id = encid($row->kunjungan_id);
                return '<div class="form-check form-check-sm form-check-custom form-check-solid">'
                    . '<input class="form-check-input row-checkbox" type="checkbox" value="' . $id . '" data-id="' . $id . '" data-cy="checkbox-row-validasi-kunjungan-' . $id . '">'
                    . '</div>';
            })
            ->addColumn('no', function () use (&$start) {
                return ++$start;
            })
            ->addColumn('nama', function ($row) {
                return $row->nama_tamu ?? $row->nama_civitas ?? '-';
            })
            ->addColumn('jenis_kelamin', function ($row) {
                return $row->jenis_kelamin_tamu ?? $row->jenis_kelamin ?? '-';
            })
            ->addColumn('email', function ($row) {
                return $row->email_tamu ?? $row->email ?? '-';
            })
            ->addColumn('nomor_telepon', function ($row) {
                return $row->nomor_telepon_tamu ?? $row->nomor_telepon ?? '-';
            })
            ->addColumn('identitas', function ($row) {
                return Kunjungan::getIdentitasBadge($row->identitas, $row->is_vip);
            })
            ->addColumn('jenis_kunjungan', function ($row) {
                $detail = $row->event_id
                    ? ($row->event?->nama_event ?? $row->nama_event)
                    : (KategoriTujuanEnum::getDescription($row->kategori_tujuan?->value) ?? '-');
                $badge = Kunjungan::getJenisKunjunganBadge($row->event_id);
                return "{$detail}<br/>{$badge}";
            })
            ->addColumn('waktu_kunjungan', function ($row) {
                return $row->created_at ? tanggal($row->created_at) . ' ' . Carbon::parse($row->created_at)->setTimezone(config('app.timezone'))->format('H:i') : '-';
            })
            ->addColumn('action', function ($row) {
                $id = encid($row->kunjungan_id);
                $dataAction = [
                    'id'  => $id,
                    'btn' => [
                        ['action' => 'detail', 'title' => 'Lihat Detail', 'attr' => ['jf-detail' => $id]],
                    ]
                ];
                return Blade::render('<x-btn.actiontable :id="$id" :btn="$btn"/>', $dataAction);
            })
            ->rawColumns(['checkbox', 'identitas', 'jenis_kunjungan', 'action'])
            ->orderColumn('waktu_kunjungan', 'created_at $1')
            ->orderColumn('identitas', 'kunjungan.identitas $1')
            ->orderColumn('nama', 'COALESCE(tamu.nama_tamu, civitas.nama_civitas) $1')
            ->orderColumn('jenis_kelamin', 'COALESCE(tamu.jenis_kelamin_tamu, civitas.jenis_kelamin) $1')
            ->orderColumn('email', 'COALESCE(tamu.email_tamu, civitas.email) $1')
            ->orderColumn('nomor_telepon', 'COALESCE(tamu.nomor_telepon_tamu, civitas.nomor_telepon) $1')
            ->orderColumn('jenis_kunjungan', 'kunjungan.event_id $1')
            ->filterColumn('waktu_kunjungan', fn($query, $keyword) =>
            dtFilterByDateKeyword($query, $keyword, 'kunjungan.created_at'))
            ->filterColumn('identitas', function ($query, $keyword) {
                $query->where('kunjungan.identitas', 'like', "%{$keyword}%");
            })
            ->filterColumn('nama', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('tamu.nama_tamu', 'like', "%{$keyword}%")
                        ->orWhere('civitas.nama_civitas', 'like', "%{$keyword}%");
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
            ->filterColumn('jenis_kunjungan', function ($query, $keyword) {
                $matchedValues = [];
                foreach (KategoriTujuanEnum::cases() as $case) {
                    if (stripos($case->description(), $keyword) !== false || stripos($case->value, $keyword) !== false) {
                        $matchedValues[] = $case->value;
                    }
                }
                $query->where(function ($q) use ($matchedValues, $keyword) {

                    if (!empty($matchedValues)) {
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
            ->toJson();
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
    public function restoreSingle(Request $request, $id): JsonResponse
    {
        $currData = Kunjungan::withTrashed()->findOrFail(decid($id));

        DB::beginTransaction();
        try {
            $currData->restore();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Data kunjungan berhasil direstore'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            abort(500, 'Gagal merestore kunjungan, kesalahan database');
        }
    }

    public function forceDeleteSingle(Request $request, $id): JsonResponse
    {
        $currData = Kunjungan::withTrashed()->findOrFail(decid($id));

        DB::beginTransaction();
        try {
            $currData->forceDelete();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Data kunjungan berhasil dihapus permanen'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            abort(500, 'Gagal menghapus kunjungan secara permanen, kesalahan database');
        }
    }

    public function bulkRestore(Request $request): JsonResponse
    {
        validate_and_response([
            'ids' => ['Parameter data', 'required|array'],
        ]);

        $ids = $request->input('ids');

        DB::beginTransaction();
        try {
            $decodedIds = array_map('decid', $ids);
            $kunjungans = Kunjungan::onlyTrashed()->whereIn('kunjungan_id', $decodedIds)->get();

            Kunjungan::onlyTrashed()->whereIn('kunjungan_id', $decodedIds)->restore();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => count($kunjungans) . ' kunjungan berhasil direstore'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            abort(500, 'Gagal melakukan restore masal, kesalahan database');
        }
    }

    public function bulkForceDelete(Request $request): JsonResponse
    {
        validate_and_response([
            'ids' => ['Parameter data', 'required|array'],
        ]);

        $ids = $request->input('ids');

        DB::beginTransaction();
        try {
            $decodedIds = array_map('decid', $ids);
            $kunjungans = Kunjungan::onlyTrashed()->whereIn('kunjungan_id', $decodedIds)->get();

            foreach ($kunjungans as $kunjungan) {
                $kunjungan->forceDelete();
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => count($kunjungans) . ' kunjungan berhasil dihapus permanen'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            abort(500, 'Gagal melakukan hapus permanen masal, kesalahan database');
        }
    }
}
