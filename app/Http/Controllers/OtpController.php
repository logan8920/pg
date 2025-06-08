<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Models\ApiCredentials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use DB;

class OtpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'phone' => ['required', 'digits:10'],
                'otp_for' => ['required']
            ]
        );

        if ($validator->fails()):
            return response()->json([
                'validationError' => $validator->errors()
            ], 200);
        endif;

        try {

            Otp::create([
                'phone' => $request->phone,
                'otp_for' => $request->otp_for,
                'status' => '0',
                'otp' => 999999
            ]);

            return response()->json([
                'success' => 'Otp Generated Successfully :)'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Otp $otp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Otp $otp)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Otp $otp)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Otp $otp)
    {
        //
    }

    public function verify(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'otp' => ['required', 'digits:6'],
                'id' => ['required', 'numeric'],
                'phone' => ['required', 'digits:10']
            ]
        );

        if ($validator->fails()):
            return response()->json([
                'validationError' => $validator->errors()
            ], 200);
        endif;

        #DB::beginTransaction();

        try {

            $otp = Otp::where(['otp' => $request->otp, 'status' => 0, 'phone' => $request->phone])->orderBy('id', 'DESC')->first();

            if (!$otp)
                throw new \Exception("Invalid Opt, Please Enter valid Otp.");

            $start = Carbon::parse($otp->created_at);
            $end = Carbon::now();

            $minutes = $start->diffInMinutes($end);

            if ($minutes > 5)
                throw new \Exception("Otp Expired Please try again.");


            if ($otp->otp != $request->otp)
                throw new \Exception("Invalid Opt, Please Enter valid Otp.");

            $otp->update(['status' => 1]);

            $api_Credential = $api_Credential = ApiCredentials::where('user_id', $request->id)->firstOrCreate([
                'user_id' => $request->id
            ]);

            if (!$api_Credential)
                throw new \Exception("Error Processing Request");

            $api_Credential->update(
                [
                    'user_id' => $request->id,
                    'status' => 1,
                    'key' => base64_encode(random_bytes(32)),
                    'iv' => base64_encode(random_bytes(16)),
                    'added_date' => date('Y-m-d H:i:s'),
                    'date_added' => date('Y-m-d H:i:s')
                ]
            );

            #DB::commit();
            return response()->json([
                'success' => 'Key Generated Successfully :)',
                "tableReqload" => true,
                "callback" => auth()->user()->api_partner === 0 ? 'sendNotification' : false
            ]);

        } catch (\Exception $e) {
            #DB::rollBack();
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }


    public function ipWhiteList(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'id' => ['required', 'numeric'],
                'ip' => ['required', 'ip'],
                'send_mail' => 'required'
            ]
        );

        if ($validator->fails()):
            return response()->json([
                'validationError' => $validator->errors()
            ], 200);
        endif;

        #DB::beginTransaction();

        try {

            $api_Credential = ApiCredentials::where('user_id', $request->id)->firstOrFail();


            if (!$api_Credential)
                throw new \Exception("Error Processing Request");


            $api_Credential->update(['ipaddress' => explode(',',$request->ip)]);

            // if($api_Credential && $request->send_mail) {
            //     $this->send_mail();
            // }

            #DB::commit();
            return response()->json([
                'success' => 'ip whitelist successfully :)',
                "tableReqload" => true,
            ]);

        } catch (\Exception $e) {
            #DB::rollBack();
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }
}
