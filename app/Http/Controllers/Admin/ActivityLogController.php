<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Column;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function index()
    {
        $this->title = 'Log Aktivitas';
        $this->activeMenu = 'log-aktivitas';
        $this->breadCrump[] = ['title' => 'Log Aktivitas', 'link' => url()->current()];

        $builder = app('datatables.html');
        $dataTable = $builder->serverSide(true)
            ->ajax(route('app.log-aktivitas.data') . '/list')
            ->orderBy(1, 'desc')
            ->columns([
                Column::make(['width' => '5%', 'title' => 'No', 'data' => 'no', 'orderable' => false, 'searchable' => false, 'className' => 'text-center']),
                Column::make(['title' => 'Waktu', 'data' => 'created_at', 'orderable' => true, 'className' => 'text-nowrap']),
                Column::make(['title' => 'User', 'data' => 'user', 'orderable' => true]),
                Column::make(['title' => 'Aktivitas', 'data' => 'description', 'orderable' => true]),
                Column::make(['title' => 'Subjek', 'data' => 'subject_type', 'orderable' => true]),
                Column::make(['width' => '10%', 'title' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'className' => 'text-center']),
            ]);

        $this->dataView([
            'dataTable' => $dataTable
        ]);

        return $this->view('admin.log-aktivitas.list');
    }

    public function data(Request $req, $param1 = ''): JsonResponse
    {
        if ($param1 == 'list') {
            $query = Activity::with('causer');

            $data = DataTables::of($query)
                ->orderColumn('created_at', function ($query, $order) {
                    $query->orderBy('created_at', $order);
                })
                ->toArray();

            $start = $req->input('start');
            $resp = [];
            foreach ($data['data'] as $key => $value) {
                $dt = [];

                $dt['no'] = ++$start;
                $dt['created_at'] = Carbon::parse($value['created_at'])->format('d M Y H:i');
                $dt['user'] = $value['causer']['name'] ?? 'System';
                $dt['description'] = $value['description'] ?? '-';
                
                $subjectType = $value['subject_type'] ?? '-';
                if ($subjectType !== '-') {
                    $parts = explode('\\', $subjectType);
                    $dt['subject_type'] = end($parts);
                } else {
                    $dt['subject_type'] = '-';
                }

                $id = $value['id'];

                $dt['action'] = '<button type="button" class="btn btn-sm btn-light-primary" onclick="viewDetail(' . $id . ')">
                    <i class="bi bi-eye"></i> Detail
                </button>';

                $resp[] = $dt;
            }

            $data['data'] = $resp;

            return response()->json($data);
        } else if ($param1 == 'detail') {
            $id = $req->input('id');
            $log = Activity::with('causer', 'subject')->find($id);

            if (!$log) {
                return response()->json([
                    'status' => false,
                    'message' => 'Log tidak ditemukan'
                ], 404);
            }

            $data = [
                'id' => $log->id,
                'log_name' => $log->log_name,
                'description' => $log->description,
                'created_at' => Carbon::parse($log->created_at)->format('d M Y H:i:s'),
                'user' => $log->causer ? $log->causer->name : 'System',
                'user_email' => $log->causer ? $log->causer->email : '-',
                'subject_type' => $log->subject_type,
                'subject_id' => $log->subject_id,
                'properties' => $log->properties ?? []
            ];

            return response()->json([
                'status' => true,
                'message' => 'Data loaded',
                'data' => $data
            ]);
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }
}
