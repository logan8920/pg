<?php

namespace App\Http\Controllers\ApiPartner;

use App\Models\PgCompany;
use App\Models\Pgtxn;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\ApiPartnerModeCompany;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use App\Models\Mode;
use DB;
use function PHPUnit\Framework\returnArgument;

class ApiPartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //dd(auth()->user()->api_partner);
        $datatableUrl = route("api-partner.show.ajax");
        return view('api-partner.list', compact('datatableUrl'));
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
                'name' => ['required', 'string', 'max:255'],
                'firmname' => ['required', 'string', 'max:255'],
                'business_name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:' . User::class],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
                'phone' => ['required', 'numeric', 'digits:10'],
                'status' => ['required', 'in:0,1']
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
                'password' => Hash::make('123456'),
                'firmname' => $request->firmname,
                'business_name' => $request->business_name,
                'username' => $request->username,
                'phone' => $request->phone,
                'status' => $request->status,
                'created_by' => auth()->user()->id,
                'api_partner' => 1,
                'datetime' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            //api partner
            $roles = Role::whereIn('name', ['Api Partner'])->get();

            $user->assignRole($roles);

            event(new Registered($user));

            return response()->json([
                'success' => 'Api Partner Created successFully :)',
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
        $dbColumns = [
            "name",
            "firmname",
            "business_name",
            "username",
            "email",
            "phone",
            "status",
            "created_at",
            "datetime"
        ];
        $order = $request->post('order');
        $start = $request->post('start') ?? 0;
        $length = $request->post('length') ?? 10;
        $search = $request->post('search')['value'] ?? null;

        $query = User::selectRaw('(@rownum := @rownum + 1) AS s_no, users.id, users.name, users.firmname, business_name, users.username, users.status, users.datetime, users.created_by, users.email, users.created_at, users.phone')
            ->crossJoin(DB::raw('(SELECT @rownum := 0) r'))
            ->where('api_partner', 1)->with(['createdBy', 'apiCredentials']);

        if (auth()->user()->api_partner) {
            $query->where('id', auth()->user()->id);
        }
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
        $postData['updated_at'] = now();
        unset($postData['username']);
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

            return response()->json(data: [
                'success' => 'User deleted successfully :)',
                'tableReqload' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }


    public function generateUsername()
    {
        try {
            $lastUserCount = User::where('api_partner', 1)->count();
            $username = 'RNFI' . str_pad(++$lastUserCount, 6, '0', STR_PAD_LEFT);

            while (User::where('username', $username)->exists()) {
                $username = 'RNFI' . str_pad(++$lastUserCount, 6, '0', STR_PAD_LEFT);
            }

            return response()->json([
                "success" => "Username Generated Successfully :)",
                "data" => ["username" => $username]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function setConfig(User $user)
    {
        if ($user->api_partner === 0) {
            abort(404);
        }

        $partnerModeCompanies = $user->apiConfig()
            ->selectRaw('MIN(user_id) as user_id, pg_company_id, MIN(c_per_day_limit) as c_per_day_limit')
            ->groupBy('pg_company_id')
            ->get()
            ->map(function ($q) use ($user) {
                $query = $user->apiConfig()->select('charges', 'mode_id', "mode_limit")
                    ->where('user_id', $q->user_id)
                    ->where('pg_company_id', $q->pg_company_id);


                $collection = $query->pluck('mode_limit', 'mode_id')->toArray();
                // dd($collection);
                $charges = $query->pluck('charges', 'mode_id')
                    ->toArray();


                $modes = [];
                foreach ($charges as $modeId => $charge) {
                    $modes[$modeId] = [
                        'charges' => $charge,
                        'mode_limit' => $collection[$modeId], // fixed this
                    ];
                }


                $q->config = $modes;
                return $q;
            })->toArray();

        //foreach()
        //dd($apiPartnerModeCompanies);
        $apiPartnerModeCompanies = [];

        foreach ($partnerModeCompanies as $data) {
            $apiPartnerModeCompanies[$data['pg_company_id']] = $data;
        }
        $pgCompanies = PgCompany::whereStatus(1)->get();
        $modes = Mode::get();
        return view('api-partner.set-config', compact('user', 'pgCompanies', 'modes', 'apiPartnerModeCompanies'));
    }

    public function ConfigUpdate(Request $request, User $user)
    {

        //dd($request->all());
        $validator = Validator::make(
            $request->all(),
            [
                'pg' => 'required|array'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'validationError' => $validator->errors()->first()
            ]);
        }

        try {

            $post = $request->pg;
            $postData = [];
            $counter = 0;
            foreach ($post as $company) {
                if (isset($company['id']) && isset($company['mode']) && !empty($company['mode'])) {
                    foreach ($company['mode'] as $mode) {
                        $postData[$counter]['c_per_day_limit'] = $company['c_per_day_limit'];
                        $postData[$counter]['mode_id'] = $mode['id'];
                        $postData[$counter]['mode_limit'] = $mode['limit'];
                        if (isset($mode['charges']) && !empty($mode['charges'])) {
                            $postData[$counter]['user_id'] = $user->id;
                            $postData[$counter]['pg_company_id'] = $company['id'];

                            $charges = [];
                            foreach ($mode['charges']['min'] as $i => $data) {
                                $charges[$i] = [];
                                $charges[$i]['min'] = $mode['charges']['min'][$i];
                                $charges[$i]['max'] = $mode['charges']['max'][$i];
                                $charges[$i]['charges_type'] = $mode['charges']['charges_type'][$i];
                                $charges[$i]['amt'] = $mode['charges']['amt'][$i];
                                $postData[$counter]['charges'] = json_encode($charges);
                            }
                            $counter++;
                        }
                    }
                }
            }
            //dd($postData);
            DB::beginTransaction();

            $user->apiConfig()->delete();

            ApiPartnerModeCompany::insert($postData);

            DB::commit();

            return response()->json([
                'success' => 'Api Config Successfully :)',
                'sweetAlert' => true,
                'redirect' => route('api-partner.list'),
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage()
            ]);

        }
    }

    public function transactionList()
    {

        $dbStartDate = Pgtxn::select('created_at')
            ->when(auth()->user()->api_partner, function ($query) {
                $query->where('id', auth()->user()->id);
            })
            ->first();

        $allTimeDate = $dbStartDate?->created_at ?
            date('Y-m-d', strtotime($dbStartDate?->created_at)) :
            '01-05-2025';


        $partnerIds = User::select('username')
            ->where("api_partner", 1)
            ->when(
                auth()->user()->api_partner,
                function ($query) {
                    $query->where('id', auth()->user()->id);
                }
            )
            ->get();


        $datatableUrl = route("api-partner.transactions.ajax");
        return view('api-partner.transaction', compact('datatableUrl', 'allTimeDate', 'partnerIds'));
    }

    public function transactionsAjax(Request $request)
    {
        $dbColumns = [
            'txnno',
            'name',
            'refid',
            'charge',
            'amt_after_deduction',
            'gst',
            'mobile',
            'status',
            'email',
            "mode_pg",
            'remarks',
            'dateadded',
            'users.username'
        ];
        $order = $request->post('order');
        $start = $request->post('start') ?? 0;
        $length = $request->post('length') ?? 10;
        $search = $request->post('search')['value'] ?? null;
        $status = $request->post('status',false);
        $partner_id = $request->post('partner_id',false);
        $transaction_no = $request->post('transaction_no',false);
        $reference_no = $request->post('reference_no', false);

        $startDate = $request->post('start_date') ?
            date('Y-m-d', strtotime($request->post('start_date'))) :
            date('Y-m-d');

        $endDate = $request->post('end_date') ?
            date('Y-m-d', strtotime($request->post('end_date'))) :
            date('Y-m-d');

        $query = Pgtxn::selectRaw('(@rownum := @rownum + 1) AS s_no, pgtxns.id, pgtxns.txnno, pgtxns.name, pgtxns.refid, pgtxns.charge, pgtxns.amt_after_deduction, pgtxns.gst, pgtxns.mobile, pgtxns.status, pgtxns.email, pgtxns.mode_pg, pgtxns.remarks, pgtxns.dateadded, pgtxns.errormsg, pgtxns.user_id')
            ->with('user:id,username')
            ->crossJoin(DB::raw('(SELECT @rownum := 0) r'));

        if (auth()->user()->api_partner) {
            $query->where('user_id', auth()->user()->id);
        }
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

        //status
        if ($status !== false && $status !== '') {
            $query->whereStatus($status);
        }

        //partner_id
        if ($partner_id) {
            $query->whereHas('user', function ($q) use ($partner_id) {
                $q->where('username', $partner_id);
            });
        }

        //reference_no id
        if ($reference_no) {
            $query->where('refid', 'like', "%$reference_no%");
        }

        //transaction_no id
        if ($transaction_no) {
            $query->where('txnno', 'like', "%$transaction_no%");
        }

        $query->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate);

        // Order
        if (isset($order[0]['dir'])) {
            $dir = $order[0]['dir'];
            $colIndex = $order[0]['column'] ?? false;
            $col = $dbColumns[$colIndex] ?? false;
            if ($col) {
                $query->orderBy($col, $dir);
            }
        } else {
            $query->orderBy('pgtxns.id', 'desc');
        }

        // Count recordsFiltered before applying limit & offset
        $recordsFiltered = $query->count(DB::raw('1'));

        // Pagination
        $data = $query->offset($start)->limit($length)->get()->toArray();

        // Total records
        $recordsTotal = Pgtxn::when(
            (int) auth()->user()->api_partner,
            function ($query) {
                return $query->where('user_id', auth()->id());
            }
        )->count();

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

}
