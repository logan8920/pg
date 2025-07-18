<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\{PgCompany, ApiPartnerModeCompany, User};
use App\Libraries\PaymentBankit;
use App\Traits\CommanTrait;
use DB;

class EasebuzzService
{
    use CommanTrait;
    private $sUrl;

    private $fUrl;

    private $gateway = 'Easebuzz';

    private $pgCollection;

    private $key;

    private $salt;

    private $env;

    private $apiPartnerId;

    public function __consturct($apiPartnerId = null)
    {
        $this->pgCollection = PgCompany::whereName($this->gateway)->first();
        $this->apiPartnerId = $apiPartnerId;
        $this->sUrl = route('pg-redirecturl.callback', $this->gateway);
        $this->fUrl = route('pg-redirecturl.callback', $this->gateway);
        $this->key = $this->pgCollection->apiPgCred()->whereUserId($this->apiPartnerId)->first()?->pg_credentials['key'];
        $this->salt = $this->pgCollection->apiPgCred()->whereUserId($this->apiPartnerId)->first()?->pg_credentials['salt'];
        $this->env = $this->pgCollection->apiPgCred()->whereUserId($this->apiPartnerId)->first()?->pg_credentials['env'];

    }

    public function init(array $reqData)
    {
        $this->__consturct($reqData["txnno"]);
        //dd($reqData);
        $dataToBePosted = [
            "key" => $this->key,
            "txnid" => $reqData["txnno"],
            "amount" => $reqData["amount"],
            "productinfo" => "Wallet Load on mobile number - " . $reqData['mobile'],
            "firstname" => $reqData['name'],
            "phone" => $reqData['mobile'],
            "email" => $reqData['email'],
            "surl" => $this->sUrl,
            "furl" => $this->fUrl,
            "show_payment_mode" => $reqData['mode_pg']
        ];

        $txninfo = PaymentBankit::getdata(["txnno" => $reqData["txnno"]])->data;

        if (!$txninfo)
            throw new \Exception("Transaction Not Found!", 1);

        if(empty($this->key) || empty($this->salt))
            throw new \Exception("Key Or Salt Not Found", 1);

        // Create hash
        $hashString = implode('|', [
            $this->key,
            trim($dataToBePosted['txnid']),
            trim($dataToBePosted['amount']),
            trim($dataToBePosted['productinfo']),
            trim($dataToBePosted['firstname']),
            trim($dataToBePosted['email']),
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '', // 10 empty pipes
            $this->salt
        ]);

        $dataToBePosted['hash'] = hash("sha512", $hashString);

        $num = now("Asia/Kolkata")->timestamp;
        Log::info("REQUEST - $num", $dataToBePosted);

        try {

            $filters = [
                'user_id' => $reqData['user_id'],
                'mode_id' => $txninfo->mode?->id,
                "pg_company_id" => $this->pgCollection?->id
            ];

            $amount = $dataToBePosted['amount'];

            $amountData = PaymentBankit::getAmount($filters)->withLimits()->withSlab()->toArray();

            $totalAmount = ($amountData['amounts'] ?? 0) + $amount;

            $modeLimit = $amountData['mode_limit'] ?? 0;

            $perDayLimit = $amountData['pg_daily_limit'] ?? 0;

            $withinLimit = $totalAmount <= $perDayLimit && $amount <= $modeLimit;


            if (!$withinLimit)
                return throw new \Exception("Easebuzz Transaction Limit Exceeded, Please Contant to Vendor!", 5);

            $txninfo->tQuery->update(['pg_request' => $dataToBePosted]);   

            $response = Http::asForm()
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->post('https://pay.easebuzz.in/payment/initiateLink', $dataToBePosted);

            $json = $response->json();

            $txninfo->tQuery->update(['pg_request' => $dataToBePosted]);

            Log::info("RESPONSE - $num", $json);

            if (isset($json['status']) && $json['status'] == 1) {

                return [
                    "status" => true,
                    //"data" => $json['data'],
                    "pg_script" => $this->getScript($json['data'])
                ];
            } else {
                $posturl = url("gateway-pg-receipt");
                $method = 'GET';
                $txninfo->update([
                    'status' => 0,
                    'dateupdated' => date('Y-m-d H:i:s')
                ]);
                $formFields = [
                    "resdata" => base64_encode(UserService::encrypt(http_build_query([
                        'id' => $reqData["txnno"],
                        'message' => $json['error_desc'] ?? 'Unable to get response from easebuzz'
                    ]))),
                ];
                return compact('posturl', 'formFields', 'method');
            }
        } catch (\Exception $e) {
            Log::error("Easebuzz error - $num: " . $e->getMessage());
            $txninfo->update([
                'status' => 0,
                'dateupdated' => date('Y-m-d H:i:s')
            ]);
            $posturl = url("gateway-pg-receipt");
            $method = 'GET';
            $formFields = [
                "resdata" => base64_encode(UserService::encrypt(http_build_query([
                    'id' => $reqData["txnno"],
                    'message' => $e->getMessage()
                ]))),
            ];
            return compact('posturl', 'formFields', 'method');
            //return ["status" => false, "error" => "Exception occurred"];
        }
    }

