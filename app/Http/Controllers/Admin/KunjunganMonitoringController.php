<?php

namespace App\Http\Controllers\Admin;

use App\Enums\KategoriTujuanEnum;
use App\Http\Controllers\Controller;
use App\Models\Kunjungan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Html\Column;

class KunjunganMonitoringController extends Controller
{
    private const PARAM_MONITORING = 'monitoring-hari-ini';

    public function index()
    {
        $this->title = 'Monitoring Kunjungan Hari Ini di PCR';
        $this->activeMenu = 'monitoring-kunjungan';
        $this->breadCrump[] = ['title' => 'Monitoring Kunjungan', 'link' => url()->current()];

        $today = Carbon::today();
        $stats = $this->getTodayStatistics();

        $builder = app('datatables.html');
        $dataTable = $builder->serverSide(true)
            ->ajax(route('app.kunjungan.data') . '/' . self::PARAM_MONITORING)
            ->columns([
                Column::make(['title' => 'Aksi', 'data' => 'action', 'orderable' => false, 'className' => 'text-nowrap text-center']),
                Column::make(['title' => 'No', 'data' => 'no', 'orderable' => false, 'className' => 'text-center']),
                Column::make(['title' => 'Jam Kedatangan', 'data' => 'jam_kedatangan', 'orderable' => true]),
                Column::make(['width' => '10%', 'title' => 'Estimasi Jam Kepulangan', 'data' => 'waktu_keluar', 'orderable' => true]),
                Column::make(['title' => 'Jam Checkout', 'data' => 'waktu_checkout', 'orderable' => true]),
                Column::make(['title' => 'Identitas', 'data' => 'identitas', 'orderable' => true]),
                Column::make(['title' => 'Nama Tamu', 'data' => 'nama', 'orderable' => true]),
                Column::make(['title' => 'Jenis Kelamin', 'data' => 'jenis_kelamin', 'orderable' => true]),
                Column::make(['title' => 'Tujuan Kunjungan', 'data' => 'jenis_kunjungan', 'orderable' => true]),
            ]);

        $this->dataView([
            'dataTable'               => $dataTable,
            'totalKunjunganHariIni'   => $stats['total'],
            'kunjunganSudahCheckout'  => $stats['checkout'],
            'kunjunganBelumCheckout'  => $stats['belum_checkout'],
            'tanggalHariIni'          => tanggal($today->toDateString()),
        ]);

        return $this->view('admin.monitoring.list');
    }

