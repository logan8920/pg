<?php

namespace App\Http\Controllers\PgCompany;

use App\Models\{PgCompany, Mode};
use App\Http\Controllers\Controller;
use App\Models\PgDefaultConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\CommanTrait;
use DB;

class PgCompanyController extends Controller
{
    use CommanTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //dd(auth()->PgCompany()->api_partner);
        $datatableUrl = route("pg-company.show.ajax");
        return view('pg-company.list', compact('datatableUrl'));
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
        $post = $request->all();

        //dd($post);
        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string', 'max:255'],
                'keys.*' => [
                    'nullable',
                    'file',
                    function ($attribute, $value, $fail) {
                        $ext = strtolower($value->getClientOriginalExtension());
                        if (!in_array($ext, ['pem', 'crt', 'key', 'cer', 'der'])) {
                            $fail("The $attribute must be a file with one of the following extensions: pem, crt, key, cer, der.");
                        }
                    },
                ],
                "pg_config" => ['required'],
                "service_class_name" => ['required', 'regex:/^[A-Za-z0-9_]+$/']
            ],
            [
                'service_class_name.regex' => 'The Service Class Name may only contain letters, numbers, and underscores without spaces.',
            ]
        );

        if ($validator->fails()):
            return response()->json([
                'validationError' => $validator->errors()
            ], 200);
        endif;

        try {

            $pgComData = [];

            if ($request->hasFile('keys')) {
                $uPath = ucfirst(clearSpeicalCharacter(trim($request->name)));

                $file = $this->uploadFile('keys', $uPath, true);

                if (!$file['uploaded'])
                    throw new \Exception($file['message']);

                $pgComData['key_path'] = $file['paths'];
            }

            $pg_config = html_entity_decode($post['pg_config']);
            $pg_config = json_decode($pg_config, true);

            if (!$pg_config)
                throw new \Exception("PG Configuration is not a valid JSON");


            $pgComData['pg_config'] = $pg_config;
            $pgComData['service_class_name'] = $request->service_class_name;
            $pgComData['name'] = $request->name;
            $pgComData['status'] = $request->status;

            if (!empty($request->service_class_name)) {
                $pgComData['service_class_path'] = $this->createServiceFile($request->service_class_name);
            }

            PgCompany::create($pgComData);

            //api partner
            return response()->json([
                'success' => 'Company Created successFully :)',
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
            "service_class_name",
            "created_at",
            "updated_at"
        ];
        $order = $request->post('order');
        $start = $request->post('start') ?? 0;
        $length = $request->post('length') ?? 10;
        $search = $request->post('search')['value'] ?? null;

        $query = PgCompany::selectRaw('(@rownum := @rownum + 1) AS s_no, pg_companies.id, pg_companies.name, pg_companies.created_at,pg_companies.service_class_name,pg_companies.status,pg_companies.pg_config')
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
            $query->orderBy('pg_companies.id', 'desc');
        }

        // Count recordsFiltered before applying limit & offset
        $recordsFiltered = $query->count(DB::raw('1'));

        // Pagination
        $data = $query->offset($start)->limit($length)->get()->toArray();

        // Total records
        $recordsTotal = PgCompany::count();

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
    public function update(Request $request, PgCompany $pgCompany)
    {
        //dd($request->all());
        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string', 'max:255'],
                'keys.*' => [
                    'nullable',
                    'file',
                    function ($attribute, $value, $fail) {
                        $ext = strtolower($value->getClientOriginalExtension());
                        if (!in_array($ext, ['pem', 'crt', 'key', 'cer', 'der'])) {
                            $fail("The $attribute must be a file with one of the following extensions: pem, crt, key, cer, der.");
                        }
                    },
                ],
                "pg_config" => ['required']
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'validationError' => $validator->errors()
            ], 200);
        }

        try {

            $pgComData = [];

            if ($request->hasFile('keys')) {
                $uPath = ucfirst(clearSpeicalCharacter(trim($request->name)));
                $file = $this->uploadFile('keys', $uPath, true);
                if (!$file['uploaded'])
                    throw new \Exception($file['message']);

                $pgComData['key_path'] = $file['paths'];
            }


            $pg_config = html_entity_decode($request->pg_config);
            $pg_config = json_decode($pg_config, true);

            if (!$pg_config)
                throw new \Exception("PG Configuration is not a valid JSON");

            $pgComData['pg_config'] = $pg_config;
            //$pgComData['service_class_name'] = $request->service_class_name;
            $pgComData['name'] = $request->name;
            $pgComData['status'] = $request->status;

            $pgCompany->update($pgComData);

            //api partner
            return response()->json([
                'success' => 'Company Updated successFully :)',
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
    public function destroy(PgCompany $pgCompany)
    {
        try {

            //$keyData = json_decode($pgCompany->key_path, true);

            // if (is_array($keyData)) {
            //     foreach ($keyData as $keyFilePath) {
            //         if (Storage::disk('local')->exists($keyFilePath)) {
            //             Storage::disk('local')->delete($keyFilePath);
            //         }
            //     }
            // } elseif (is_string($pgCompany->key_path)) {
            //     if (Storage::disk('local')->exists($pgCompany->key_path)) {
            //         Storage::disk('local')->delete($pgCompany->key_path);
            //     }
            // }


            // if (!empty($pgCompany->service_path) && File::exists(base_path($pgCompany->service_path))) {
            //     File::delete(base_path($pgCompany->service_path));
            // }

            $pgCompany->delete();

            return response()->json([
                'success' => 'Pg Company and associated resources deleted successfully.',
                'tableReqload' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function defaultConfig(PgCompany $pgCompany)
    {
        if (auth()->user()->api_partner === 1) {
            abort(404);
        }

        $partnerModeCompanies = $pgCompany->defaultConfig()
            ->selectRaw('MIN(pg_company_id) as pg_company_id, MIN(c_per_day_limit) as c_per_day_limit')
            ->groupBy('pg_company_id')
            ->get()
            ->map(function ($q) use ($pgCompany) {
                $query = $pgCompany->defaultConfig()->select('charges', 'mode_id', "mode_limit")
                    //->where('user_id', $q->user_id)
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

        $apiPartnerModeCompanies = [];

        foreach ($partnerModeCompanies as $data) {
            $apiPartnerModeCompanies[$data['pg_company_id']] = $data;
        }
    
        $modes = Mode::get();
        return view('pg-company.default-config', compact( 'pgCompany', 'modes', 'apiPartnerModeCompanies'));
    }

    public function defaultConfigUpdate(PgCompany $pgCompany, Request $request)
    {
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
                        if(isset($mode['id'])) {
                            $postData[$counter]['c_per_day_limit'] = $company['c_per_day_limit'];
                            $postData[$counter]['mode_id'] = $mode['id'];
                            $postData[$counter]['mode_limit'] = $mode['limit'];
                            if (isset($mode['charges']) && !empty($mode['charges'])) {
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

            $pgCompany->defaultConfig()->delete();

            PgDefaultConfig::insert($postData);

            DB::commit();

            return response()->json([
                'success' => 'Default Api Config Update Successfully :)',
                'sweetAlert' => true,
                'redirect' => route('pg-company.list')
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage()
            ]);

        }
    }



}
