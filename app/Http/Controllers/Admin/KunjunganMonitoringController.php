<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kunjungan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Column;

class KunjunganMonitoringController extends Controller
{
    private const PARAM_MONITORING = 'monitoring-hari-ini';

    public function index()
    {
        $this->title = 'Monitoring Kunjungan Hari Ini';
        $this->activeMenu = 'monitoring-kunjungan';
        $this->breadCrump[] = ['title' => 'Monitoring Kunjungan', 'link' => url()->current()];

        $today = Carbon::today();
        $stats = $this->getTodayStatistics();

        $builder = app('datatables.html');
        $dataTable = $builder->serverSide(true)
            ->ajax(route('app.kunjungan.data') . '/' . self::PARAM_MONITORING)
            ->columns([
                Column::make([
                    'title' => 'No',
                    'data' => 'no',
                    'orderable' => false,
                    'className' => 'text-center'
                ]),
                Column::make([
                    'title' => 'Nama Tamu',
                    'data' => 'nama',
                    'orderable' => true
                ]),
                Column::make([
                    'title' => 'Identitas',
                    'data' => 'identitas',
                    'orderable' => true,
                ]),
                Column::make([
                    'title' => 'Jenis Kunjungan',
                    'data' => 'jenis_kunjungan',
                    'orderable' => true,
                ]),
                Column::make([
                    'title' => 'Waktu Kunjungan',
                    'data' => 'waktu_kunjungan',
                    'orderable' => true,
                ]),
                Column::make([
                    'title' => 'Waktu Keluar (Estimasi)',
                    'data' => 'waktu_keluar',
                    'orderable' => true,
                ]),
                Column::make([
                    'title' => 'Waktu Checkout',
                    'data' => 'waktu_checkout',
                    'orderable' => true,
                ]),
                Column::make([
                    'title' => 'Aksi',
                    'data' => 'action',
                    'orderable' => false,
                    'className' => 'text-nowrap text-center'
                ]),
            ]);

        $this->dataView([
            'dataTable' => $dataTable,
            'totalKunjunganHariIni' => $stats['total'],
            'kunjunganSudahCheckout' => $stats['checkout'],
            'tanggalHariIni' => tanggal($today->toDateString()),
        ]);

        return $this->view('admin.monitoring.list');
    }

    public function data(Request $req, $param1 = '', $param2 = ''): JsonResponse
    {
        if ($param1 !== '' && $param1 !== self::PARAM_MONITORING) {
            abort(404, 'Halaman tidak ditemukan');
        }

        $query = $this->getTodayKunjunganQuery();
        $data = DataTables::of($query)->toArray();

        $start = $req->input('start');
        $resp = [];
        foreach ($data['data'] as $key => $value) {
            $dt = [];
            $dt['no'] = ++$start;
            $dt['waktu_kunjungan'] = $value['created_at'] ? Carbon::parse($value['created_at'])
                ->setTimezone(config('app.timezone'))->format('H:i') : '-';
            $dt['nama'] = $value['tamu']['nama_tamu'] ?? $value['civitas']['nama_civitas'] ?? '-';

            $dt['identitas'] = Kunjungan::getIdentitasBadge($value['identitas'], $value['is_vip']);
            $dt['jenis_kunjungan'] = Kunjungan::getJenisKunjunganBadge($value['event_id']);
            $dt['waktu_keluar'] = $value['waktu_keluar'] ? Carbon::parse($value['waktu_keluar'])
                ->format('H:i') : '-';
            $dt['waktu_checkout'] = $value['is_checkout'] ? Carbon::parse($value['checkout_time'])
                ->setTimezone(config('app.timezone'))->format('H:i') : '<span class="badge badge-warning">Belum Checkout</span>';

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
    }

    public function getStats(): JsonResponse
    {
        $stats = $this->getTodayStatistics();

        return response()->json([
            'success' => true,
            'totalKunjunganHariIni' => $stats['total'],
            'kunjunganSudahCheckout' => $stats['checkout'],
        ]);
    }

    private function getTodayStatistics(): array
    {
        $today = Carbon::today();
        $totalQuery = Kunjungan::whereDate('created_at', $today);

        return [
            'total' => $totalQuery->count(),
            'checkout' => (clone $totalQuery)->where('is_checkout', true)->count(),
        ];
    }

    private function getTodayKunjunganQuery()
    {
        $today = Carbon::today();

        return Kunjungan::with(['tamu', 'civitas', 'details', 'event', 'event.eventKategori'])
            ->whereDate('created_at', $today)
            ->latest()
            ->get();
    }
}
