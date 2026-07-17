<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Column;

class UserController extends Controller
{
    public function __construct()
    {
        $this->activeRoot = 'manajemen_sistem';
        $this->breadCrump[] = ['title' => 'Manajemen Sistem', 'link' => url('#')];
    }

    public function index()
    {
        $this->title = 'Kelola Pengguna';
        $this->activeMenu = 'pengguna';
        $this->breadCrump[] = ['title' => 'Pengguna', 'link' => url()->current()];

        $roles = Role::whereIn('name', UserRole::getAdminEksekutifSecurityRoles())->get();
        $allRoles = Role::orderBy('name')->get();

        $builder = app('datatables.html');
        $dataTable = $builder->serverSide(true)->ajax(route('app.user.data').'/list')->columns([
            Column::make(['width' => '5%', 'title' => 'No', 'data' => 'no', 'orderable' => false, 'searchable' => false, 'className' => 'text-center']),
            Column::make(['width' => '15%', 'title' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'className' => 'text-center']),
            Column::make(['title' => 'Nama', 'data' => 'name']),
            Column::make(['title' => 'Email', 'data' => 'email']),
            Column::make(['title' => 'Role', 'data' => 'role', 'orderable' => true, 'searchable' => true]),
        ]);

        $this->dataView([
            'dataTable' => $dataTable,
            'roles' => $roles,
            'allRoles' => $allRoles,
        ]);

        return $this->view('admin.pengguna.list');
    }

    public function data(Request $req, $param1 = ''): JsonResponse
    {
        if ($param1 == 'list') {
            $filterRole = $req->input('filter_role', '');
            $query = User::with('roles')
                ->select('users.*')
                ->withMin('roles', 'name')
                ->when(! empty($filterRole), function ($q) use ($filterRole) {
                    $q->whereHas('roles', function ($sq) use ($filterRole) {
                        $sq->where('name', $filterRole);
                    });
                });

            $start = (int) $req->input('start', 0);

            return DataTables::of($query)
                ->addColumn('no', function () use (&$start) {
                    return ++$start;
                })
                ->addColumn('role', function ($row) {
                    $userRoles = $row->roles->pluck('name')->toArray();

                    return ! empty($userRoles) ? implode(', ', $userRoles) : 'No Role';
                })
                ->addColumn('action', function ($row) {
                    $id = encid($row->id);
                    $dataAction = [
                        'id' => $id,
                        'btn' => [
                            ['action' => 'edit', 'attr' => ['jf-edit' => $id]],
                            ['action' => 'delete', 'attr' => ['jf-delete' => $id]],
                        ],
                    ];

                    return Blade::render('<x-btn.actiontable :id="$id" :btn="$btn"/>', $dataAction);
                })
                ->rawColumns(['action'])
                ->orderColumn('name', 'users.name $1')
                ->orderColumn('email', 'users.email $1')
                ->orderColumn('role', 'roles_min_name $1')
                ->filterColumn('name', function ($query, $keyword) {
                    $query->where('users.name', 'like', "%{$keyword}%");
                })
                ->filterColumn('email', function ($query, $keyword) {
                    $query->where('users.email', 'like', "%{$keyword}%");
                })
                ->filterColumn('role', function ($query, $keyword) {
                    $query->whereHas('roles', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->toJson();
        } elseif ($param1 === 'detail') {
            validate_and_response([
                'id' => ['Parameter data', 'required'],
            ]);
            $currData = User::findOrFail(decid($req->input('id')));

            $userData = $currData->toArray();
            $userData['roles'] = $currData->roles()->whereIn('name', ['Admin', 'Eksekutif', 'Security'])->pluck('name')->toArray();

            return response()->json([
                'status' => true,
                'message' => 'Data loaded',
                'data' => $userData,
            ]);
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }

    public function store(Request $req, $param1 = ''): JsonResponse
    {
        if ($param1 == '') {
            validate_and_response([
                'name' => ['Nama', 'required'],
                'email' => ['Email', 'required|email|unique:users,email'],
                'roles' => ['Role', 'required|array'],
                'roles.*' => ['Role', 'required|in:Admin,Eksekutif,Security'],
            ]);

            $data['name'] = clean_post('name');
            $data['email'] = clean_post('email');
            $data['password'] = bcrypt(uniqid());

            DB::beginTransaction();
            try {
                $inserted = User::create($data);

                $roles = $req->input('roles', []);
                if (! empty($roles)) {
                    $validRoles = array_intersect($roles, ['Admin', 'Eksekutif', 'Security']);
                    if (! empty($validRoles)) {
                        $inserted->assignRole($validRoles);
                    }
                }

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Pengguna berhasil ditambah.',
                    'data' => ['id' => encid($inserted->id)],
                ]);
            } catch (\Throwable $th) {
                DB::rollback();
                abort(404, 'Tambah data gagal, '.$th->getMessage());
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

            $currData = User::findOrFail(decid($req->input('id')));

            DB::beginTransaction();
            try {
                $currData->delete();

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Data berhasil dihapus',
                ]);
            } catch (\Throwable $th) {
                DB::rollback();
                abort(404, 'Hapus data gagal, '.$th->getMessage());
            }
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }

    public function update(Request $req, $param1 = ''): JsonResponse
    {
        if ($param1 == '') {
            validate_and_response([
                'name' => ['Nama', 'required'],
                'email' => ['Email', 'required|email'],
                'roles' => ['Role', 'array|required'],
                'roles.*' => ['Role', 'required|in:Admin,Eksekutif,Security'],
            ]);

            $id = $req->input('id');
            $currData = User::findOrFail($id);

            $data['name'] = clean_post('name');
            $data['email'] = clean_post('email');
            $newRoles = $req->input('roles', []);

            DB::beginTransaction();
            try {
                $currData->update($data);

                $baseRoles = $currData->roles()->whereIn('name', ['Mahasiswa', 'Staf'])->pluck('name')->toArray();

                $validAdminRoles = array_intersect($newRoles, ['Admin', 'Eksekutif', 'Security']);
                $allRoles = array_merge($baseRoles, $validAdminRoles);

                $currData->syncRoles($allRoles);
                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Update data berhasil.',
                    'data' => ['id' => $id],
                ]);
            } catch (\Throwable $th) {
                DB::rollback();
                abort(404, 'Update data gagal, '.$th->getMessage());
            }
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }
}
