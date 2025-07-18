<?php

namespace App\Services;
use Exception;
use Illuminate\Support\Facades\Request;
use App\Libraries\PaymentBankit;
use App\Models\{PgCompany, Mode, ApiPartnerModeCompany, User, Pgtxn};
use App\Traits\CommanTrait;
use Log;
use DB;

class Pay10Service
{
    use CommanTrait;

    function __consturct()
    {

        //throw new \Exception('Pay10 Service is Not Set.');
    }

    public function init(array $data)
    {
        $request = request();
        $user = User::whereId($data['user_id'])->first();
        $paymentGateway = $user->apiConfig()->first()->company;
        $paymentGatewayId = $paymentGateway?->id;

        try {
            //dd($data);
            $mode = trim(strtoupper($data['mode_pg']));
            $modeCollection = Mode::whereName($mode)->first();
            $amount = $data['amount'];
            $mobile = $data['mobile'];
            $email = $data['email'];
            $card = $data['card'];
            $name = $data['name'];
            //$OrderId = (string) Str::uuid();
            $filters = [
                'user_id' => $user->id,
                'mode_id' => $modeCollection->id,
                "pg_company_id" => $paymentGateway->id
            ];

            $amountData = PaymentBankit::getAmount($filters)->withLimits()->withSlab()->toArray();

            $totalAmount = ($amountData['amounts'] ?? 0) + $amount;

            $modeLimit = $amountData['mode_limit'] ?? 0;

            $perDayLimit = $amountData['pg_daily_limit'] ?? 0;

            $withinLimit = $totalAmount <= $perDayLimit && $amount <= $modeLimit;


            if (!$withinLimit)
                throw new Exception("Transaction Limit Exceeded, Please Contant to Vendor!", 20);

            $posturl = 'https://preprod.pay10.com/pgui/jsp/paymentrequest';
            //$posturl = 'https://uatpg.pay10.ae/pgui/jsp/paymentrequest';

            $transaction = Pgtxn::where([
                "txnno" => $data['txnno'],
                "refid" => $data['refid'],
                "user_id" => $data['user_id']
            ])
                ->first();

            $finaldata = [
                'txnid' => $transaction['txnno'],
                'amount' => $amount,
                'name' => 'VIDCOM BUSINESS SOLUTION PRIVATE LIMITED',
                'email' => $email,
                'phone' => $mobile,
                'surl' => route("pg-redirecturl.callback", $paymentGateway->name),
                'mode' => $mode,
                'productinfo' => 'PG',
            ];

            $gatway = PgCompany::whereName($paymentGateway->name)->first();

            if (!$gatway)
                throw new Exception("Invalid Payment Gateway!", 21);
            
            $payid = $gatway->apiPgCred()->whereUserId($user->id)->first()?->pg_credentials['payid'];
            $salt = $gatway->apiPgCred()->whereUserId($user->id)->first()?->pg_credentials['salt'];

            // $payid = '5011767734260001';
            // $salt = 'f05666e27b65421d';
            $amount = $finaldata['amount'] * 100;
            $string =
                'AMOUNT=' .
                $amount .
                '~CURRENCY_CODE=356~ORDER_ID=' .
                $finaldata['txnid'] .
                '~PAY_ID=' .
                $payid .
                '~RETURN_URL=' .
                $finaldata['surl'] .
                '~TXNTYPE=SALE~UDF14=' .
                $finaldata['mode'] .
                $salt;
            $hash = strtoupper(hash('sha256', $string));

            $formFields = [
                "PAY_ID" => $payid,
                "ORDER_ID" => $finaldata['txnid'],
                "AMOUNT" => $amount,
                "RETURN_URL" => $finaldata['surl'],
                "CURRENCY_CODE" => '356',
                "TXNTYPE" => 'SALE',
                "UDF14" => $finaldata['mode'],
                "HASH" => $hash
            ];

            $transaction->tQuery->update(["pg_request" => $formFields]);

            return compact('posturl', 'finaldata', 'formFields');

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());

        } catch (\Throwable $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function handlePgCallback()
    {
        $request = request();
        $provider = "Pay10";
        $txninfo = false;
        try {

            $gatway = PgCompany::whereName($provider)->first();

            if (!$gatway)
                throw new Exception("Invalid Payment Gateway", 21);


            $return = $request->post();

            Log::channel($gatway)->info('RESPONSE-REDIRECT-' . $request->ip(), $return);

            if (empty($return['ENCDATA'] ?? ''))
                throw new Exception("Unable to get response for Payment gateway", 22);


            $encryptionKey = $gatway->pg_config['key'];

            $iv = $gatway->pg_config['iv'];

            $decrypted = UserService::decryptCustom($return['ENCDATA'], $encryptionKey, $iv, 'AES-256-CBC');

            parse_str(str_replace('~', '&', $decrypted), $return);

            $cardmode = '';

            $data = [];

            if (!empty($return) && $return['STATUS'] == 'Captured') {
                
                //$cardDetails = json_decode($return['DEMO_FINAL_REQUEST'], true);

                $mode = $return['PAYMENT_TYPE'] ?? 'other';
                $modes = Mode::get()->pluck('name')->toArray();
                $cardmode = in_array($mode, $modes) ? $mode : 'other';

                $data = [
                    'id' => $return['ORDER_ID'],
                    'amount' => $return['AMOUNT'] / 100,
                    'orderid' => $return['TXN_ID'],
                    'banktxnid' => $return['RRN'],
                    'errormsg' => $return['STATUS'],
                    'pg_order_id' => $return['ACQ_ID'],
                    'paymentmode' => $cardmode,
                ];

                $txninfo = PaymentBankit::getdata(["txnno" => $data['id']])->data;

                if ($txninfo) {

                    $txninfo->update([
                        'mode_pg' => "{$txninfo?->mode?->name}_{$gatway?->name}",
                        'amount'  => $data['amount'],
                        'status'  => 1
                    ]);

                    $getpgComm = ApiPartnerModeCompany::where(
                        [
                            'user_id' => $txninfo->user_id,
                            "mode_id" => $txninfo->mode_id,
                            "pg_company_id" => $gatway->id
                        ]
                    )->first();

                    $charges = $getpgComm?->charges;

                    if (!$charges)
                        throw new Exception("Charges Not Updated!", 27);

                    $amts = $this->calculateCharge($data['amount'], $charges);

                    $amtAfterDudection = $amts['final_amount'];

                    $walletTopUp = round($amtAfterDudection, 2);
                    $data['message'] = "Wallet Topup Successful with amount : $walletTopUp";

                    DB::beginTransaction();

                    $requestData = [
                        'txnno' => $data['id'],
                        'order_id' => $data['orderid'],
                        'refid' => $txninfo->refid,
                        'transfertype' => 'Credit',
                        'remarks' => $data['message'],
                        'comment' => $data['message'],
                        'user_id' => $txninfo->user_id,
                        'amount' => $amts['original_amount'],
                        'charge' => $amts['charge_amount'],
                        'amt_after_deduction' => $amtAfterDudection,
                        'gst' => $amts['gst_amount'],
                        'mode' => $txninfo->mode?->name,
                        'previous_balance' => $txninfo->user?->balance,
                        'balance' => round(($txninfo->user?->balance ?? 0.00) + $amtAfterDudection, 2),
                        'gateway' => $gatway?->name,
                        'pg_company_id' => $gatway->id,
                        'mode_id' => $txninfo->mode_id,
                        'utr' => $data['banktxnid'],
                        'dateupdated' => date('Y-m-d H:i:s'),
                    ];

                    if ($txninfo->status == 1 && $return['STATUS'] == 'Captured') {
                        $data['status'] = 'Success';
                        $txnData = [
                            'txnno' => $data['id'],
                            'order_id' => $data['orderid'],
                            'remarks' => $data['message'],
                            'utr' => $data['banktxnid'],
                            'mode_pg' => "{$txninfo->mode?->name}_{$gatway?->name}",
                            'pg_company_id' => $gatway?->id,
                            'amount' => $amts['original_amount'],
                            'charge' => $amts['charge_amount'],
                            'amt_after_deduction' => $amtAfterDudection,
                            'gst' => $amts['gst_amount'],
                            'dateupdated' => date('Y-m-d H:i:s'),
                            //'status' => 1
                        ];


                        PaymentBankit::tnxSuccess($txnData, $requestData);
                        $data['msg'] = $data['message'];
                        $userPreviousBalance = $txninfo->user?->balance ?? 0.00;
                        User::whereId($txninfo->user_id)->update(['balance' => ($userPreviousBalance + $amtAfterDudection)]);

                    } else {
                        $data['status'] = 'Failed';
                        $errorTxn = [
                            'txnno' => $return['ORDER_ID'],
                            'order_id' => $return['TXN_ID'],
                            'utr' => $return['RRN'],
                            'errormsg' => $return['STATUS'],
                            'sub_type' => $return['ACQ_ID'],
                            'status' => 0
                        ];
                        PaymentBankit::txnError($errorTxn);
                        $data['msg'] = 'Payment Failed.';
                    }
                } else {
                    $data['msg'] = 'Invalid referenceId or txnno.';
                }
            } elseif (!empty($return) && $return['STATUS'] == 'Failed') {
                $txninfo = PaymentBankit::txnError([
                    'utr' => $return['RRN'],
                    'status' => 0,
                    'txnno' => $return['ORDER_ID'],
                    'errormsg' => $return['PG_TXN_MESSAGE'],
                    'dateupdated' => now("Asia/Kolkata")
                ]);

                $data = [
                    'id' => $txninfo?->txnno,
                    'amount' => $txninfo->amount,
                    'orderid' => $txninfo->id,
                    'banktxnid' => $txninfo->utr,
                    'errormsg' => $txninfo->errormsg,
                    "{$gatway?->name}_order_id" => $txninfo->utr,
                    'paymentmode' => $txninfo->mode->name,
                    'msg' => $txninfo?->errormsg,
                    'status' => 'Failed',
                ];
            } else {
                $data['msg'] = "Invalid {$gatway?->name} signature.";
            }

            if ($txninfo) {
                $txninfo->tQuery->update(['pg_response' => is_array($return) ? $return : []]);
            }
            
            $response = UserService::encrypt(http_build_query($data));
            DB::commit();
            return base64_encode($response);
            //return redirect(url('gateway-pg-receipt?resdata=' . $resData));
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
