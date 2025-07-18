<?php

namespace App\Http\Controllers\ApiPartner;

use App\Models\{PaymentTransaction, PgDefaultConfig};
use App\Models\Query;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\{Hash, Validator};
use App\Models\{User, ApiPartnerModeCompany, Mode, PgCompany, Pgtxn};
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\{Rules, Rule};
use App\Traits\ApiResponseTrait;
use App\Libraries\PaymentBankit;
use DB;
use Carbon\Carbon;

class ApiPartnerController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //dd(auth()->user()->api_partner);
        $datatableUrl = route("api-partner.show.ajax");
        $pgCompanies = PgCompany::select('name as label','id as value')->get()->toArray();
        return view('api-partner.list', compact('datatableUrl','pgCompanies'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->payment_gateway = explode(',', $request->payment_gateway);
        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string', 'max:255'],
                'firmname' => ['required', 'string', 'max:255'],
                'business_name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:' . User::class],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
                'phone' => ['required', 'numeric', 'digits:10'],
                'status' => ['required', 'in:0,1'],
                'payment_gateway.*' => ['nullable', 'in:'.implode(',',PgCompany::get()->pluck('id')->toArray())]
            ]
        );

        if ($validator->fails()):
            return response()->json([
                'validationError' => $validator->errors()
            ], 200);
        endif;

        try {
            DB::beginTransaction();

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
            $roles = Role::whereIn('id', [2])->get();

            $user->assignRole($roles);
            $pgIds = $request->payment_gateway;
            $defaultConfig = PgDefaultConfig::whereIn('id',$pgIds)->toArray();

            foreach ($defaultConfig as &$config) {
                $config['charges'] = json_encode($config['charges']);
                $config['user_id'] = $user->id;
                $config['created_at'] = now("Asia/Kolkata");
            }

            ApiPartnerModeCompany::insert($defaultConfig);

            event(new Registered($user));

            DB::commit();

            return response()->json([
                'success' => 'Api Partner Created successFully :)',
                'reloadReq' => false,
                'sweetAlert' => true,
                "tableReqload" => true
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
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
            ->where('api_partner', 1)->with(['createdBy:id,name', 'apiCredentials:user_id,ipaddress','apiConfig:user_id,pg_company_id']);

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
                        if (isset($mode['id'])) {
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
            'pgtxns.name',
            'refid',
            'charge',
            'amt_after_deduction',
            'gst',
            'mobile',
            'pgtxns.status',
            'pgtxns.email',
            "mode_pg",
            'remarks',
            'dateadded',
            'users.username',
            'pgtxns.amount'
        ];
        $order = $request->post('order');
        $start = $request->post('start') ?? 0;
        $length = $request->post('length') ?? 10;
        $search = $request->post('search')['value'] ?? null;
        $status = $request->post('status', false);
        $partner_id = $request->post('partner_id', false);
        $transaction_no = $request->post('transaction_no', false);
        $reference_no = $request->post('reference_no', false);

        $startDate = $request->post('start_date') ?
            date('Y-m-d', strtotime($request->post('start_date'))) :
            date('Y-m-d');

        $endDate = $request->post('end_date') ?
            date('Y-m-d', strtotime($request->post('end_date'))) :
            date('Y-m-d');

        $query = Pgtxn::selectRaw('(@rownum := @rownum + 1) AS s_no, pgtxns.id, pgtxns.txnno, pgtxns.name, pgtxns.refid, pgtxns.charge, pgtxns.amt_after_deduction,pgtxns.amount, pgtxns.gst, pgtxns.mobile, pgtxns.status, pgtxns.email, pgtxns.mode_pg, pgtxns.remarks, pgtxns.dateadded, pgtxns.errormsg, pgtxns.user_id, pgtxns.txnid,  pgtxns.refund_remarks')
            ->with('user:id,username')
            ->join('users as users', 'users.id', '=', 'pgtxns.user_id')
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
            $query->where('pgtxns.status', $status);
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

        $query->where('pgtxns.addeddate', '>=', $startDate)
            ->where('pgtxns.addeddate', '<=', $endDate);

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

    public function initiateRefund(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'txnid' => [
                        'required',
                        'exists:payment_transactions,id',
                        function ($attribute, $value, $fail) {
                            $exists = Pgtxn::where('txnid', $value)
                                ->where('status', 3)
                                ->exists();
                            if (!$exists) {
                                $fail("Only Completed Transaction can be refunded.");
                            }
                        },
                        function ($attribute, $value, $fail) {
                            $txn = Pgtxn::where('txnid', $value)
                                ->where('status', 3)
                                ->first();

                            if (!$txn) {
                                $fail("Only Completed Transaction can be refunded...");
                            } elseif ($txn?->user?->balance < ((float) $txn?->amt_after_deduction)) {
                                $fail("Partner doesn't have sufficient balance in their wallet to process the refund.");
                            }
                        }
                    ],
                    'remark' => 'required|string'
                ]
            );

            if ($validator->fails())
                throw new \Exception($validator->errors()->first(), 0);

            $txnId = $request?->txnid;

            $txnData = Pgtxn::whereTxnid($txnId)->first();

            $data = [
                "refunded" => 1,
                "daterefunded" => now("Asia/Kolkata"),
                "processby" => auth()->id(),
                "refund_remarks" => $request->remark,
                "status" => 4
            ];

            $refundAmt = $txnData->amt_after_deduction;
            $balanceAfterRefund = ((float) $txnData->user?->balance) - ((float) $txnData->amt_after_deduction);

            $refundTxnData = [
                'txnno' => $txnData->user->unicode(),
                'order_id' => null,
                'refid' => $txnData->refid,
                'transfertype' => 'Debit',
                'remarks' => $request->remark,
                'comment' => $request->remark,
                'user_id' => $txnData->user_id,
                'amount' => $txnData?->amt_after_deduction,
                'previous_balance' => $txnData?->user?->balance,
                'balance' => round($balanceAfterRefund, 2),
                'dateupdated' => now("Asia/Kolkata"),
            ];

            $refund = PaymentBankit::tnxRefund(
                $data,
                $refundTxnData,
                $balanceAfterRefund
            );

            if (!$refund)
                throw new \Exception("Unable to process the refund right now. Please try again later.", 1);


            $response = [
                "tableReqload" => true,
                "sweetAlert" => true
            ];

            return $this->successResponse(
                $response,
                "Amount $refundAmt refunded successFully to {$txnData->user?->username}"
            );

        } catch (\Exception $e) {

            return $this->errorResponse(
                $e->getMessage(),
                is_numeric($e->getCode()) ? $e->getCode() : 0,
                422
            );

        } catch (\Throwable $th) {
            return $this->errorResponse(
                $th->getMessage(),
                is_numeric($th->getCode()) ? $th->getCode() : 0,
                422
            );
        }
    }

    public function dashboardTxnData(Request $request)
    {
        try {

            $validator = Validator::make(
                $request->all(),
                [
                    'token__' => 'required|string'
                ]
            );

            if ($validator->fails())
                throw new Exception($validator->errors()->first(), 0);

            $is_api_partner = auth()->user()->api_partner === 1;

            $data = Pgtxn::selectRaw(
                'SUM(amount) as totalTransaction,
                    SUM(CASE WHEN status IN (2, 3, 4) THEN amount ELSE 0 END) as totalSTransaction,
                    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN amount ELSE 0 END) as todayTransaction,
                    SUM(CASE WHEN DATE(created_at) = CURDATE() AND status IN (2, 3, 4) THEN amount ELSE 0 END) as todaySTransaction'
            )
                ->when($is_api_partner, function ($q) {
                    $q->whereUserId(auth()->id());
                })
                ->first();

            $transactionTillData = Pgtxn::select('status', DB::raw('SUM(amount) as total'))
                ->when($is_api_partner, function ($q) {
                    $q->whereUserId(auth()->id());
                })
                ->groupBy('status')
                ->pluck('total', 'status')
                ->map(fn($value) => (float) $value)
                ->toArray();

            $transactionTill = [];
            for ($i = 0; $i <= 4; $i++) {
                $transactionTill[] = isset($transactionTillData[$i]) ? (float) $transactionTillData[$i] : 0.0;
            }

            $monthlyTotals = Pgtxn::selectRaw('MONTH(created_at) as month, SUM(amount) as total')
                ->when($is_api_partner, function ($q) {
                    $q->where('user_id', auth()->id());
                })
                ->whereYear('created_at', Carbon::now()->year)
                ->groupBy(DB::raw('MONTH(created_at)'))
                ->pluck('total', 'month')
                ->toArray();

            // Step 2: Ensure float values for all months
            $yearlyData = [];
            for ($i = 1; $i <= 12; $i++) {
                $yearlyData[] = isset($monthlyTotals[$i]) ? (float) $monthlyTotals[$i] : 0.0;
            }


            $response = [
                'totalTransaction' => $data?->totalTransaction,
                'totalSTransaction' => $data?->totalSTransaction,
                'todaySTransaction' => $data?->todaySTransaction,
                'todayTransaction' => $data?->todayTransaction,
                'transactionTill' => $transactionTill,
                'yearlyData' => $yearlyData
            ];


            return $this->successResponse(
                $response,
                'Data Fetch Successfully'
            );

        } catch (Exception $e) {

            return $this->errorResponse(
                $e->getMessage(),
                $e->getCode(),
                422
            );

        } catch (\Throwable $th) {
            return $this->errorResponse(
                $th->getMessage(),
                $th->getCode(),
                422
            );
        }

    }

    public function getQuery(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'pgtxnid' => 'required|exists:pgtxns,id'
                ]
            );

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first(), 422);
            }

            $query = Query::select('pg_request', 'pg_response')->wherePgtxnId($request->pgtxnid)->first();

            if (!$query)
                throw new \Exception('No Query Found!', 422);

            return $this->successResponse(
                $query->toArray(),
                'Query Fetch Successfully!'
            );

        } catch (\Exception $e) {

            return $this->errorResponse(
                $e->getMessage()
            );

        } catch (\Throwable $th) {

            return $this->errorResponse(
                $th->getMessage()
            );

        }




    }

    public function ledger()
    {
        $dbStartDate = PaymentTransaction::select('created_at')
            ->when(auth()->user()->api_partner, function ($query) {
                $query->where('user_id', auth()->user()->id);
            })
            ->first();

        $allTimeDate = $dbStartDate?->created_at ?
            date('Y-m-d', strtotime($dbStartDate?->created_at)) :
            '01-05-2025';

        $startDate = date('d-m-Y', strtotime('-1 month'));
        $endDate = date('d-m-Y');
        $partnerIds = User::select('username')
            ->where("api_partner", 1)
            ->when(
                auth()->user()->api_partner,
                function ($query) {
                    $query->where('id', auth()->user()->id);
                }
            )
            ->get();


        $datatableUrl = route("api-partner.ledger.ajax");
        return view('api-partner.ledger', compact('datatableUrl', 'startDate', 'endDate', 'partnerIds', 'allTimeDate'));
    }


    public function ledgerAjax(Request $request)
    {

        $dbColumns = [
            'payment_transactions.txnno',
            'payment_transactions.remarks',
            'payment_transactions.refid',
            'payment_transactions.charge',
            'payment_transactions.amt_after_deduction',
            'payment_transactions.amount',
            'payment_transactions.balance',
            'payment_transactions.gst',
            'payment_transactions.created_at'
        ];
        $order = $request->post('order');
        $start = $request->post('start') ?? 0;
        $length = $request->post('length') ?? 10;
        $search = $request->post('search')['value'] ?? null;
        //$status = $request->post('status', false);
        $partner_id = $request->post('partner_id', false);
        $transaction_no = $request->post('transaction_no', false);
        $reference_no = $request->post('reference_no', false);

        $startDate = $request->post('start_date') ?
            date('Y-m-d', strtotime($request->post('start_date'))) :
            date('Y-m-d', strtotime('-1 month'));

        $endDate = $request->post('end_date') ?
            date('Y-m-d', strtotime($request->post('end_date'))) :
            date('Y-m-d');

        $query = PaymentTransaction::selectRaw('(@rownum := @rownum + 1) AS s_no, payment_transactions.id, payment_transactions.txnno, IF(transfertype = "Debit", payment_transactions.amount,NULL) AS debit, IF(transfertype = "Credit", payment_transactions.amt_after_deduction,NULL) AS credit, payment_transactions.refid, payment_transactions.charge, payment_transactions.balance, payment_transactions.gst, payment_transactions.remarks, payment_transactions.amount, payment_transactions.created_at, payment_transactions.transfertype')
            ->with("refundDetail:refundtxnid,txnno")
            ->join('users as users', 'users.id', '=', 'payment_transactions.user_id')
            ->crossJoin(DB::raw('(SELECT @rownum := 0) r'));

        if (auth()->user()->api_partner) {
            $query->where('payment_transactions.user_id', auth()->user()->id);
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

        //partner_id
        if ($partner_id) {
            $query->whereHas('user', function ($q) use ($partner_id) {
                $q->where('users.username', $partner_id);
            });
        }

        //reference_no id
        if ($reference_no) {
            $query->where('payment_transactions.refid', 'like', "%$reference_no%");
        }

        //transaction_no id
        if ($transaction_no) {
            $query->where('payment_transactions.txnno', 'like', "%$transaction_no%");
        }

        $query->where('payment_transactions.created_at', '>=', $startDate . " 00:00:00")
            ->where('payment_transactions.created_at', '<=', $endDate . " 23:59:59");

        // Order
        if (isset($order[0]['dir'])) {
            $dir = $order[0]['dir'];
            $colIndex = $order[0]['column'] ?? false;
            $col = $dbColumns[$colIndex] ?? false;
            if ($col) {
                $query->orderBy($col, $dir);
            }
        } else {
            $query->orderBy('payment_transactions.id', 'desc');
        }

        // Count recordsFiltered before applying limit & offset
        $recordsFiltered = $query->count(DB::raw('1'));

        // Pagination
        $data = $query->offset($start)->limit($length)->get()->toArray();

        // Total records
        $recordsTotal = PaymentTransaction::when(
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

    public function ApiPartnerPgCredentails(PgCompany $pgCompany, User $user, Request $request)
    {
        try {
            $pg_config = $pgCompany->pg_config;

            if (count($pg_config ?? [])) {

                $pgValiation = [];

                foreach ($pg_config as $key => $config) {
                    $pgValiation[$key] = 'required|string';
                }

                $validation = Validator::make(
                    $request->all(),
                    $pgValiation
                );

                if ($validation->fails())
                    throw new Exception($validation->errors()->first(), 422);

            }

            $user->apiPgCredentials()->sync([
                $pgCompany->id => [
                    "pg_credentials" => UserService::encrypt(json_encode($request->all())),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            ],false);

            return response()->json([
                'success' => "PG {$pgCompany->name} for Parten {$user->username} added successfully!",
                'sweetAlert' => true
            ]);

        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

}
