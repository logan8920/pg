<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Storage, URL};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use App\Services\UserService;
use App\Models\Pgtxn;

class PgReceiptController extends Controller
{
    public function index(Request $request)
    {
        $data = [];
        try {

           
            $resData = str_replace(" ", "+", base64_decode($request?->resdata));

            parse_str(str_replace('"', "", UserService::decrypt($resData)), $data);
            dd($data);
            $txn = Pgtxn::where(["txnno" => $data["id"] ?? "false111", "status" => 1])->first();

            if (!$txn)
                throw new \Exception( $data["msg"] ?? $data["message"] ?? 'Something Went Worng!', 422);

            $encData = $txn?->encdata;

            $decrptdata = json_decode(UserService::decrypt($encData), true);

            $data['returnUrl'] = $decrptdata["redirect_url"];
            $userKey = $txn->user?->apiCredentials?->key;
            $userIv = $txn->user?->apiCredentials?->iv;
            $data['encResponse'] = UserService::encryptCustom(
                json_encode($data),
                $userKey,
                $userIv
            );

            $txn->update(['status' => 3]);

            return view('payment-gateway.gateway-pg-receipt', $data);

        } catch (\Exception $e) {

            if (isset($data["id"]) && !empty($data["id"])) {
                $txn = Pgtxn::where(["txnno" => $data["id"], "status" => 0])->first();
                if ($txn) {
                    $encData = $txn?->encdata;
                    $txn->update(['errormsg' => $e->getMessage()]);
                    $decrptdata = json_decode(UserService::decrypt($encData), true);
                    $data['returnUrl'] = $decrptdata["redirect_url"];
                    $userKey = $txn->user?->apiCredentials?->key;
                    $userIv = $txn->user?->apiCredentials?->iv;
                    $data['encResponse'] = UserService::encryptCustom(
                        json_encode($data),
                        $userKey,
                        $userIv
                    );
                }
            }
            
            $data['error'] = $e->getMessage();
            return view('payment-gateway.error', $data);

        }

    }

    private function generateAndStoreReceipt($data)
    {
        try {
            $pdf = Pdf::loadView('pdf.receipt', ['data' => $data]);

            $fileName = 'receipt_' . Str::slug($data['orderid']) . '_' . time() . '.pdf';

            Storage::disk('public')->put("receipts/{$fileName}", $pdf->output());

            $tempUrl = URL::temporarySignedRoute(
                'download.receipt',
                now()->addMinutes(10),
                ['filename' => $fileName]
            );

            return [
                'success' => true,
                'url' => $tempUrl,
            ];
        } catch (\Exception $e) {
            throw $e;
        }

    }
}
