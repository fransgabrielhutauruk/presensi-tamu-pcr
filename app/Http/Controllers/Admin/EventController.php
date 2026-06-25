<?php

namespace App\Http\Controllers\Admin;

use App\Enums\KategoriTujuanEnum;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventKategori;
use App\Models\Kunjungan;
use App\Models\KunjunganDetail;
use App\Models\Tamu;
use App\Services\CypressTestingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Throwable;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Column;

class EventController extends Controller
{
    protected $dataKategori;
    private CypressTestingService $cypressTestingService;

    public function __construct(CypressTestingService $cypressTestingService)
    {
        $this->cypressTestingService = $cypressTestingService;
        $this->activeRoot = 'event';
        $this->breadCrump[] = ['title' => 'Event', 'link' => route('app.event.index')];
        $this->dataKategori = $this->buildKategoriSelect();
    }

    private function buildKategoriSelect(): array
    {
        return EventKategori::all()
            ->map(fn($row) => [
                'id' => encid($row->eventkategori_id),
                'text' => $row->nama_kategori,
            ])
            ->toArray();
    }

    private function buildEventData(Request $request): array
    {
        $tanggalRaw = clean_post('tanggal_event');
        $tanggalMulai = $tanggalRaw;
        $tanggalSelesai = null;

        if (str_contains($tanggalRaw, ' s/d ')) {
            $parts = explode(' s/d ', $tanggalRaw);
            $tanggalMulai = $parts[0];
            $tanggalSelesai = $parts[1] ?? null;
        }

        return [
            'eventkategori_id' => decid($request->post('eventkategori_id')),
            'kategori_lokasi' => clean_post('kategori_lokasi'),
            'jenis_kegiatan' => clean_post('jenis_kegiatan'),
            'nama_event' => clean_post('nama_event'),
            'deskripsi_event' => clean_post('deskripsi_event'),
            'tanggal_event' => $tanggalMulai,
            'tanggal_selesai_event' => $tanggalSelesai,
            'waktu_mulai_event' => clean_post('waktu_mulai_event'),
            'waktu_selesai_event' => clean_post('waktu_selesai_event'),
            'lokasi_event' => clean_post('lokasi_event'),
            'link_dokumentasi_event' => clean_post('link_dokumentasi_event'),
        ];
    }

    private function buildKategoriData(Request $request): array
    {
        return [
            'nama_kategori' => clean_post('nama_kategori'),
            'deskripsi_kategori' => clean_post('deskripsi_kategori'),
        ];
    }

