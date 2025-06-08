<?php

namespace App\Http\Controllers\UserManagement;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use DB;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $initial_permission = [['label' => 'Parent', 'value' => 0]];
        $parent_permissions = Permission::select('name','id')
            ->where('parent',0)
            ->get()
            ->map(function ($c) {
                return [
                    'label' => ucwords(str_replace('-',' ', $c->name)),
                    'value' => $c->id,
                ];
            })
            ->toArray();

        $parent_permissions = array_merge( $initial_permission, $parent_permissions);
        //dd($parent_permissions);
        $datatableUrl = route("permission.show.ajax");
        return view('user-management.permission.list', compact('datatableUrl', 'parent_permissions'));
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
                'name' => 'required|unique:'.Permission::class,
                'description' => 'nullable',
                'parent' => 'required|numeric'
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

            Permission::create($postData);
            $bool = $postData['parent'] === '0' ? true : false;
            return response()->json([
                'success' => 'Role Created successFully :)',
                'reloadReq' => $bool,
                'sweetAlert' => !$bool,
                "tableReqload" => !$bool
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 200);
        }
    }


    public function showAll(Request $request)
    {
        $dbColumns = ['name', 'description', 'created_at'];
        $order = $request->post('order');
        $start = $request->post('start') ?? 0;
        $length = $request->post('length') ?? 10;
        $search = $request->post('search')['value'] ?? null;

        $query = Permission::selectRaw('(@rownum := @rownum + 1) AS s_no, permissions.id, permissions.name, permissions.description, permissions.parent, permissions.created_at')
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
            $query->orderBy('permissions.id', 'desc');
        }

        // Count recordsFiltered before applying limit & offset
        $recordsFiltered = $query->count(DB::raw('1'));

        // Pagination
        $data = $query->offset($start)->limit($length)->get()->toArray();

        // Total records
        $recordsTotal = Permission::count();

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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
    public function update(Request $request, Permission $permission)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['required', Rule::unique(Permission::class)->ignore($permission->id)],
                'description' => 'nullable',
                'parent' => 'required|numeric'
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

            $permission->update($postData);
            $bool = $postData['parent'] === '0' ? true : false;
            return response()->json([
                'success' => 'Permission updated successFully :)',
                'reloadReq' => $bool,
                'sweetAlert' => !$bool,
                "tableReqload" => !$bool
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
    public function destroy(Permission $permission)
    {
        try {
            // Revoke all permissions from the role
            $roles = $permission->roles;
            foreach ($roles as $role) {
                $role->revokePermissionTo($permission);
            }

            // Finally delete the permission
            $permission->delete();

            return response()->json([
                'success' => 'Permission deleted successfully :)',
                'tableReqload' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }
}