    public function handlePgCallback()
    {
        
        $return = request()->all();
        $num = time();
        Log::error("Easebuzz JS RESPONSE - " . $num, $return);

        $txninfo = PaymentBankit::getdata(["txnno" => $return["txnno"]])->data;
        $this->__consturct($txninfo->user_id);
        if (!$txninfo) {
            return response()->json(['status' => false, 'message' => 'Unknown Transaction']);
        }

        $txninfo->tQuery->update(['pg_response' => $return]);

        if ($return['status'] == 'success') {
            DB::beginTransaction();
            try {
                $updateData = [
                    'userid' => $txninfo->user_id,
                    'amount' => $return['amount'],
                    'ipaddress' => request()->ip(),
                    'name' => $return["name_on_card"],
                    'mode_pg' => $return['mode'],
                    'dateupdated' => now("Asia/Kolkata"),
                    'utr' => $return["bank_ref_num"],
                    'errormsg' => $return["error_Message"] ?: $return["error"],
                    'card' => str_replace("X", "", $return['cardnum']),
                    'status' => 1,
                ];

                $txninfo->update([
                    'mode_pg' => "{$return['mode']}_{$this->gateway}"
                ]);

                $getpgComm = ApiPartnerModeCompany::where(
                    [
                        'user_id' => $txninfo->user_id,
                        "mode_id" => $txninfo->mode_id,
                        "pg_company_id" => $this->pgCollection->id
                    ]
                )->first();

                $charges = $getpgComm?->charges;

                if (!$charges)
                    throw new \Exception("Charges Not Updated!", 1);

                if ($txninfo->status !== 2) {
                    throw new \Exception("Invalid Transaction Status", 1);
                }
                $amts = $this->calculateCharge($return['amount'], $charges);

                $amtAfterDudection = $amts['final_amount'];

                $walletTopUp = round($amtAfterDudection, 2);

                $data['message'] = "Wallet no. {$txninfo->mobile} is successfully loaded with Rs {$walletTopUp}.";

                $requestData = [
                    'txnno' => $txninfo->txnno,
                    'order_id' => '',
                    'refid' => $txninfo->refid,
                    'transfertype' => 'Credit',
                    'remarks' => $data['message'],
                    'comment' => $data['message'],
                    'amount' => $amts['original_amount'],
                    'charge' => $amts['charge_amount'],
                    'amt_after_deduction' => $amtAfterDudection,
                    'gst' => $amts['gst_amount'],
                    'mode' => $txninfo->mode?->name,
                    'previous_balance' => $txninfo->user?->balance,
                    'balance' => round(($txninfo->user?->balance ?? 0.00) + $amtAfterDudection, 2),
                    'gateway' => $this->pgCollection?->name,
                    'pg_company_id' => $this->pgCollection?->id,
                    'errormsg' => $return["error_Message"] == "" ? $return["error"] : $return["error_Message"],
                    'utr' => $return["bank_ref_num"],
                    'user_id' => $txninfo->user_id,
                    'dateupdated' => date('Y-m-d H:i:s'),
                ];


                $data['status'] = 'Success';
                $txnData = [
                    'txnno' => $txninfo->txnno,
                    'order_id' => $data['orderid'],
                    'remarks' => $data['message'],
                    'utr' => $data['banktxnid'],
                    'mode_pg' => "{$txninfo->mode?->name}_{$this->pgCollection?->name}",
                    'pg_company_id' => $this->pgCollection?->id,
                    'amount' => $amts['original_amount'],
                    'charge' => $amts['charge_amount'],
                    'amt_after_deduction' => $amtAfterDudection,
                    'gst' => $amts['gst_amount'],
                    'dateupdated' => date('Y-m-d H:i:s'),
                    'status' => 1
                ];


                PaymentBankit::tnxSuccess($txnData, $requestData);
                $data['msg'] = $data['message'];
                $userPreviousBalance = $txninfo->user?->balance ?? 0.00;
                User::whereId($txninfo->user_id)->update(['balance' => ($userPreviousBalance + $amtAfterDudection)]);

                DB::commit();
                $reqData = base64_encode(UserService::encrypt(http_build_query([
                    'id' => $txninfo->txnno,
                    'message' => "Transaction Successful !"
                ])));
                return [
                    'status' => true,
                    'message' => ' Redirecting please wait....',
                    'redirectURL' => url("gateway-pg-receipt?resdata={$reqData}")
                ];


            } catch (\Exception $e) {
                DB::rollBack();
                $updateData = [
                    'txnno' => $txninfo->txnno,
                    "amount" => $return['amount'] ?? $txninfo->amount,
                    "ipaddress" => request()->ip(),
                    "mode_pg" => "{$txninfo->mode?->name}_{$this->pgCollection?->name}",
                    "dateupdated" => now("Asia/Kolkata"),
                    "utr" => $return["bank_ref_num"] ?? '',
                    "errormsg" => $e->getMessage(),
                    "card" => str_replace("X", "", $return['cardnum'] ?? $txninfo->card),
                    "status" => 0
                ];

                PaymentBankit::txnError($updateData);

                $reqData = base64_encode(UserService::encrypt(http_build_query([
                    'id' => $txninfo->txnno,
                    'message' => $e->getMessage(),
                ])));

                return [
                    'status' => false,
                    'message' => 'Payment Failed redirecting...',
                    'redirectURL' => url("gateway-pg-receipt?resdata={$reqData}")
                ];
            }
        } else {

            $updateData = [
                "amount" => $txninfo->amount,
                "ipaddress" => request()->ip(),
                "name" => $return["name_on_card"] ?? $txninfo->name,
                "mode_pg" => $return['mode'] ?? "{$txninfo->mode?->name}_{$this->pgCollection?->name}",
                "dateupdated" => now("Asia/Kolkata"),
                "utr" => $return["bank_ref_num"] ?? '',
                "errormsg" => $return["error_Message"] ?? $return["error"] ?? 'No Response Form Easebuzz',
                "card" => str_replace("X", "", $return['cardnum'] ?? $txninfo->card),
                "status" => 0
            ];

            PaymentBankit::txnError($updateData);

            $reqData = base64_encode(UserService::encrypt(http_build_query([
                'id' => $txninfo->txnno,
                'message' => $return["error_Message"] == "" ? $return["error"] : $return["error_Message"],
            ])));

            return [
                'status' => false,
                'message' => 'Payment Failed redirecting...',
                'redirectURL' => url("gateway-pg-receipt?resdata={$reqData}")
            ];

        }

    }

    private function getScript(array $data)
    {
        $template = '';
        $key = $this->key;
        $env = $this->env;
        $accesskey = $data['accesskey'];
        $gateway = $this->gateway;
        include base_path('app/Template/EasebuzzScript.php');
        return $template;
    }
}