    public function index(Request $request)
    {
        if ($this->cypressTestingService->isMockEnabled($request)) {
            $this->cypressTestingService->ensureEventKategoriExists();
            $this->dataKategori = $this->buildKategoriSelect();
        }

        $this->title = 'Kelola Event';
        $this->activeMenu = 'event';
        $builder = app('datatables.html');
        $dataTable = $builder->serverSide(true)
            ->ajax(route('app.event.data') . '/list')
            ->columns([
                Column::make(['title' => 'Aksi', 'data' => 'action', 'orderable' => false, 'className' => 'text-nowrap text-center']),
                Column::make(['title' => 'No', 'data' => 'no', 'orderable' => false, 'className' => 'text-center']),
                Column::make(['title' => 'Waktu Dibuat', 'data' => 'waktu_dibuat', 'orderable' => true]),
                Column::make(['title' => 'Nama Event', 'data' => 'nama_event', 'orderable' => true]),
                Column::make(['title' => 'Kategori', 'data' => 'nama_kategori', 'orderable' => true]),
                Column::make(['title' => 'Lokasi', 'data' => 'lokasi_event', 'orderable' => true]),
                Column::make(['title' => 'Tanggal Event', 'data' => 'tanggal_event', 'orderable' => true]),
                Column::make(['title' => 'Waktu Event', 'data' => 'waktu_event', 'orderable' => true]),
                Column::make(['title' => 'Status', 'data' => 'status', 'orderable' => false]),
                Column::make(['title' => 'Dokumentasi', 'data' => 'dokumentasi', 'orderable' => false, 'className' => 'text-center']),
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
            $rolesCanAccess = UserRole::getAdminEksekutifSecurityRoles();
            if (!in_array(getActiveRole(), $rolesCanAccess)) {
                abort(403, 'Anda  tidak memiliki akses ke halaman ini dengan role ' . getActiveRole());
            }

            $this->title = 'Kelola Kategori Event';
            $this->activeMenu = 'event-kategori';
            $this->breadCrump[] = ['title' => 'Kategori', 'link' => url()->current()];

            $builder = app('datatables.html');
            $dataTable = $builder->serverSide(true)->ajax(route('app.event.data') . '/kategori-list')->columns([
                Column::make(['title' => 'Aksi', 'data' => 'action', 'orderable' => false, 'className' => 'text-nowrap text-center']),
                Column::make(['title' => 'No', 'data' => 'no', 'orderable' => false, 'className' => 'text-center']),
                Column::make(['title' => 'Nama Kategori', 'data' => 'nama_kategori', 'orderable' => true]),
                Column::make(['title' => 'Deskripsi', 'data' => 'deskripsi_kategori', 'orderable' => true])
            ]);

            $this->dataView([
                'dataKategori' => $this->dataKategori,
                'dataTable' => $dataTable
            ]);

            return $this->view('admin.event.event-kategori');
        } else if ($param1 == 'validasi-kunjungan') {
            $eventId = decid($param2);
            $event = Event::findOrFail($eventId);

            $this->title = 'Validasi Kunjungan - ' . $event->nama_event;
            $this->activeMenu = 'event';
            $this->breadCrump[] = ['title' => 'Validasi Kunjungan', 'link' => url()->current()];

            $builder = app('datatables.html');
            $dataTable = $builder->serverSide(true)->ajax(route('app.event.data') . '/validasi-kunjungan-list/' . $param2)->columns([
                Column::make([
                    'title' => '<div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" id="checkAllValidasi" data-cy="checkbox-check-all-validasi-event"></div>',
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
                    'title' => 'No',
                    'data' => 'no',
                    'orderable' => false,
                    'className' => 'text-center'
                ]),
                Column::make([
                    'title' => 'Waktu Kunjungan',
                    'data' => 'waktu_kunjungan',
                    'orderable' => true,
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
                    'title' => 'Status',
                    'data' => 'status',
                    'orderable' => true,
                    'className' => 'text-center'
                ])
            ]);

            $this->dataView([
                'dataTable' => $dataTable,
                'event' => $event,
                'eventIdEnc' => $param2,
            ]);

            return $this->view('admin.event.validasi-kunjungan');
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
                'kategori_lokasi' => ['Kategori Lokasi', 'required|in:dalam_kampus,luar_kampus'],
                'jenis_kegiatan' => ['Jenis Kegiatan', 'required|in:pmb,non_pmb'],
                'tanggal_event' => ['Tanggal Event', 'required', 'string'],
                'waktu_mulai_event' => ['Waktu Mulai', 'required|date_format:H:i'],
                'waktu_selesai_event' => ['Waktu Selesai', 'required|date_format:H:i'],
                'lokasi_event' => ['Lokasi Event', 'required'],
                'link_dokumentasi_event' => ['Link Dokumentasi', 'nullable|url'],
            ]);

            try {
                $event = null;
                DB::transaction(function () use ($req, &$event) {
                    $event = Event::create($this->buildEventData($req));
                });
            } catch (Throwable $th) {
                abort(500, 'Tambah data gagal, kesalahan database');
            }

            return response()->json([
                'status' => true,
                'message' => 'Data event berhasil disimpan',
                'data' => [
                    'event_id' => isset($event) ? encid($event->event_id) : null,
                ],
            ]);
        } else if ($param1 == 'kategori') {
            validate_and_response([
                'nama_kategori' => ['Nama Kategori', 'required'],
                'deskripsi_kategori' => ['Deskripsi Kategori', 'nullable'],
            ]);

            try {
                DB::transaction(fn() => EventKategori::create($this->buildKategoriData($req)));
            } catch (Throwable $th) {
                abort(500, 'Tambah data gagal, kesalahan database');
            }

            return response()->json([
                'status' => true,
                'message' => 'Data kategori berhasil disimpan'
            ]);
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
                'kategori_lokasi' => ['Kategori Lokasi', 'required|in:dalam_kampus,luar_kampus'],
                'jenis_kegiatan' => ['Jenis Kegiatan', 'required|in:pmb,non_pmb'],
                'tanggal_event' => ['Tanggal Event', 'required', 'string'],
                'link_dokumentasi_event' => ['Link Dokumentasi', 'nullable|url'],
            ]);

            $event = Event::findOrFail(decid($req->input('id')));

            try {
                DB::transaction(fn() => $event->update($this->buildEventData($req)));
            } catch (Throwable $th) {
                abort(500, 'Gagal memperbarui data, kesalahan database');
            }

            return response()->json([
                'status' => true,
                'message' => 'Data event berhasil diperbarui'
            ]);
        } else if ($param1 == 'kategori') {
            validate_and_response([
                'id' => ['Parameter data', 'required'],
                'nama_kategori' => ['Nama Kategori', 'required'],
            ]);

            $kategori = EventKategori::findOrFail(decid($req->input('id')));

            try {
                DB::transaction(fn() => $kategori->update($this->buildKategoriData($req)));
            } catch (Throwable $th) {
                abort(500, 'Gagal memperbarui data, kesalahan database');
            }

            return response()->json([
                'status' => true,
                'message' => 'Data kategori berhasil diperbarui'
            ]);
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

            $event = Event::findOrFail(decid($req->input('id')));

            try {
                DB::transaction(fn() => $event->delete());
            } catch (Throwable $th) {
                abort(500, 'Gagal menghapus data, kesalahan database');
            }

            return response()->json(['status' => true, 'message' => 'Data event berhasil dihapus']);
        } else if ($param1 == 'kategori') {
            validate_and_response([
                'id' => ['Parameter data', 'required'],
            ]);

            $kategori = EventKategori::findOrFail(decid($req->input('id')));

            $eventCount = Event::where('eventkategori_id', $kategori->eventkategori_id)->count();
            if ($eventCount > 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Kategori tidak dapat dihapus karena masih digunakan oleh {$eventCount} event"
                ], 422);
            }

            try {
                DB::transaction(fn() => $kategori->delete());
            } catch (Throwable $th) {
                abort(500, 'Gagal menghapus data, kesalahan database');
            }

            return response()->json(['status' => true, 'message' => 'Data kategori berhasil dihapus']);
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }

    public function data(Request $req, $param1 = '', $param2 = ''): JsonResponse
    {
        if ($param1 == 'list') {
            $filterKategoriLokasi = $req->input('filter_kategori_lokasi', '');
            $filterStatus         = $req->input('filter_status', '');
            $filterKategori       = $req->input('filter_kategori', '');
            $filterDateFrom       = $req->input('filter_date_from', '');
            $filterDateTo         = $req->input('filter_date_to', '');

            $rolesCanViewAll = UserRole::getAdminEksekutifSecurityRoles();
            $activeRole = getActiveRole();
            $tz  = config('app.timezone', 'Asia/Jakarta');
            $now = Carbon::now()->setTimezone($tz);

            $query = Event::select([
                'event.event_id',
                'event.nama_event',
                'event.deskripsi_event',
                'event.tanggal_event',
                'event.tanggal_selesai_event',
                'event.waktu_mulai_event',
                'event.waktu_selesai_event',
                'event.lokasi_event',
                'event.kategori_lokasi',
                'event.jenis_kegiatan',
                'event.link_dokumentasi_event',
                'event.created_by',
                'event.created_at',
                'event.eventkategori_id',
                'event_kategori.nama_kategori',
            ])
                ->join('event_kategori', 'event.eventkategori_id', '=', 'event_kategori.eventkategori_id')
                ->when(!in_array($activeRole, $rolesCanViewAll), function ($q) {
                    $q->where('event.created_by', userId());
                })
                ->when(!empty($filterKategori), function ($q) use ($filterKategori) {
                    $q->where('event.eventkategori_id', decid($filterKategori));
                })
                ->when(!empty($filterKategoriLokasi), function ($q) use ($filterKategoriLokasi) {
                    $q->where('event.kategori_lokasi', $filterKategoriLokasi);
                })
                ->when(!empty($filterDateFrom), function ($q) use ($filterDateFrom) {
                    $q->whereRaw("COALESCE(event.tanggal_selesai_event, event.tanggal_event) >= ?", [$filterDateFrom]);
                })
                ->when(!empty($filterDateTo), function ($q) use ($filterDateTo) {
                    $q->where('event.tanggal_event', '<=', $filterDateTo);
                })
                ->when(!empty($filterStatus), function ($q) use ($filterStatus, $now) {
                    $nowStr = $now->format('Y-m-d H:i:s');
                    $driver = DB::connection()->getDriverName();

                    if ($driver === 'sqlsrv') {
                        match ($filterStatus) {
                            'mendatang' => $q->whereRaw(
                                "CAST(CONVERT(VARCHAR(10), event.tanggal_event, 120) + ' ' + COALESCE(CONVERT(VARCHAR(8), event.waktu_mulai_event, 108), '00:00:00') AS DATETIME) > ?",
                                [$nowStr]
                            ),
                            'berlangsung' => $q->whereRaw(
                                "? BETWEEN CAST(CONVERT(VARCHAR(10), event.tanggal_event, 120) + ' ' + COALESCE(CONVERT(VARCHAR(8), event.waktu_mulai_event, 108), '00:00:00') AS DATETIME) AND CAST(CONVERT(VARCHAR(10), COALESCE(event.tanggal_selesai_event, event.tanggal_event), 120) + ' ' + COALESCE(CONVERT(VARCHAR(8), event.waktu_selesai_event, 108), '23:59:59') AS DATETIME)",
                                [$nowStr]
                            ),
                            'selesai' => $q->whereRaw(
                                "CAST(CONVERT(VARCHAR(10), COALESCE(event.tanggal_selesai_event, event.tanggal_event), 120) + ' ' + COALESCE(CONVERT(VARCHAR(8), event.waktu_selesai_event, 108), '23:59:59') AS DATETIME) < ?",
                                [$nowStr]
                            ),
                            default => null,
                        };
                    } else {
                        match ($filterStatus) {
                            'mendatang' => $q->whereRaw(
                                "STR_TO_DATE(CONCAT(DATE_FORMAT(event.tanggal_event, '%Y-%m-%d'), ' ', COALESCE(TIME_FORMAT(event.waktu_mulai_event, '%H:%i:%s'), '00:00:00')), '%Y-%m-%d %H:%i:%s') > ?",
                                [$nowStr]
                            ),
                            'berlangsung' => $q->whereRaw(
                                "? BETWEEN STR_TO_DATE(CONCAT(DATE_FORMAT(event.tanggal_event, '%Y-%m-%d'), ' ', COALESCE(TIME_FORMAT(event.waktu_mulai_event, '%H:%i:%s'), '00:00:00')), '%Y-%m-%d %H:%i:%s') AND STR_TO_DATE(CONCAT(DATE_FORMAT(COALESCE(event.tanggal_selesai_event, event.tanggal_event), '%Y-%m-%d'), ' ', COALESCE(TIME_FORMAT(event.waktu_selesai_event, '%H:%i:%s'), '23:59:59')), '%Y-%m-%d %H:%i:%s')",
                                [$nowStr]
                            ),
                            'selesai' => $q->whereRaw(
                                "STR_TO_DATE(CONCAT(DATE_FORMAT(COALESCE(event.tanggal_selesai_event, event.tanggal_event), '%Y-%m-%d'), ' ', COALESCE(TIME_FORMAT(event.waktu_selesai_event, '%H:%i:%s'), '23:59:59')), '%Y-%m-%d %H:%i:%s') < ?",
                                [$nowStr]
                            ),
                            default => null,
                        };
                    }
                });

            $start = (int) $req->input('start', 0);

            return DataTables::of($query)
                ->addColumn('no', function () use (&$start) {
                    return ++$start;
                })
                ->addColumn('waktu_dibuat', function ($row) {
                    return $row->created_at ? tanggal($row->created_at) . ' ' . Carbon::parse($row->created_at)->format('H:i') : '-';
                })
                ->addColumn('nama_event', fn($row) => $row->nama_event ?? '-')
                ->addColumn('nama_kategori', fn($row) => $row->nama_kategori ?? '-')
                ->addColumn('kategori_lokasi', function ($row) {
                    return match ($row->kategori_lokasi) {
                        'dalam_kampus' => '<span class="badge badge-primary">Dalam Kampus</span>',
                        'luar_kampus'  => '<span class="badge badge-info">Luar Kampus</span>',
                        default        => '-',
                    };
                })
                ->addColumn('lokasi_event', function ($row) {
                    $lokasi = $row->lokasi_event ?? '-';
                    $badgeKategori = match ($row->kategori_lokasi) {
                        'dalam_kampus' => '<span class="badge badge-primary">Dalam Kampus</span>',
                        'luar_kampus'  => '<span class="badge badge-info">Luar Kampus</span>',
                        default        => '',
                    };
                    return $badgeKategori !== '' ? "{$lokasi} <br/> {$badgeKategori}" : $lokasi;
                })
                ->addColumn('tanggal_event', fn($row) => $row->formatted_date_range)
                ->addColumn('waktu_event', function ($row) {
                    $raw_mulai   = $row->getRawOriginal('waktu_mulai_event');
                    $raw_selesai = $row->getRawOriginal('waktu_selesai_event');
                    $mulai   = $raw_mulai   ? date('H:i', strtotime($raw_mulai))   : '-';
                    $selesai = $raw_selesai ? date('H:i', strtotime($raw_selesai)) : '-';
                    return "{$mulai} s/d {$selesai}";
                })
                ->addColumn('status', function ($row) use ($tz) {
                    try {
                        $tanggal = $row->getRawOriginal('tanggal_event');
                        $tanggal_selesai = $row->getRawOriginal('tanggal_selesai_event');
                        $mulai   = $row->getRawOriginal('waktu_mulai_event');
                        $selesai = $row->getRawOriginal('waktu_selesai_event');

                        if (empty($tanggal)) {
                            return '<span class="badge badge-light">-</span>';
                        }

                        $nowCarbon    = Carbon::now()->setTimezone($tz);
                        $eventStartAt = !empty($mulai)
                            ? Carbon::parse($tanggal . ' ' . $mulai)->setTimezone($tz)
                            : Carbon::parse($tanggal)->startOfDay()->setTimezone($tz);

                        $endDate = $tanggal_selesai ?? $tanggal;
                        $eventEndAt   = !empty($selesai)
                            ? Carbon::parse($endDate . ' ' . $selesai)->setTimezone($tz)
                            : Carbon::parse($endDate)->endOfDay()->setTimezone($tz);

                        if ($nowCarbon->lt($eventStartAt)) {
                            return '<span class="badge badge-warning">Mendatang</span>';
                        } elseif ($nowCarbon->between($eventStartAt, $eventEndAt)) {
                            return '<span class="badge badge-success">Berlangsung</span>';
                        } else {
                            return '<span class="badge badge-secondary">Selesai</span>';
                        }
                    } catch (\Throwable $e) {
                        return '<span class="badge badge-light">-</span>';
                    }
                })
                ->addColumn('dokumentasi', function ($row) {
                    $id = encid($row->event_id);
                    if (!empty($row->link_dokumentasi_event)) {
                        return '<a href="' . $row->link_dokumentasi_event . '" target="_blank" class="text-primary" data-cy="link-dokumentasi-event-' . $id . '">Lihat</a>';
                    }
                    return '<a href="javascript:;" class="text-warning" jf-edit="' . $id . '" data-cy="link-tambah-dokumentasi-event-' . $id . '">Tambah</a>';
                })
                ->addColumn('action', function ($row) {
                    $id         = encid($row->event_id);
                    $dataAction = [
                        'id'  => $id,
                        'btn' => [
                            ['action' => 'qrcode', 'title' => 'QR Code Presensi', 'link' => route('app.event.qr-code', $id)],
                            ['action' => 'check',  'title' => 'Validasi Kunjungan', 'link' => route('app.event.show', ['param1' => 'validasi-kunjungan', 'param2' => $id])],
                            ['action' => 'edit',   'attr'  => ['jf-edit' => $id]],
                            ['action' => 'delete', 'attr'  => ['jf-delete' => $id]],
                        ]
                    ];
                    return Blade::render('<x-btn.actiontable :id="$id" :btn="$btn"/>', $dataAction);
                })
                ->rawColumns(['lokasi_event', 'status', 'dokumentasi', 'action'])
                ->orderColumn('waktu_dibuat', 'created_at $1')
                ->orderColumn('nama_event',      'event.nama_event $1')
                ->orderColumn('nama_kategori',   'event_kategori.nama_kategori $1')
                ->orderColumn('lokasi_event',    'event.lokasi_event $1')
                ->orderColumn('tanggal_event',   'event.tanggal_event $1')
                ->orderColumn('waktu_event',     'event.waktu_mulai_event $1')
                ->filterColumn('nama_event', fn($query, $keyword) =>
                $query->where('event.nama_event', 'like', "%{$keyword}%"))
                ->filterColumn('lokasi_event', fn($query, $keyword) =>
                $query->where('event.lokasi_event', 'like', "%{$keyword}%"))
                ->filterColumn('tanggal_event', fn($query, $keyword) =>
                dtFilterByDateKeyword($query, $keyword, 'event.tanggal_event', 10))
                ->filterColumn('waktu_event', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $driver = DB::connection()->getDriverName();
                        if ($driver === 'sqlsrv') {
                            $q->whereRaw("CONVERT(VARCHAR(8), event.waktu_mulai_event, 108) LIKE ?",   ["%{$keyword}%"])
                                ->orWhereRaw("CONVERT(VARCHAR(8), event.waktu_selesai_event, 108) LIKE ?", ["%{$keyword}%"]);
                        } else {
                            $q->whereRaw("TIME_FORMAT(event.waktu_mulai_event, '%H:%i:%s') LIKE ?",   ["%{$keyword}%"])
                                ->orWhereRaw("TIME_FORMAT(event.waktu_selesai_event, '%H:%i:%s') LIKE ?", ["%{$keyword}%"]);
                        }
                    });
                })
                ->filterColumn('status', function ($query, $keyword) use ($now) {
                    $lc     = strtolower(trim($keyword));
                    $nowStr = $now->format('Y-m-d H:i:s');
                    $driver = DB::connection()->getDriverName();

                    if ($driver === 'sqlsrv') {
                        if (str_contains($lc, 'mendatang')) {
                            $query->whereRaw(
                                "CAST(CONVERT(VARCHAR(10), event.tanggal_event, 120) + ' ' + COALESCE(CONVERT(VARCHAR(8), event.waktu_mulai_event, 108), '00:00:00') AS DATETIME) > ?",
                                [$nowStr]
                            );
                        } elseif (str_contains($lc, 'berlangsung')) {
                            $query->whereRaw(
                                "? BETWEEN CAST(CONVERT(VARCHAR(10), event.tanggal_event, 120) + ' ' + COALESCE(CONVERT(VARCHAR(8), event.waktu_mulai_event, 108), '00:00:00') AS DATETIME) AND CAST(CONVERT(VARCHAR(10), COALESCE(event.tanggal_selesai_event, event.tanggal_event), 120) + ' ' + COALESCE(CONVERT(VARCHAR(8), event.waktu_selesai_event, 108), '23:59:59') AS DATETIME)",
                                [$nowStr]
                            );
                        } elseif (str_contains($lc, 'selesai')) {
                            $query->whereRaw(
                                "CAST(CONVERT(VARCHAR(10), COALESCE(event.tanggal_selesai_event, event.tanggal_event), 120) + ' ' + COALESCE(CONVERT(VARCHAR(8), event.waktu_selesai_event, 108), '23:59:59') AS DATETIME) < ?",
                                [$nowStr]
                            );
                        }
                    } else {
                        if (str_contains($lc, 'mendatang')) {
                            $query->whereRaw(
                                "STR_TO_DATE(CONCAT(DATE_FORMAT(event.tanggal_event, '%Y-%m-%d'), ' ', COALESCE(TIME_FORMAT(event.waktu_mulai_event, '%H:%i:%s'), '00:00:00')), '%Y-%m-%d %H:%i:%s') > ?",
                                [$nowStr]
                            );
                        } elseif (str_contains($lc, 'berlangsung')) {
                            $query->whereRaw(
                                "? BETWEEN STR_TO_DATE(CONCAT(DATE_FORMAT(event.tanggal_event, '%Y-%m-%d'), ' ', COALESCE(TIME_FORMAT(event.waktu_mulai_event, '%H:%i:%s'), '00:00:00')), '%Y-%m-%d %H:%i:%s') AND STR_TO_DATE(CONCAT(DATE_FORMAT(COALESCE(event.tanggal_selesai_event, event.tanggal_event), '%Y-%m-%d'), ' ', COALESCE(TIME_FORMAT(event.waktu_selesai_event, '%H:%i:%s'), '23:59:59')), '%Y-%m-%d %H:%i:%s')",
                                [$nowStr]
                            );
                        } elseif (str_contains($lc, 'selesai')) {
                            $query->whereRaw(
                                "STR_TO_DATE(CONCAT(DATE_FORMAT(COALESCE(event.tanggal_selesai_event, event.tanggal_event), '%Y-%m-%d'), ' ', COALESCE(TIME_FORMAT(event.waktu_selesai_event, '%H:%i:%s'), '23:59:59')), '%Y-%m-%d %H:%i:%s') < ?",
                                [$nowStr]
                            );
                        }
                    }
                })
                ->toJson();
        } else if ($param1 == 'detail') {
            validate_and_response([
                'id' => ['Parameter data', 'required'],
            ]);

            $currData = Event::findOrFail(decid($req->input('id')))->makeHidden(Event::$exceptEdit);

            $currData->id = $req->input('id');
            $currData->eventkategori_id = encid($currData->eventkategori_id);

            if ($currData->tanggal_selesai_event && $currData->tanggal_event !== $currData->tanggal_selesai_event) {
                $currData->tanggal_event = $currData->tanggal_event . ' s/d ' . $currData->tanggal_selesai_event;
            }

            return response()->json(['status' => true, 'message' => 'Data loaded', 'data' => $currData]);
        } else if ($param1 == 'kategori-detail') {
            validate_and_response([
                'id' => ['Parameter data', 'required'],
            ]);

            $currData = EventKategori::findOrFail(decid($req->input('id')))->makeHidden(EventKategori::$exceptEdit);

            $currData->id = $req->input('id');

            return response()->json(['status' => true, 'message' => 'Data loaded', 'data' => $currData]);
        } else if ($param1 == 'kategori-list') {
            $filter = [];

            $data = DataTables::of(EventKategori::where($filter))->toArray();

            $start = $req->input('start');
            $resp = [];
            foreach ($data['data'] as $key => $value) {
                $dt = [];
                $dt['no'] = ++$start;
                $dt['eventkategori_id'] = $value['eventkategori_id'] ?? '-';
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
        } else if ($param1 == 'validasi-kunjungan-list') {
            $eventId               = decid($param2);
            $filterJK              = $req->input('filter_jenis_kelamin', '');
            $filterStatusValidasi  = $req->input('filter_status_validasi', '');
            $filterIdentitas       = $req->input('filter_identitas', '');

            $query = Kunjungan::select([
                'kunjungan.kunjungan_id',
                'kunjungan.tamu_id',
                'kunjungan.civitas_id',
                'kunjungan.identitas',
                'kunjungan.is_vip',
                'kunjungan.status_validasi',
                'kunjungan.created_at',
                'tamu.nama_tamu',
                'tamu.jenis_kelamin_tamu',
                'tamu.email_tamu',
                'civitas.nama_civitas',
                'civitas.jenis_kelamin as jenis_kelamin_civitas',
                'civitas.email as email_civitas',
            ])
                ->leftJoin('tamu', function ($join) {
                    $join->on('kunjungan.tamu_id', '=', 'tamu.tamu_id')
                        ->whereNull('tamu.deleted_at');
                })
                ->leftJoin('civitas', function ($join) {
                    $join->on('kunjungan.civitas_id', '=', 'civitas.civitas_id')
                        ->whereNull('civitas.deleted_at');
                })
                ->where('kunjungan.event_id', $eventId)
                ->when(!empty($filterJK), function ($q) use ($filterJK) {
                    $q->where(function ($q) use ($filterJK) {
                        $q->where('tamu.jenis_kelamin_tamu', $filterJK)
                            ->orWhere('civitas.jenis_kelamin', $filterJK);
                    });
                })
                ->when(!empty($filterStatusValidasi), function ($q) use ($filterStatusValidasi) {
                    match ($filterStatusValidasi) {
                        'belum_validasi' => $q->where('kunjungan.status_validasi', false),
                        'tervalidasi'    => $q->where('kunjungan.status_validasi', true),
                        default          => null,
                    };
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

            $start = (int) $req->input('start', 0);

            return DataTables::of($query)
                ->addColumn('checkbox', function ($row) {
                    $id = encid($row->kunjungan_id);
                    if (!$row->status_validasi) {
                        return '<div class="form-check form-check-sm form-check-custom form-check-solid">
                            <input class="form-check-input row-checkbox" type="checkbox" value="' . $id . '" data-id="' . $id . '" data-cy="checkbox-row-validasi-event-' . $id . '">
                        </div>';
                    }
                    return '<div class="text-center">-</div>';
                })
                ->addColumn('no', function () use (&$start) {
                    return ++$start;
                })
                ->addColumn('nama', fn($row) => $row->nama_tamu ?? $row->nama_civitas ?? '-')
                ->addColumn('jenis_kelamin', fn($row) => $row->jenis_kelamin_tamu ?? $row->jenis_kelamin_civitas ?? '-')
                ->addColumn('email', fn($row) => $row->email_tamu ?? $row->email_civitas ?? '-')
                ->addColumn('nomor_telepon', function ($row) {
                    return $row->tamu->nomor_telepon_tamu ?? $row->civitas->nomor_telepon ?? '-';
                })
                ->addColumn('identitas', fn($row) => Kunjungan::getIdentitasBadge($row->identitas, $row->is_vip))
                ->addColumn('waktu_kunjungan', function ($row) {
                    return $row->created_at
                        ? tanggal($row->created_at) . ' ' . Carbon::parse($row->created_at)->setTimezone(config('app.timezone'))->format('H:i')
                        : '-';
                })
                ->addColumn('status', fn($row) => Kunjungan::getStatusValidasiBadge($row->status_validasi))
                ->addColumn('action', function ($row) {
                    $id         = encid($row->kunjungan_id);
                    $dataAction = [
                        'id'  => $id,
                        'btn' => [
                            ['action' => 'detail', 'attr' => ['jf-detail-modal' => 'kunjungan-event-validasi', 'jf-detail' => $id]],
                        ]
                    ];
                    return Blade::render('<x-btn.actiontable :id="$id" :btn="$btn"/>', $dataAction);
                })
                ->rawColumns(['checkbox', 'identitas', 'status', 'action'])
                ->orderColumn('identitas', 'kunjungan.identitas $1')
                ->orderColumn('nama', 'COALESCE(tamu.nama_tamu, civitas.nama_civitas) $1')
                ->orderColumn('jenis_kelamin', 'COALESCE(tamu.jenis_kelamin_tamu, civitas.jenis_kelamin) $1')
                ->orderColumn('email', 'COALESCE(tamu.email_tamu, civitas.email) $1')
                ->orderColumn('nomor_telepon', 'COALESCE(tamu.nomor_telepon_tamu, civitas.nomor_telepon) $1')
                ->orderColumn('waktu_kunjungan', 'kunjungan.created_at $1')
                ->orderColumn('status', 'kunjungan.status_validasi $1')
                ->filterColumn('nama', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('tamu.nama_tamu',      'like', "%{$keyword}%")
                            ->orWhere('civitas.nama_civitas', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('jenis_kelamin', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('tamu.jenis_kelamin_tamu', 'like', "%{$keyword}%")
                            ->orWhere('civitas.jenis_kelamin',  'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('email', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('tamu.email_tamu', 'like', "%{$keyword}%")
                            ->orWhere('civitas.email',      'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('nomor_telepon', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('tamu.nomor_telepon_tamu', 'like', "%{$keyword}%")
                            ->orWhere('civitas.nomor_telepon', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('identitas', fn($query, $keyword) =>
                $query->where('kunjungan.identitas', 'like', "%{$keyword}%"))
                ->filterColumn('waktu_kunjungan', fn($query, $keyword) =>
                dtFilterByDateKeyword($query, $keyword, 'kunjungan.created_at'))
                ->filterColumn('status', function ($query, $keyword) {
                    $lc = strtolower(trim($keyword));
                    if (str_contains($lc, 'belum')) {
                        $query->where('kunjungan.status_validasi', false);
                    } elseif (str_contains($lc, 'tervalid') || str_contains($lc, 'sudah')) {
                        $query->where('kunjungan.status_validasi', true);
                    }
                })
                ->toJson();
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }

    public function showQrCode(Request $request, $eventId)
    {
        $event = Event::with('eventKategori')->findOrFail(decid($eventId));
        $this->title = 'QR Code Event - ' . $event->nama_event;
        $this->activeMenu = 'event';
        $this->breadCrump[] = ['title' => 'QR Code', 'link' => url()->current()];

        $presensiUrl = route('tamu.event.identitas', $eventId);

        if ($request->get('generate') === 'true') {
            $qrCode = QrCode::size(300)
                ->backgroundColor(255, 255, 255)
                ->color(0, 0, 0)
                ->margin(2)
                ->generate($presensiUrl);

            return response($qrCode, 200, [
                'Content-Type' => 'image/svg+xml',
                'Content-Disposition' => 'inline; filename="qr-event-' . $event->event_id . '.svg"'
            ]);
        }

        $qrCodeSvg = QrCode::size(250)
            ->backgroundColor(255, 255, 255)
            ->color(0, 0, 0)
            ->margin(2)
            ->generate($presensiUrl);

        $this->dataView([
            'event' => $event,
            'presensiUrl' => $presensiUrl,
            'qrCodeSvg' => $qrCodeSvg
        ]);

        return $this->view('admin.event.qr-code');
    }

    public function storeVipGuest(Request $request): JsonResponse
    {
        validate_and_response([
            'event_id' => ['Event ID', 'required'],
            'nama' => ['Nama', 'required|string|max:255'],
            'jenis_kelamin' => ['Jenis Kelamin', 'required|in:Laki-laki,Perempuan'],
            'institusi' => ['Institusi', 'required|string|max:255'],
            'jabatan' => ['Jabatan', 'required|string|max:255'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                $eventId = decid($request->post('event_id'));
                $event = Event::findOrFail($eventId);

                $tamu = Tamu::create([
                    'nama_tamu' => clean_post('nama'),
                    'jenis_kelamin_tamu' => $request->post('jenis_kelamin'),
                ]);

                $kunjungan = Kunjungan::create([
                    'tamu_id' => $tamu->tamu_id,
                    'event_id' => $eventId,
                    'identitas' => 'non-civitas',
                    'kategori_tujuan' => KategoriTujuanEnum::EVENT->value,
                    'waktu_keluar' => $event->waktu_selesai_event,
                    'status_validasi' => true,
                    'is_vip' => true,
                ]);

                $this->storeVipGuestDetails($kunjungan->kunjungan_id, $request);
            });
        } catch (Throwable $th) {
            abort(500, 'Tambah data gagal, kesalahan database');
        }

        return response()->json([
            'status' => true,
            'message' => 'Tamu VIP berhasil ditambahkan'
        ]);
    }

    private function storeVipGuestDetails(int $kunjunganId, Request $request): void
    {
        $details = [
            ['kunci' => 'institusi', 'nilai' => clean_post('institusi'), 'urutan' => 1],
            ['kunci' => 'jabatan', 'nilai' => clean_post('jabatan'), 'urutan' => 2],
        ];

        foreach ($details as $detail) {
            KunjunganDetail::create([
                'kunjungan_id' => $kunjunganId,
                'kunci' => $detail['kunci'],
                'nilai' => $detail['nilai'],
                'urutan' => $detail['urutan'],
            ]);
        }
    }
}
