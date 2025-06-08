<?php

namespace App\Http\Controllers\UserManagement;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use DB;
class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $heading = 'Role List';
        $datatableUrl = route("role.show.ajax");
        return view('user-management.role.list', compact('heading', 'datatableUrl'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'description' => 'nullable'
            ]
        );

        if ($validator->fails()):
            return response()->json([
                'validationError' => $validator->errors()
            ], 200);
        endif;

        $postData = $request->input();
        $postData['guard_name'] = 'web';
        $postData['created_at'] = date('Y-m-d H:i:s');
        try {

            Role::create($postData);

            return response()->json([
                'success' => 'Role Created successFully :)',
                'reloadReq' => false,
                'sweetAlert' => true,
                "tableReqload" => true
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 200);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function showAll(Request $request)
    {
        $dbColumns = ['name', 'description', 'created_at'];
        $order = $request->post('order');
        $start = $request->post('start') ?? 0;
        $length = $request->post('length') ?? 10;
        $search = $request->post('search')['value'] ?? null;

        $query = Role::selectRaw('(@rownum := @rownum + 1) AS s_no, roles.id, roles.name, roles.description, roles.created_at')
            ->crossJoin(DB::raw('(SELECT @rownum := 0) r'));

        // Search filter
        if ($search) {
            $query->where(function ($q) use ($dbColumns, $search) {
                foreach ($dbColumns as $key => $column) {
                    if ($key === 0) {
                        $q->where($column, 'like', "%$search%");
                    } else {
                        $q->orWhere($column, 'like', "%$search%");
                    }
                }
            });
        }

        // Order
        if (isset($order[0]['dir'])) {
            $dir = $order[0]['dir'];
            $colIndex = $order[0]['column'] ?? false;
            $col = $dbColumns[$colIndex] ?? false;
            if ($col) {
                $query->orderBy($col, $dir);
            }
        } else {
            $query->orderBy('roles.id', 'desc');
        }

        // Count recordsFiltered before applying limit & offset
        $recordsFiltered = $query->count(DB::raw('1'));

        // Pagination
        $data = $query->offset($start)->limit($length)->get()->toArray();

        // Total records
        $recordsTotal = Role::count();

        // Prepare response
        $draw = $request->post('draw');

        $response = [
            'draw' => intval($draw),
            'recordsTotal' => intval($recordsTotal),
            'recordsFiltered' => intval($recordsFiltered),
            'data' => $data
        ];

        return response()->json($response);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'description' => 'nullable'
            ]
        );

        if ($validator->fails()):
            return response()->json([
                'validationError' => $validator->errors()
            ], 200);
        endif;

        $postData = $request->input();

        $postData['updated_at'] = date('Y-m-d H:i:s');
        try {

            $role->update($postData);

            return response()->json([
                'success' => 'Role updated successFully :)',
                'reloadReq' => false,
                'sweetAlert' => true,
                "tableReqload" => true
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        try {
            // Revoke all permissions from the role
            $permissions = $role->permissions;

            if ($permissions->isNotEmpty()) {
                $role->revokePermissionTo($permissions);
            }

            // Delete the role
            $role->delete();

            return response()->json([
                'success' => 'Role deleted successfully :)',
                'tableReqload' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function assginPermssion(Role $role)
    {
        $permissions = Permission::where('parent', 0)->get()->map(function ($parent) {
            $children = Permission::where('parent', $parent->id)->get()->toArray();
            return (object) [
                'id' => $parent->id,
                'name' => $parent->name,
                'children' => $children,
            ];
        });
        //dd( $permissions);
        $rolePermission = $role->permissions->toArray();
        $rolePermission = array_column($rolePermission, 'id');
        // dd(array_column($rolePermission,'name'));
        return view('user-management.role.assign-permission', compact('permissions', 'role', 'rolePermission'));
    }


    public function assginPermssionUpdate(Request $request, Role $role)
    {

        try {
            $permissions = $request->permission ?? [];
            $dbPermissions = Permission::whereIn('id', $permissions)->pluck('name')->toArray();
            ;
            $role->syncPermissions($dbPermissions);

            return response()->json([
                'success' => "Permission Assign to {$role->name} successFully :)",
                'reloadReq' => false,
                'sweetAlert' => true,
                'redirect' => route('role.list')
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 200);
        }
    }

}
