<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;
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
                Column::make(['width' => '10%', 'title' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'className' => 'text-center']),
                Column::make(['width' => '5%', 'title' => 'No', 'data' => 'no', 'orderable' => false, 'searchable' => false, 'className' => 'text-center']),
                Column::make(['title' => 'Waktu', 'data' => 'created_at', 'orderable' => true, 'className' => 'text-nowrap']),
                Column::make(['title' => 'User', 'data' => 'user', 'orderable' => true]),
                Column::make(['title' => 'Aktivitas', 'data' => 'description', 'orderable' => true]),
                Column::make(['title' => 'Subjek', 'data' => 'subject_type', 'orderable' => true]),
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
            $filterUser = $req->input('filter_user');
            $filterEvent = $req->input('filter_event');
            $filterSubject = $req->input('filter_subject');
            $filterDateFrom = $req->input('filter_date_from');
            $filterDateTo = $req->input('filter_date_to');

            $query = Activity::select([
                'sys_activity_log.id',
                'sys_activity_log.log_name',
                'sys_activity_log.description',
                'sys_activity_log.subject_type',
                'sys_activity_log.event',
                'sys_activity_log.subject_id',
                'sys_activity_log.causer_type',
                'sys_activity_log.causer_id',
                'sys_activity_log.properties',
                'sys_activity_log.batch_uuid',
                'sys_activity_log.created_at',
                'sys_activity_log.updated_at',
                'users.name as user_name'
            ])
                ->leftJoin('users', 'sys_activity_log.causer_id', '=', 'users.id')
                ->when(!empty($filterUser), fn($q) => $q->where('sys_activity_log.causer_id', $filterUser))
                ->when(!empty($filterEvent), fn($q) => $q->where(function ($sq) use ($filterEvent) {
                    $sq->where('sys_activity_log.event', $filterEvent)
                        ->orWhere('sys_activity_log.description', $filterEvent);
                }))
                ->when(!empty($filterSubject), fn($q) => $q->where('sys_activity_log.subject_type', $filterSubject))
                ->when(!empty($filterDateFrom), fn($q) => $q->whereDate('sys_activity_log.created_at', '>=', $filterDateFrom))
                ->when(!empty($filterDateTo), fn($q) => $q->whereDate('sys_activity_log.created_at', '<=', $filterDateTo));

            $start = (int) $req->input('start', 0);

            return DataTables::of($query)
                ->addColumn('no', function () use (&$start) {
                    return ++$start;
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at ? Carbon::parse($row->created_at)->timezone(config('app.timezone'))->format('d M Y H:i') : '-';
                })
                ->addColumn('user', function ($row) {
                    return $row->user_name ?? 'System';
                })
                ->addColumn('description', function ($row) {
                    return $row->description ?? '-';
                })
                ->addColumn('subject_type', function ($row) {
                    $subjectType = $row->subject_type ?? '-';
                    if ($subjectType !== '-') {
                        $parts = explode('\\', $subjectType);
                        return end($parts);
                    }
                    return '-';
                })
                ->addColumn('action', function ($row) {
                    $id = $row->id;
                    return '<button type="button" class="btn btn-sm btn-light-primary" data-cy="btn-action-detail-log-' . $id . '" onclick="viewDetail(' . $id . ')">
                        <i class="bi bi-eye"></i>
                    </button>';
                })
                ->rawColumns(['action'])
                ->orderColumn('created_at', 'sys_activity_log.created_at $1')
                ->orderColumn('user', 'users.name $1')
                ->orderColumn('description', 'sys_activity_log.description $1')
                ->orderColumn('subject_type', 'sys_activity_log.subject_type $1')
                ->filterColumn('created_at', fn($q, $keyword) => dtFilterByDateKeyword($q, $keyword, 'sys_activity_log.created_at'))
                ->filterColumn('user', fn($q, $keyword) => $q->where('users.name', 'like', "%{$keyword}%"))
                ->filterColumn('description', fn($q, $keyword) => $q->where('sys_activity_log.description', 'like', "%{$keyword}%"))
                ->filterColumn('subject_type', fn($q, $keyword) => $q->where('sys_activity_log.subject_type', 'like', "%{$keyword}%"))
                ->toJson();
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
