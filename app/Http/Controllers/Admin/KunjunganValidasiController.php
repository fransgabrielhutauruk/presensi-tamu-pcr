<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kunjungan;
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

        return $this->view('admin.validasi-kunjungan.list');
    }

    public function data(Request $req, $param1 = '', $param2 = ''): JsonResponse
    {
        if ($param1 !== '' && $param1 != 'validasi-list') {
            abort(404, 'Halaman tidak ditemukan');
        }

        $filter = ['status_validasi' => false];
        $query = Kunjungan::with(['tamu', 'civitas', 'details', 'event'])
            ->where($filter)
            ->latest()
            ->get();
        $data = DataTables::of($query)->toArray();

        $start = $req->input('start');
        $resp = [];
        foreach ($data['data'] as $key => $value) {
            $dt = [];

            $id = encid($value['kunjungan_id']);

            $dt['checkbox'] = '<div class="form-check form-check-sm form-check-custom form-check-solid">'
                . '<input class="form-check-input row-checkbox" type="checkbox" value="' . $id . '" data-id="' . $id . '">'
                . '</div>';

            $dt['no'] = ++$start;
            $dt['nama'] = $value['tamu']['nama_tamu'] ?? $value['civitas']['nama_civitas'] ?? '-';
            $dt['jenis_kelamin'] = $value['tamu']['jenis_kelamin_tamu'] ?? $value['civitas']['jenis_kelamin'] ?? '-';
            $dt['email'] = $value['tamu']['email_tamu'] ?? $value['civitas']['email'] ?? '-';
            $dt['nomor_telepon'] = $value['tamu']['nomor_telepon_tamu'] ?? $value['civitas']['nomor_telepon'] ?? '-';
            $dt['kategori_tujuan'] = $value['kategori_tujuan'] ?? '-';
            $dt['transportasi'] = $value['transportasi'] ?? '-';
            $dt['identitas'] = Kunjungan::getIdentitasBadge($value['identitas'], $value['is_vip']);
            $dt['jenis_kunjungan'] = Kunjungan::getJenisKunjunganBadge($value['event_id']);

            $dt['waktu_kunjungan'] = $value['created_at'] ? tanggal($value['created_at']) . ' ' .
                \Carbon\Carbon::parse($value['created_at'])->setTimezone(config('app.timezone'))->format('H:i') : '-';

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
