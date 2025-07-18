<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use App\Models\Pgtxn;
use App\Models\LogRequest;
use App\Services\JwtService;
use Illuminate\Support\Facades\Validator;
use App\Services\UserService;
use App\Models\{PgCompany, User, Mode, ApiPartnerModeCompany};
use App\Traits\{ApiResponseTrait};
use App\Libraries\PaymentBankit;
use Illuminate\Support\Facades\{Log};
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DB;

class PgtxnController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */

    private $status = ["Failed", "Success", "Initiated", "Complete", "Refund"];

    public function index(Request $request, JwtService $jwtService)
    {
        $info = [];

        try {
            $partner_info = $request->apiCredentials();

            if (!$partner_info)
                return $this->errorResponse('Authentication Failed', 0);

            $this->is_valid($partner_info);


            if (!in_array(1, [$partner_info->pg]))
                return $this->errorResponse('PG service is disabled.', 1, 404);


            $token = $request->header('Token');

            if (!$token)
                return $this->errorResponse('Invalid Jwt Token.', 2, 401);

            $body = json_encode($request->all());
            $method = 'GENERATE-URL';

            $info = $this->decodeToken(
                $token,
                $partner_info->key,
                $partner_info->user->username,
                $body,
                $method,
                $jwtService
            );

            if (!$info['status'])
                return $this->errorResponse($info['message'] ?? 'Unable to process request', $info['code'] ?? 25);

            if (!($info['data']['partnerId'] === $partner_info->user?->username)) {
                return $this->errorResponse("Partner ID Doesn't Match!", 10);
            }

            $allModes = Mode::get()->pluck('name')->toArray();

            $validator = Validator::make($request->all(), [
                'mode' => 'required|in:' . implode(',', $allModes),
                'amount' => [
                    'required',
                    'numeric',
                    'min:100',
                    'max:1000000',
                    function ($attribute, $value, $fail) {
                        if (!(is_numeric($value) && floor($value) == $value)) {
                            $fail("The $attribute must be a decimal (float) with decimal places.");
                        }
                    }
                ],
                'mobile' => 'required|regex:/^[6-9][0-9]{9}$/',
                'email' => 'required|email',
                'refid' => 'required|string|max:16|unique:pgtxns,refid',
                'redirect_url' => 'required|url',
                'card' => 'nullable|required_unless:mode,NB|numeric|digits:4',
                'name' => 'nullable|required_unless:mode,NB|string',
            ]);

            if ($validator->fails())
                throw new Exception($validator->errors()->first(), 11);


            $duplicate = Pgtxn::where([
                'refid' => $request->refid,
                'user_id' => $partner_info['user_id']
            ])->first();

            if ($duplicate)
                throw new Exception('Duplicate Ref number', 4);

            $mode = Mode::whereName(trim($request->mode))->first();

            if (!$mode)
                throw new Exception('Unable to get transaction mode', 1);


            $txns = [
                'user_id' => $partner_info->user_id,
                'amount' => $request->amount,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'mode_pg' => $request->mode,
                'card' => $request->card,
                'name' => $request->name,
                'refid' => $request->refid,
                'redirect_url' => $request->redirect_url,
                'status' => 2,
                'api' => 'Generate-url',
                'ipaddress' => $request->ip(),
                'mode_id' => $mode->id,
            ];

            $transaction = (new Pgtxn)->initiate($txns);

            if ($transaction['status'] === false)
                throw new Exception($transaction['message'], 9);


            $txns['txnno'] = $transaction['txnno'];
            unset($txns['status']);
            unset($txns['api']);
            unset($txns['ipaddress']);
            $encdata = UserService::encrypt($txns);

            $transaction['modal']->update([
                'encdata' => $encdata,
                'addeddate' => now("Asia/Kolkata")->format('Y-m-d'),
                'dateadded' => now("Asia/Kolkata")
            ]);

            $transaction['modal']->tQuery()->create([
                'client_request' => [
                    'headers' => $request->headers->all(),
                    'body' => $request->getContent(),
                    'payload' => $request->all(),
                ]
            ]);

            return $this->successResponse(
                [
                    'url' => route('pg.redirect'),
                    'encdata' => $encdata ?? ''
                ],
                'Data Successfully Generated',
                logInsertedId: $info['log_insert_id'] ?? 0
            );
        } catch (\Throwable $e) {
            return $this->errorResponse(
                $e->getMessage(),
                $e->getCode(),
                500,
                logInsertedId: $info['log_insert_id'] ?? 0
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                $e->getCode(),
                500,
                logInsertedId: $info['log_insert_id'] ?? 0
            );
        }
    }

    public function initiateTransaction(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'encdata' => "required|string"
            ]
        );

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 11);
            //return redirect()->route('pg-transaction.failure');
        }
        $decrptdata = [];
        try {
            $encData = $request->encdata;
            $decrptdata = json_decode(UserService::decrypt($encData), true);
            //dd($decrptdata);
            $validator = Validator::make(
                $decrptdata,
                [
                    'txnno' => [
                        'required',
                        'string',
                        Rule::unique('pgtxns')->where(function ($query) {
                            return $query->where('status', '!=', 2);
                        }),
                    ],
                    'user_id' => 'required|exists:users,id',
                    'amount' => 'required|numeric',
                    'mobile' => 'required|digits:10',
                    'email' => 'required|email',
                    'mode_pg' => 'required|exists:modes,name', // adjust if using ID
                    'card' => 'nullable|required_unless:mode,NB|numeric|digits:4',
                    'name' => 'nullable|required_unless:mode,NB|string',
                    'refid' => [
                        'required',
                        'string',
                        'max:100',
                        Rule::unique('pgtxns', 'refid')->where(function ($query) {
                            return $query->where('status', '!=', 2);
                        }),
                    ],
                    'redirect_url' => 'required|url',
                ],
                [
                    'refid.unique' => 'Invalid Request!',
                ]
            );

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first(), 11);
                //return redirect()->route(route: 'pg-transaction.failure');
            }

            $mode = Mode::whereId($decrptdata['mode_id'])->first();
            $paymentGateway = PaymentBankit::getAvailableGateway(
                $decrptdata['amount'],
                $decrptdata['user_id'],
                $mode->id
            )?->company;

            if (!$paymentGateway)
                throw new Exception("Payment Gateway Not Found or Limit Excusted", 13);

            $path = (string) "App\Services\\" . $paymentGateway->service_class_name;

            if (!class_exists($path))
                throw new Exception("{$paymentGateway->name} Service Class Not Found!", 14);

            $paymentService = new $path();

            $data = $paymentService->init($decrptdata);

            return view('payment-gateway.pgredirect', $data);

        } catch (Exception $e) {
            if ($decrptdata['txnno']) {
                $txn = Pgtxn::where('txnno', $decrptdata['txnno'])->first();
                if ($txn) {
                    $txn->update([
                        'status' => 0,
                        'errormsg' => $e->getMessage()
                    ]);
                }
            }
            $decrptdata["message"] = $e->getMessage();
            $resData = base64_encode(UserService::encrypt(http_build_query($decrptdata)));
            $url = uri("/gateway-pg-receipt?resdata={$resData}");
            return redirect($url);
        } catch (\Throwable $e) {

            if ($decrptdata['txnno']) {
                $txn = Pgtxn::where('txnno', $decrptdata['txnno'])->first();
                if ($txn) {
                    $txn->update([
                        'status' => 0,
                        'errormsg' => $e->getMessage()
                    ]);
                }
            }
            $decrptdata["message"] = $e->getMessage();
            $resData = base64_encode(UserService::encrypt(http_build_query($decrptdata)));
            $url = uri("/gateway-pg-receipt?resdata={$resData}");
            return redirect($url);
        }
    }

    public function handlePgCallback($gateway)
    {
        $gateway = trim($gateway);

        try {

            $pgCompany = PgCompany::whereName($gateway)->first();

            if (!$pgCompany)
                throw new Exception("Payment Gateway {$gateway} Not Found!", 14);


            $path = (string) "App\Services\\" . $pgCompany->service_class_name;

            if (!class_exists($path))
                throw new Exception("{$gateway} Service CLass Not Found!", 14);

            $paymentService = new $path();

            $recieptdata = $paymentService->handlePgCallback();

            if (is_array($recieptdata)) {
                return response()->json($recieptdata);
            } else {
                return redirect(url("gateway-pg-receipt?resdata={$recieptdata}"));
            }

        } catch (Exception $e) {

            return $this->errorResponse($e->getMessage(), $e->getCode(), 422);

            //return redirect()->route("pg-transaction.failure");

        }

    }

    public function handlePgCallbackTest(Request $request)
    {
        $gatway = 'Paytm';
        $decrypted = 'RESPONSE_DATE_TIME=2025-05-30 13:32:47~RESPONSE_CODE=000~AUTH_CODE=169049325~MOP_TYPE=VI~CARD_MASK=************8001~CURRENCY_CODE=356~RRN=169049325~CARD_HOLDER_NAME=jaabsadsad~PG_TXN_MESSAGE=Successful Payment~STATUS=Captured~PG_REF_NUM=2530133055753081~AMOUNT=10000~RESPONSE_MESSAGE=Successful Payment~DEMO_FINAL_REQUEST={"appId":"611626","orderId":"2530133055753081","orderAmount":null,"orderCurrency":"INR","customerName":"NA","customerEmail":"support.txn@asiancheckout.com","customerPhone":"9767146866","returnUrl":"https://preprod.pay10.com/pgui/jsp/demoResponse","paymentOption":"card","card_number":"4035625755538001","card_holder":"jaabsadsad","card_expiryMonth":"02","card_expiryYear":"2029","card_cvv":"123","signature":"AVi4JLEYWrzL8OfM0cXXcQVs8J95nN6c9W0A3Qr3usQ="}~CARD_ISSUER_COUNTRY=INDIA~TXN_ID=2530133247351090~CARD_ISSUER_BANK=ICICI BANK, LTD.~ACQ_ID=169049325~TXNTYPE=SALE~SURCHARGE_FLAG=N~HASH=0906CCD49D1060D43D6C96DC2A21F54277CD3130078D4B424BFE38B0E3E48C71~PAYMENT_TYPE=CC~RETURN_URL=http://127.0.0.1:8000/api/pg/Paytm/callback~PAY_ID=5011767734260001~ORDER_ID=TXN-20250530080027-6329-1~TOTAL_AMOUNT=10000~ACQUIRER_TYPE=DEMO';
        // dd($decrypted);
        parse_str(str_replace('~', '&', $decrypted), $return);
        //dd($return);

        Log::channel('pay10')->info('RESPONSE-REDIRECT-' . $request->ip(), $return);

        $cardmode = '';
        $savemode = '';
        $data = [];

        if (!empty($return) && $return['STATUS'] == 'Captured') {
            $cardDetails = json_decode($return['DEMO_FINAL_REQUEST'], true);
            //dd($cardDetails);
            $mode = $return['PAYMENT_TYPE'] ?? 'other';
            $modes = Mode::get()->pluck('name')->toArray();
            $cardmode = $savemode = in_array($mode, $modes) ? $mode : 'other';

            $data = [
                'id' => $return['ORDER_ID'],
                'amount' => $return['AMOUNT'] / 100,
                'orderid' => $return['TXN_ID'],
                'banktxnid' => $return['RRN'],
                'errormsg' => $return['STATUS'],
                'pg_order_id' => $return['ACQ_ID'],
                'paymentmode' => $cardmode,
            ];



            $txninfo = PaymentBankit::getdata(["txnid" => $data['id']])->data;

            if ($txninfo) {
                $getpgComm = ApiPartnerModeCompany::where(
                    [
                        'user_id' => $txninfo->user_id,
                        "mode_id" => $txninfo->mode_id,
                        "pg_company_id" => $txninfo->pg_company_id
                    ]
                )->first();
                $charges = $getpgComm->charges;

                if (!$charges)
                    throw new Exception("Charges Not Updated!", 1);


                $amtAfterDudection = $this->calculateCharge($data['amount'], $charges);

                $walletTopUp = round($amtAfterDudection, 2);
                $data['message'] = "Wallet Topup Successful with amount : $walletTopUp";

                $requestData = [
                    //'id' => $data['id'],
                    'txnid' => $data['id'],
                    'order_id' => $data['orderid'],
                    'transfertype' => $gatway,
                    'remarks' => $data['message'],
                    'comment' => $data['message'],
                    'user_id' => $txninfo->user_id,
                    'charges' => ((float) $data['amount']) - $amtAfterDudection,
                    'gst' => 0,
                    'profit' => 0,

                    'pg_company_id' => $txninfo->pg_company_id,
                    'mode_id' => $txninfo->mode_id,
                    'utr' => $data['banktxnid'],
                    'dateupdated' => date('Y-m-d H:i:s'),
                ];
                // dd( $txninfo->status);
                if ($txninfo->status == 2 && $return['STATUS'] == 'Captured') {
                    $data['status'] = 'Success';
                    $txnData = [
                        'txnid' => $data['id'],
                        'order_id' => $data['orderid'],
                        'remarks' => $data['message'],
                        'utr' => $data['banktxnid'],
                        'dateupdated' => date('Y-m-d H:i:s'),
                        'status' => 1
                    ];
                    PaymentBankit::tnxSuccess($txnData, $requestData);
                    $data['msg'] = $data['message'];


                } else {
                    $data['status'] = 'Failed';
                    $errorTxn = [
                        'txnid' => $return['ORDER_ID'],
                        'order_id' => $return['TXN_ID'],
                        'utr' => $return['RRN'],
                        'errormsg' => $return['STATUS'],
                        'sub_type' => $return['ACQ_ID']
                    ];
                    PaymentBankit::txnError($errorTxn);
                    $data['msg'] = 'Payment Failed.';
                }
            } else {
                $data['msg'] = 'Invalid referenceId.';
            }
        } elseif (!empty($return) && $return['STATUS'] == 'Failed') {
            $txninfo = PaymentBankit::txnError([
                'utr' => $return['RRN'],
                'status' => 0,
                'txnid' => $return['ORDER_ID'],
                'errormsg' => $return['PG_TXN_MESSAGE'],
                'dateupdated' => now()
            ]);

            $data = [
                'id' => $txninfo?->id,
                'amount' => $txninfo->amount,
                'orderid' => $txninfo->id,
                'banktxnid' => $txninfo->utr,
                'errormsg' => $txninfo->errormsg,
                "{$gatway}_order_id" => $txninfo->utr,
                'paymentmode' => $txninfo->mode->name,
                'msg' => 'Payment Failed.',
                'status' => 'Failed',
            ];
        } else {
            $data['msg'] = "Invalid {$gatway} signature.";
        }

        $resData = http_build_query($data);
        return redirect(url('gateway-pg-receipt?resdata=' . $resData));
    }

    public function generateToken(Request $request, JwtService $jwtService)
    {

        $validator = Validator::make(
            $request->all(),
            [
                //'iss' => 'required',
                //'product' => 'required',
                //'timestamp' => 'required',
                'reqid' => 'required',
                'partnerId' => 'required',
                'key' => 'required',
                'method' => 'required|in:GENERATE-URL,CHECK-TXN-STATUS'
            ]
        );
        try {

            if ($validator->fails())
                throw new Exception($validator->errors()->first());

            $this->checkValidRequest($request);

            $data = [
                //'iss' => $request->iss,
                //'product' => $request->product,
                'timestamp' => $request?->timestamp ?? time(),
                'reqid' => $request->reqid,
                'partnerId' => $request->partnerId,
                'method' => $request->method
            ];

            $key = str_replace(' ', '+', trim($request->key));

            $token = $jwtService->generateToken($data, $key, 300); // 5 mints

            if (!$token)
                throw new Exception("Unable To Generate Token, Please Contact to Vendor!", 15);

            return $this->successResponse(
                [
                    "token" => $token,
                    "method" => $request->method
                ],
                "Token Generate Successfully!"
            );

        } catch (Exception $e) {

            return $this->errorResponse(
                $e->getMessage(),
                $e->getCode()
            );

        }

    }

    private function decodeToken($token, $key, $userId, $body, $method, $jwtService)
    {
        try {

            $decoded = $jwtService->decodeToken($token, $key);

            $decodedData = (array) $decoded;

            if (!empty($decodedData)) {
                if (($decodedData['method'] ?? '') === $method) {
                    if (!empty($decodedData['timestamp'])) {
                        if (!empty($decodedData['reqid'])) {

                            $reqid = $decodedData['partnerId'] . $decodedData['reqid'];
                            $currentTime = time();
                            $reqTime = $decodedData['timestamp'];
                            $decodedData['LB-address'] = request()->server('SERVER_ADDR');

                            $mins = ($currentTime - $reqTime) / 60;

                            if (round($mins) <= 5) {
                                $exists = LogRequest::where('partner_id', $userId)
                                    ->where('reqid', $reqid)
                                    ->exists();

                                if (!$exists) {

                                    //$inserted = true;
                                    $inserted = LogRequest::create([
                                        'partner_id' => $userId,
                                        'reqid' => $reqid,
                                        'request' => $decodedData,
                                        'body' => $body,
                                        'method' => $method
                                    ]);

                                    return $inserted ? ['status' => true, 'message' => "Success", "data" => $decodedData, 'log_insert_id' => $inserted->id]
                                        : ['status' => false, 'message' => 'Unable To Log Request right now.', "code" => 3];

                                } else {
                                    return ['status' => false, 'message' => 'Duplicate Request ID Found.', "code" => 4];
                                }

                            } else {
                                return ['status' => false, 'message' => 'Request timestamp is older than 5 min.', "code" => 5];
                            }

                        } else {
                            return ['status' => false, 'message' => 'Invalid request ID', "code" => 6];
                        }
                    } else {
                        return ['status' => false, 'message' => 'Invalid timestamp'];
                    }
                } else {
                    return ['status' => false, 'message' => 'Invalid Method!', "code" => 7];
                }

            } else {
                return ['status' => false, 'message' => 'Unable to decode data.', "code" => 8];
            }

        } catch (Exception $e) {
            return ['status' => false, 'message' => $e->getMessage(), "code" => 9];
        }
    }

    private function is_valid($partner_info)
    {
        $companies = PgCompany::whereStatus(1)->pluck('id')->toArray();

        $config = $partner_info->user->apiConfig()->whereIn('pg_company_id', $companies)->get()->toArray();

        if (!count($config))
            throw new Exception('Partner Configuration Not Set.', 16);
    }

    private function checkValidRequest($req)
    {
        $valid = $req->apiCredentials();

        $key = str_replace(' ', '+', trim($req->key));

        if (!$valid->checkKey(trim($key)))
            throw new Exception("Invalid Partner Key", 17);

        $user = $valid->user->checkPartner($req->partnerId);

        if (!$user)
            throw new Exception("Invalid Partner Id.", 18);

        $reqid = $req->partnerId . $req->reqid;

        $exists = LogRequest::where('partner_id', $req->partnerId)
            ->where('reqid', $reqid)
            ->exists();

        if ($exists)
            throw new Exception('Duplicate Request ID Found.', 4);

        return true;
    }

    public function txnStatus(Request $request, JwtService $jwtService)
    {
        $info = [];
        try {
            $partner_info = $request->apiCredentials();

            //dd($partner_info->user->username);
            if (!$partner_info)
                return $this->errorResponse('Authentication failed', 0);

            $this->is_valid($partner_info);


            if (!in_array(1, [$partner_info->pg]))
                return $this->errorResponse('PG service is disabled.', 1, 404);


            $token = $request->header('Token');

            if (!$token)
                return $this->errorResponse('Invalid Jwt Token.', 2, 401);

            $body = $request->all();
            $method = 'CHECK-TXN-STATUS';

            $info = $this->decodeToken(
                $token,
                $partner_info->key,
                $partner_info->user->username,
                $body,
                $method,
                $jwtService
            );

            if (!$info['status'])
                return $this->errorResponse($info['message'] ?? 'Unable to process request', $info['code'] ?? 25);

            if (!($info['data']['partnerId'] === $partner_info->user?->username)) {
                return $this->errorResponse("Partner ID Doesn't Match!", 10);
            }

            $validator = Validator::make($request->all(), [
                'refid' => 'required|string'
            ]);

            if ($validator->fails())
                return $this->errorResponse($validator->errors()->first(), 11);


            $txn = Pgtxn::where([
                'refid' => $request->refid,
                'user_id' => $partner_info['user_id']
            ])->first();

            if (!$txn)
                return $this->errorResponse('Invalid Reference Number', 19);

            $response = [
                "id" => $txn->txnno,
                "amount" => $txn->amt_after_deduction ?? $txn->amount,
                "banktxnid" => $txn->utr,
                "errormsg" => $txn?->errormsg ?? '',
                "{$txn?->company?->name}_order_id" => $txn->order_id,
                "paymentmode" => $txn?->mode?->name,
                "msg" => $txn->status == 4 ? $txn?->refund_remarks : $txn->remarks,
                "status" => $this->status[((int) $txn->status)] ?? ''
            ];

            return $this->successResponse(
                $response,
                'Status Fetch Successfully!',
                logInsertedId: $info['log_insert_id'] ?? 0
            );
        } catch (\Throwable $e) {
            return $this->errorResponse(
                $e->getMessage(),
                $e->getCode(),
                500,
                logInsertedId: $info['log_insert_id'] ?? 0
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                $e->getCode(),
                500,
                logInsertedId: $info['log_insert_id'] ?? 0
            );
        }
    }


    public function updateIniTransaction()
    {
        try {
            $currentTime = Carbon::now()->subMinutes(30);

            $timeoutTxns = Pgtxn::where('status', 2)
                ->where('created_at', '<=', $currentTime)
                ->get();

            if ($timeoutTxns->isNotEmpty()) {

                DB::beginTransaction();

                Pgtxn::whereIn('id', $timeoutTxns->pluck('id'))
                    ->update([
                        'status' => 0,
                        'errormsg' => 'Transaction Timeout',
                        'updated_at' => now()
                    ]);

                foreach ($timeoutTxns as $txn) {
                    Log::channel('cron_logs')->info('Transaction marked as timeout', [
                        'id' => $txn->id,
                        'txnno' => $txn->txnno,
                        'refid' => $txn->refid,
                    ]);
                }

                DB::commit();

            } else {
                Log::channel('cron_logs')->info("No timed-out transactions found.");
            }

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::channel('cron_logs')->error('Exception during timeout update: ' . $e->getMessage());
        }
    }

}
