<?php

namespace App\Http\Controllers\UserManagement;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Role id : 2 refer to api partner
        $roles = Role::whereNotIn('id',[2])->get();
        $datatableUrl = route("user.show.ajax");
        return view('user-management.user.list', compact('datatableUrl', 'roles'));
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
        $allRole = Role::whereNotIn('id',[2])->get()->pluck('id')->toArray();
        $request->role = explode(',',$request->role);
        
        $validator = Validator::make(
            $request->all(),
            [
                'role.*' => ['required|numeric|in:'.implode(',',$allRole)],
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]
        );

        if ($validator->fails()):
            return response()->json([
                'validationError' => $validator->errors()
            ], 200);
        endif;

        try {

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $roles = Role::whereIn('id', $request->role)->get();

            $user->assignRole($roles);

            event(new Registered($user));

            return response()->json([
                'success' => 'User Created successFully :)',
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


    public function showAll(Request $request)
    {
        $dbColumns = ['name', 'description', 'created_at'];
        $order = $request->post('order');
        $start = $request->post('start') ?? 0;
        $length = $request->post('length') ?? 10;
        $search = $request->post('search')['value'] ?? null;

        $query = User::selectRaw('(@rownum := @rownum + 1) AS s_no, users.id, users.name, users.email, users.created_at')
            ->where('users.api_partner', 0)
            ->crossJoin(DB::raw('(SELECT @rownum := 0) r'))->with('roles:id,name');

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
            $query->orderBy('users.id', 'desc');
        }

        // Count recordsFiltered before applying limit & offset
        $recordsFiltered = $query->count(DB::raw('1'));

        // Pagination
        $data = $query->offset($start)->limit($length)->get()->toArray();

        // Total records
        $recordsTotal = User::count();

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
    public function update(Request $request, User $user)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => [
                    'required',
                    'string',
                    'lowercase',
                    'email',
                    'max:255',
                    Rule::unique(User::class)->ignore($user->id),
                ],
                'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'validationError' => $validator->errors()
            ], 200);
        }

        $postData = $request->except('role', 'password', 'password_confirmation');
        $postData['updated_at'] = now("Asia/Kolkata");

        if ($request->filled('password')) {
            $postData['password'] = bcrypt($request->password);
        }

        try {
            $user->update($postData);

            if ($request->filled('role')) {
                $newRole = trim($request->role);
                $currentRoles = $user->getRoleNames(); // Collection

                if (!$currentRoles->contains($newRole)) {
                    $user->syncRoles([$newRole]); // Clean replace
                }
            }

            return response()->json([
                'success' => 'User updated successfully :)',
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
    public function destroy(User $user)
    {
        try {
            // Revoke all roles from the user
            foreach ($user->roles as $role) {
                $user->removeRole($role);
            }

            // Finally delete the user
            $user->delete();

            return response()->json([
                'success' => 'User deleted successfully :)',
                'tableReqload' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

}
