<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\PgCompany;

class EasebuzzService
{
    private $sUrl;

    private $fUrl;

    private $gateway = 'Easebuzz';

    private $pgCollection;

    private $key;

    private $salt;

    private $env;

    public function __consturct()
    {
        $this->pgCollection = PgCompany::whereName($this->gateway)->first();
        $this->sUrl = route('pg-redirecturl.callback', $this->gateway);
        $this->sUrl = route('pg-redirecturl.callback', $this->gateway);
        $this->key = $this->pgCollection->config['key'];
        $this->salt = $this->pgCollection->config['salt'];
        $this->env = $this->pgCollection->config['env'];

    }

    public function init(array $reqData)
    {
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

        $num = now()->timestamp;
        Log::info("REQUEST - $num", $dataToBePosted);

        try {
            $response = Http::asForm()
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->post('https://pay.easebuzz.in/payment/initiateLink', $dataToBePosted);

            $json = $response->json();

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
                $formFields = [
                    "resdata" => UserService::encrypt([
                        'id' => $reqData["txnno"],
                        'message' => $json['error_desc'] ?? 'Unable to get response from easebuzz'
                    ]),
                ];
                return compact('posturl', 'formFields','method');
            }
        } catch (\Exception $e) {
            Log::error("Easebuzz error - $num: " . $e->getMessage());
            $posturl = url("gateway-pg-receipt");
            $method = 'GET';
            $formFields = [
                "resdata" => UserService::encrypt([
                    'id' => $reqData["txnno"],
                    'message' => $e->getMessage()
                ]),
            ];
            return compact('posturl', 'formFields','method');
            //return ["status" => false, "error" => "Exception occurred"];
        }
    }

    public function handlePgCallback()
    {
        throw new \Exception('RazorpayService Service is Not Set.');
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