    public function data(Request $req, $param1 = '', $param2 = ''): JsonResponse
    {
        if ($param1 !== '' && $param1 !== self::PARAM_MONITORING) {
            abort(404, 'Halaman tidak ditemukan');
        }

        $today                = Carbon::today();
        $filterJenisKunjungan = $req->input('filter_jenis_kunjungan', '');
        $filterCheckout       = $req->input('filter_is_checkout', '');
        $filterJK             = $req->input('filter_jenis_kelamin', '');
        $filterIdentitas      = $req->input('filter_identitas', '');
        $start                = (int) $req->input('start', 0);

        $query = Kunjungan::select([
            'kunjungan.kunjungan_id',
            'kunjungan.tamu_id',
            'tamu.jenis_kelamin_tamu',
            'kunjungan.civitas_id',
            'civitas.jenis_kelamin',
            'kunjungan.event_id',
            'event.nama_event',
            'event.kategori_lokasi',
            'kunjungan.kategori_tujuan',
            'kunjungan.identitas',
            'kunjungan.is_vip',
            'kunjungan.is_checkout',
            'kunjungan.waktu_keluar',
            'kunjungan.checkout_time',
            'kunjungan.created_at',
            'tamu.nama_tamu',
            'civitas.nama_civitas',
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
            ->whereDate('kunjungan.created_at', $today)
            ->where(function ($q) {
                $q->whereNull('kunjungan.event_id')
                    ->orWhere('event.kategori_lokasi', 'dalam_kampus');
            })
            ->when(!empty($filterJenisKunjungan), function ($q) use ($filterJenisKunjungan) {
                match ($filterJenisKunjungan) {
                    'event'     => $q->whereNotNull('kunjungan.event_id'),
                    'non_event' => $q->whereNull('kunjungan.event_id'),
                    default     => null,
                };
            })
            ->when($filterCheckout === '1' || $filterCheckout === '0', function ($q) use ($filterCheckout) {
                $q->where('kunjungan.is_checkout', $filterCheckout === '1');
            })
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
            });

        return DataTables::of($query)
            ->addColumn('no', function () use (&$start) {
                return ++$start;
            })
            ->addColumn('nama', fn($row) => $row->nama_tamu ?? $row->nama_civitas ?? '-')
            ->addColumn('jenis_kelamin', function ($row) {
                return $row->jenis_kelamin_tamu ?? $row->jenis_kelamin ?? '-';
            })
            ->addColumn('identitas', fn($row) => Kunjungan::getIdentitasBadge($row->identitas, $row->is_vip))
            ->addColumn('jenis_kunjungan', function ($row) {
                $detail = $row->event_id
                    ? ($row->event?->nama_event ?? $row->nama_event)
                    : (KategoriTujuanEnum::getDescription($row->kategori_tujuan?->value) ?? '-');
                $badge = Kunjungan::getJenisKunjunganBadge($row->event_id);
                return "{$detail}<br/>{$badge}";
            })
            ->addColumn('jam_kedatangan', function ($row) {
                return $row->created_at
                    ? Carbon::parse($row->created_at)->setTimezone(config('app.timezone'))->format('H:i')
                    : '-';
            })
            ->addColumn('waktu_keluar', function ($row) {
                return $row->waktu_keluar
                    ? Carbon::parse($row->waktu_keluar)->format('H:i')
                    : '-';
            })
            ->addColumn('waktu_checkout', function ($row) {
                return $row->is_checkout
                    ? Carbon::parse($row->checkout_time)->setTimezone(config('app.timezone'))->format('H:i')
                    : '<span class="badge badge-secondary">Belum Checkout</span>';
            })
            ->addColumn('action', function ($row) {
                $id         = encid($row->kunjungan_id);
                $dataAction = [
                    'id'  => $id,
                    'btn' => [
                        ['action' => 'detail', 'title' => 'Lihat Detail', 'attr' => ['jf-detail' => $id]],
                    ]
                ];
                return Blade::render('<x-btn.actiontable :id="$id" :btn="$btn"/>', $dataAction);
            })
            ->rawColumns(['identitas', 'jenis_kunjungan', 'waktu_checkout', 'action'])
            ->orderColumn('jam_kedatangan', 'kunjungan.created_at $1')
            ->orderColumn('waktu_keluar', 'kunjungan.waktu_keluar $1')
            ->orderColumn('waktu_checkout', 'kunjungan.checkout_time $1')
            ->orderColumn('identitas', 'kunjungan.identitas $1')
            ->orderColumn('nama', 'COALESCE(tamu.nama_tamu, civitas.nama_civitas) $1')
            ->orderColumn('jenis_kelamin', 'COALESCE(tamu.jenis_kelamin_tamu, civitas.jenis_kelamin) $1')
            ->orderColumn('jenis_kunjungan', 'kunjungan.event_id $1')
            ->filterColumn('nama', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('tamu.nama_tamu',      'like', "%{$keyword}%")
                        ->orWhere('civitas.nama_civitas', 'like', "%{$keyword}%");
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
            ->filterColumn('jam_kedatangan', fn($query, $keyword) =>
            $query->whereRaw("CONVERT(VARCHAR(8), kunjungan.created_at, 108) LIKE ?", ["%{$keyword}%"]))
            ->filterColumn('waktu_keluar', fn($query, $keyword) =>
            $query->whereRaw("CONVERT(VARCHAR(5), kunjungan.waktu_keluar, 108) LIKE ?", ["%{$keyword}%"]))
            ->filterColumn('waktu_checkout', function ($query, $keyword) {
                $lc = strtolower(trim($keyword));
                if (str_contains($lc, 'belum')) {
                    $query->where('kunjungan.is_checkout', false);
                } else {
                    $query->where('kunjungan.is_checkout', true)
                        ->whereRaw("CONVERT(VARCHAR(8), kunjungan.checkout_time, 108) LIKE ?", ["%{$keyword}%"]);
                }
            })
            ->toJson();
    }

    public function getStats(): JsonResponse
    {
        $stats = $this->getTodayStatistics();

        return response()->json([
            'success'                => true,
            'totalKunjunganHariIni'  => $stats['total'],
            'kunjunganSudahCheckout' => $stats['checkout'],
            'kunjunganBelumCheckout' => $stats['belum_checkout'],
        ]);
    }

    private function getTodayStatistics(): array
    {
        $today = Carbon::today();

        $baseQuery = Kunjungan::whereDate('kunjungan.created_at', $today)
            ->leftJoin('event', 'kunjungan.event_id', '=', 'event.event_id')
            ->where(function ($q) {
                $q->whereNull('kunjungan.event_id')
                    ->orWhere('event.kategori_lokasi', 'dalam_kampus');
            });

        return [
            'total' => (clone $baseQuery)->count(),
            'checkout' => (clone $baseQuery)->where('kunjungan.is_checkout', true)->count(),
            'belum_checkout' => (clone $baseQuery)->where('kunjungan.is_checkout', false)->count(),
        ];
    }
}
