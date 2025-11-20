<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Blade;

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

        $builder = app('datatables.html');
        $dataTable = $builder->serverSide(true)->ajax(route('app.user.data') . '/list')->columns([
            Column::make(['width' => '5%', 'title' => 'No', 'data' => 'no', 'orderable' => false, 'searchable' => false, 'className' => 'text-center']),
            Column::make(['title' => 'Nama', 'data' => 'name']),
            Column::make(['title' => 'Email', 'data' => 'email']),
            Column::make(['title' => 'Role', 'data' => 'role', 'orderable' => false, 'searchable' => false]),
            Column::make(['width' => '15%', 'title' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'className' => 'text-center']),
        ]);

        $this->dataView([
            'dataTable' => $dataTable,
            'roles' => $roles
        ]);

        return $this->view('admin.pengguna.list');
    }

    public function data(Request $req, $param1 = ''): JsonResponse
    {
        if ($param1 == 'list') {
            $filter = [];
            $data = DataTables::of(User::getDataDetail($filter, get: false))->toArray();

            $start = $req->input('start');
            $resp = [];
            foreach ($data['data'] as $key => $value) {
                $dt = [];

                $dt['no']       = ++$start;
                $dt['name']     = $value['name'] ?? '-';
                $dt['email']    = $value['email'] ?? '-';

                $user = User::find($value['id']);
                $userRoles = $user ? $user->roles()->pluck('name')->toArray() : [];
                $dt['role'] = !empty($userRoles) ? implode(', ', $userRoles) : 'No Role';

                $id = encid($value['id']);

                $dataAction = [
                    'id'  => $id,
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
        } else if ($param1 = 'detail') {
            validate_and_response([
                'id' => ['Paramater data', 'required'],
            ]);
            $currData = User::findOrFail(decid($req->input('id')));
            
            $userData = $currData->toArray();
            $userData['roles'] = $currData->roles()->whereIn('name', ['Admin', 'Eksekutif'])->pluck('name')->toArray();

            return response()->json([
                'status' => true,
                'message' => 'Data loaded',
                'data' => $userData
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
                'roles.*' => ['Role', 'required|in:Admin,Eksekutif'],
            ]);

            $data['name'] = clean_post('name');
            $data['email'] = clean_post('email');
            $data['password'] = bcrypt(uniqid());

            DB::beginTransaction();
            try {
                $inserted = User::create($data);
                
                $roles = $req->input('roles', []);
                if (!empty($roles)) {
                    $validRoles = array_intersect($roles, ['Admin', 'Eksekutif']);
                    if (!empty($validRoles)) {
                        $inserted->assignRole($validRoles);
                    }
                }
                
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Pengguna berhasil ditambah.',
                    'data' => ['id' => encid($inserted->id)]
                ]);
            } catch (\Throwable $th) {
                DB::rollback();
                abort(404, 'Tambah data gagal, ' . $th->getMessage());
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
                    'message' => 'Data berhasil dihapus'
                ]);
            } catch (\Throwable $th) {
                DB::rollback();
                abort(404, 'Hapus data gagal, ' . $th->getMessage());
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
                'roles.*' => ['Role', 'required|in:Admin,Eksekutif'],
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
                
                $validAdminRoles = array_intersect($newRoles, ['Admin', 'Eksekutif']);
                $allRoles = array_merge($baseRoles, $validAdminRoles);
                
                $currData->syncRoles($allRoles);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Update data berhasil.',
                    'data' => ['id' => $id]
                ]);
            } catch (\Throwable $th) {
                DB::rollback();
                abort(404, 'Update data gagal, ' . $th->getMessage());
            }
        } else {
            abort(404, 'Halaman tidak ditemukan');
        }
    }
}
