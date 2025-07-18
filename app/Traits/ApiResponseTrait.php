<?php

namespace App\Traits;
use App\Models\LogRequest;
use Log;

trait ApiResponseTrait
{
    public function errorResponse(string $message, int $code = 25, int $httpStatus = 401, $logInsertedId = 0)
    {
        Log::error($message);

        $response = [
            'status' => false,
            'response_code' => $code,
            'message' => env('APP_DEBUG', false) ? $message : $this->errorMessage($code, $message),
            'timestamp' => now("Asia/Kolkata")
        ];

        if ($logInsertedId !== 0) {
            LogRequest::whereId($logInsertedId)->update(['response' => $response]);
        }

        return response()->json($response, $httpStatus);
    }

    public function successResponse(array $data = [], string $message = 'Success', int $code = 1, int $httpStatus = 200, $logInsertedId = 0)
    {
        $response = [
            'status' => true,
            'response_code' => $code,
            'data' => $data,
            'message' => $message,
            'timestamp' => now("Asia/Kolkata")
        ];

        if ($logInsertedId !== 0) {
            LogRequest::whereId($logInsertedId)->update(['response' => $response]);
        }

        return response()->json($response, $httpStatus);
    }

    public function errorMessage($code = null, $message = null)
    {
        $messages = [
            0 => "Authentication Failed",
            1 => "PG service is disabled.",
            2 => "Invalid Jwt Token.",
            3 => "Unable To Log Request Right Now.",
            4 => "Duplicate Request ID Found.",
            5 => "Request timestamp is older than 5 min.",
            6 => "Invalid request ID",
            7 => "Invalid Method!",
            8 => "Unable to decode data.",
            9 => "Exception Occurred.",
            10 => "Partner ID Doesn't Match!",
            11 => "Validation Error", // message added below
            12 => "Unable to get transaction mode",
            13 => "Payment Gateway Not Found or Limit Exceeded",
            14 => "Class Exception", // message added below
            15 => "Unable To Generate Token, Please Contact Vendor.",
            16 => "Partner Configuration Not Set.",
            17 => "Invalid Partner Key",
            18 => "Invalid Partner Id.",
            19 => "Invalid Reference Number",
            20 => "Transaction Limit Exceeded, Please Contact Vendor!",
            21 => "Invalid Payment Gateway",
            22 => "Unable to get response for Payment Gateway",
            23 => "Invalid encrypted data",
            24 => "Decryption failed",
            25 => "Unknown Error Occurred!",
            26 => "Encryption failed",
            27 => "Charges Not Updated!",
            28 => 'Api Partner Credentials Not Found!'
        ];

        $response = $messages[((int)$code)] ?? 'Something went wrong, Please Contact Vendor!';

        if (in_array((int) $code, [11, 14]) && $message) {
            $response .= ": {$message}";
        }

        return $response;
    }

}
