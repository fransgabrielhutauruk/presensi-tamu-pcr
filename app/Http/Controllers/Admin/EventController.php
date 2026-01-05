<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Tamu;
use App\Models\Event;
use App\Enums\UserRole;
use App\Models\Kunjungan;
use Illuminate\Http\Request;
use App\Models\EventKategori;
use App\Models\KunjunganDetail;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Blade;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
        $dataTable = $builder->serverSide(true)
            ->ajax(route('app.event.data') . '/list')
            ->columns([
                Column::make(['width' => '5%', 'title' => 'No', 'data' => 'no', 'orderable' => false, 'className' => 'text-center']),
                Column::make(['title' => 'Nama Event', 'data' => 'nama_event', 'orderable' => true]),
                Column::make(['title' => 'Kategori', 'data' => 'nama_kategori', 'orderable' => true]),
                Column::make(['title' => 'Lokasi', 'data' => 'lokasi_event', 'orderable' => true]),
                Column::make(['title' => 'Tanggal Event', 'data' => 'tanggal_event', 'orderable' => true, 'className' => 'text-center']),
                Column::make(['title' => 'Waktu Event', 'data' => 'waktu_event', 'orderable' => true, 'className' => 'text-center']),
                Column::make(['width' => '10%', 'title' => 'Dokumentasi', 'data' => 'dokumentasi', 'orderable' => false, 'className' => 'text-center']),
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
            $rolesCanAccess = UserRole::getAdminEksekutifSecurityRoles();
            if (!in_array(getActiveRole(), $rolesCanAccess)) {
                abort(403, 'Anda  tidak memiliki akses ke halaman ini dengan role ' . getActiveRole());
            }

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
        } else if ($param1 == 'validasi-kunjungan') {
            $eventId = decid($param2);
            $event = Event::findOrFail($eventId);

            $this->title = 'Validasi Kunjungan - ' . $event->nama_event;
            $this->activeMenu = 'event';
            $this->breadCrump[] = ['title' => 'Validasi Kunjungan', 'link' => url()->current()];

            $builder = app('datatables.html');
            $dataTable = $builder->serverSide(true)->ajax(route('app.event.data') . '/validasi-kunjungan-list/' . $param2)->columns([
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
                    'title' => 'Waktu Kunjungan',
                    'data' => 'waktu_kunjungan',
                    'orderable' => true,
                    'className' => 'text-center'
                ]),
                Column::make([
                    'width' => '10%',
                    'title' => 'Status',
                    'data' => 'status',
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
                'tanggal_event' => ['Tanggal Event', 'required|date'],
                'waktu_mulai_event' => ['Waktu Mulai', 'required|date_format:H:i'],
                'waktu_selesai_event' => ['Waktu Selesai', 'required|date_format:H:i'],
                'lokasi_event' => ['Lokasi Event', 'required'],
                'link_dokumentasi_event' => ['Link Dokumentasi', 'nullable|url'],
            ]);

            $data = [
                'eventkategori_id' => decid($req->post('eventkategori_id')),
                'nama_event' => clean_post('nama_event'),
                'deskripsi_event' => clean_post('deskripsi_event'),
                'tanggal_event' => clean_post('tanggal_event'),
                'waktu_mulai_event' => clean_post('waktu_mulai_event'),
                'waktu_selesai_event' => clean_post('waktu_selesai_event'),
                'lokasi_event' => clean_post('lokasi_event'),
                'link_dokumentasi_event' => clean_post('link_dokumentasi_event'),
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
                'link_dokumentasi_event' => ['Link Dokumentasi', 'nullable|url'],
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
                'link_dokumentasi_event' => clean_post('link_dokumentasi_event'),
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

            $rolesCanViewAll = UserRole::getAdminEksekutifSecurityRoles();
            $activeRole = getActiveRole();

            if (!in_array($activeRole, $rolesCanViewAll)) {
                $filter['a.created_by'] = userId();
            }

            $data = DataTables::of(Event::getDataDetail($filter, get: true))->toArray();

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

                $dt['tanggal_event'] = tanggal($value['tanggal_event']) ?? '-';
                $dt['waktu_event'] = ($value['waktu_mulai_event'] ?
                    date('H:i', strtotime($value['waktu_mulai_event'])) : '-') .
                    ' s/d ' .
                    ($value['waktu_selesai_event'] ? date('H:i', strtotime($value['waktu_selesai_event'])) : '-');

                if (!empty($value['link_dokumentasi_event'])) {
                    $dt['dokumentasi'] = '<a href="' . $value['link_dokumentasi_event'] . '" target="_blank" class="text-primary">Lihat</a>';
                } else {
                    $dt['dokumentasi'] = '<a href="javascript:;" class="text-warning" jf-edit="' . $id . '">Tambah</a>';
                }

                $dataAction = [
                    'id'  => $id,
                    'btn' => [
                        ['action' => 'qrcode', 'title' => 'QR Code Presensi', 'link' => route('app.event.qr-code', $id)],
                        ['action' => 'check', 'title' => 'Validasi Kunjungan', 'link' => route(
                            'app.event.show',
                            ['param1' => 'validasi-kunjungan', 'param2' => $id]
                        )],
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
            $eventId = decid($param2);
            $filter = ['event_id' => $eventId];

            $data = DataTables::of(Kunjungan::with(['tamu', 'details', 'event'])->where($filter))->toArray();

            $start = $req->input('start');
            $resp = [];
            foreach ($data['data'] as $key => $value) {
                $dt = [];
                $id = encid($value['kunjungan_id']);
                $statusValidasi = $value['status_validasi'] ?? false;

                if (!$statusValidasi) {
                    $dt['checkbox'] = '<div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input row-checkbox" type="checkbox" value="' . $id . '" data-id="' . $id . '">
                    </div>';
                } else {
                    $dt['checkbox'] = '<div class="text-center">-</div>';
                }

                $dt['no'] = ++$start;
                $dt['nama'] = $value['tamu']['nama_tamu'] ?? '-';
                $dt['jenis_kelamin'] = $value['tamu']['jenis_kelamin_tamu'] ?? '-';
                $dt['email'] = $value['tamu']['email_tamu'] ?? '-';
                $dt['nomor_telepon'] = $value['tamu']['nomor_telepon_tamu'] ?? '-';
                $dt['identitas'] = Kunjungan::getIdentitasBadge($value['identitas'], $value['is_vip']);

                $dt['waktu_kunjungan'] = $value['created_at'] ? tanggal($value['created_at']) . ' ' .
                    Carbon::parse($value['created_at'])->setTimezone(config('app.timezone'))->format('H:i') : '-';

                $dt['status'] = Kunjungan::getStatusValidasiBadge($statusValidasi);
                $actionButtons = [
                    ['action' => 'detail', 'attr' => ['jf-detail-modal' => 'kunjungan-event-validasi', 'jf-detail' => $id]]
                ];

                $dataAction = ['id' => $id, 'btn' => $actionButtons];

                $dt['action'] = Blade::render('<x-btn.actiontable :id="$id" :btn="$btn"/>', $dataAction);

                $resp[] = $dt;
            }

            $data['data'] = $resp;

            return response()->json($data);
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

        DB::beginTransaction();
        try {
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
                'waktu_keluar' => $event->waktu_selesai_event,
                'status_validasi' => true,
                'is_vip' => true,
            ]);

            KunjunganDetail::create([
                'kunjungan_id' => $kunjungan->kunjungan_id,
                'kunci' => 'institusi',
                'nilai' => clean_post('institusi'),
                'urutan' => 1,
            ]);

            KunjunganDetail::create([
                'kunjungan_id' => $kunjungan->kunjungan_id,
                'kunci' => 'jabatan',
                'nilai' => clean_post('jabatan'),
                'urutan' => 2,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Tamu VIP berhasil ditambahkan'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            abort(500, 'Tambah data gagal, kesalahan database');
        }
    }
}
