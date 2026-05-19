<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
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

        $users = User::select('id', 'name')->orderBy('name')->get();
        $subjects = Activity::select('subject_type')
            ->whereNotNull('subject_type')
            ->distinct()
            ->orderBy('subject_type')
            ->pluck('subject_type');

        $builder = app('datatables.html');
        $dataTable = $builder->serverSide(true)
            ->ajax(route('app.log-aktivitas.data') . '/list')
            ->columns([
                Column::make(['width' => '5%', 'title' => 'No', 'data' => 'no', 'orderable' => false, 'searchable' => false, 'className' => 'text-center']),
                Column::make(['title' => 'Waktu', 'data' => 'created_at', 'orderable' => true, 'className' => 'text-nowrap']),
                Column::make(['title' => 'User', 'data' => 'user', 'orderable' => true]),
                Column::make(['title' => 'Aktivitas', 'data' => 'description', 'orderable' => true]),
                Column::make(['title' => 'Subjek', 'data' => 'subject_type', 'orderable' => true]),
                Column::make(['width' => '10%', 'title' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'className' => 'text-center']),
            ]);

        $this->dataView([
            'dataTable' => $dataTable,
            'users' => $users,
            'subjects' => $subjects,
        ]);

        return $this->view('admin.log-aktivitas.list');
    }

    public function data(Request $req, $param1 = ''): JsonResponse
    {
        if ($param1 === 'list') {
            $query = Activity::with('causer');

            $filterUser = $req->input('filter_user');
            $filterEvent = $req->input('filter_event');
            $filterSubject = $req->input('filter_subject');
            $filterDateFrom = $req->input('filter_date_from');
            $filterDateTo = $req->input('filter_date_to');

            if (!empty($filterUser)) {
                $query->where('causer_id', $filterUser);
            }

            if (!empty($filterEvent)) {
                $query->where(function ($q) use ($filterEvent) {
                    $q->where('event', $filterEvent)
                        ->orWhere('description', $filterEvent);
                });
            }

            if (!empty($filterSubject)) {
                $query->where('subject_type', $filterSubject);
            }

            if (!empty($filterDateFrom)) {
                $query->whereDate('created_at', '>=', $filterDateFrom);
            }

            if (!empty($filterDateTo)) {
                $query->whereDate('created_at', '<=', $filterDateTo);
            }

            $data = DataTables::of($query->latest()->get())
                ->toArray();

            $start = (int) $req->input('start', 0);
            $resp = [];
            foreach ($data['data'] as $value) {
                $dt = [];

                $dt['no'] = ++$start;
                $dt['created_at'] = Carbon::parse($value['created_at'])
                    ->timezone('Asia/Jakarta')
                    ->format('d M Y H:i');
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
        } else if ($param1 === 'detail') {
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
