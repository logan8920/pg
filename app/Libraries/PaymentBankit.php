<?php

namespace App\Libraries;

use App\Models\{PaymentTransaction,Pgtxn,ApiPartnerModeCompany,UserPgCredential};
use Exception;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use DB;

/**
 * Class PaymentBankit
 *
 * Handles transaction amount checks and associated PG mode limits.
 */
class PaymentBankit
{
    /**
     * The transaction instance or data.
     *
     * @var \Illuminate\Support\Collection
     */
    protected static $transaction;

    /**
     * Filters used for querying the transaction and limits.
     *
     * @var array|null
     */
    public static $filters = null;

    /**
     * Holds any result data passed to the class.
     *
     * @var array|Collection
     */
    public $data;

    /**
     * Holds any result data passed to the class.
     *
     * @var array|Collection
     */
    public static $slab = NULL;

    /**
     * Constructor to initialize the data.
     *
     * @param array|Collection $data
     */
    public function __construct($data = [])
    {
        $this->data = $data;
    }

    /**
     * Get the sum of amounts for today based on provided filters.
     * Defaults to 0 if no transaction is found.
     *
     * @param array $filters
     * @return PaymentBankit
     */
    public static function getAmount(array $filters)
    {
        self::$filters = $filters;
        $transaction = PaymentTransaction::where($filters)
            ->whereDate('created_at', Carbon::today()) // filter only today's records
            ->selectRaw('SUM(amount) as amounts')
            ->first();

        // If no transaction found, set default value
        self::$transaction = collect([
            'amounts' => $transaction?->amounts ?? 0,
        ]);

        return new self(self::$transaction);
    }

    /**
     * Attach the configured limit from ApiPartnerModeCompany based on filters.
     *
     * @return PaymentBankit
     */
    public static function withLimits($filters = null)
    {
        if (is_null($filters)) {
            $filters = self::$filters;
        }
        $limits = ApiPartnerModeCompany::select("mode_limit", "charges", "c_per_day_limit")
            ->where($filters)
            ->first();

        $return = null;

        if (self::$transaction) {
            self::$transaction->put('mode_limit', $limits?->mode_limit);
            self::$transaction->put('pg_daily_limit', $limits?->c_per_day_limit);
            $return = self::$transaction;
        } else {
            $return = $limits;
        }


        if ($limits) {
            self::$slab = $limits->charges;
        }

        return new self($return);
    }

    /**
     * Attach the configured limit from ApiPartnerModeCompany based on filters.
     *
     * @return PaymentBankit
     */
    public static function withSlab()
    {
        self::$transaction->put('slab', self::$slab);

        return new PaymentBankit(data: self::$transaction);
    }

    /**
     * Attach the configured limit from ApiPartnerModeCompany based on filters.
     *
     * @return PaymentBankit
     */
    public static function getdata(array $filters)
    {
        self::$transaction = Pgtxn::where($filters)->first();

        return new self(data: self::$transaction);
    }

    /**
     * Attach the configured limit from ApiPartnerModeCompany based on filters.
     *
     * @return PaymentBankit
     */
    public static function tnxSuccess(array $data, array $successTxnData)
    {
        try {

            DB::beginTransaction();

            $payment = PaymentTransaction::create($successTxnData);
            Pgtxn::whereTxnno($payment->txnno)->update($data);
            $txn = Pgtxn::whereTxnno($payment->txnno)->first();
            $txn->update(['txnid' => $payment->id]);

            DB::commit();

            return $txn;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * Attach the configured limit from ApiPartnerModeCompany based on filters.
     *
     * @return PaymentBankit
     */
    public static function tnxRefund(array $data, array $refundTxnData, float $balanceAfterRefund)
    {
        try {

            DB::beginTransaction();
            $payment = PaymentTransaction::create($refundTxnData);
            Pgtxn::whereRefid($payment->refid)->update($data);
            $txn = Pgtxn::whereRefid($payment->refid)->first();
            $txn->update(['refundtxnid' => $payment->id]);
            $txn->user->update(['balance' => $balanceAfterRefund]);
            DB::commit();

            return $txn;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Attach the configured limit from ApiPartnerModeCompany based on filters.
     *
     * @return PaymentBankit
     */
    public static function txnError(array $data)
    {
        try {
            $data['status'] = 0;
            Pgtxn::whereTxnno($data['txnno'])->update($data);
            $pgtxn = Pgtxn::whereTxnno($data['txnno'])->first();
            return $pgtxn;
        } catch (\Exception $e) {
            #DB::rollBack();
            throw $e;
        }
    }


    /**
     * Return the transaction data as an array.
     *
     * @return array
     */
    public static function toArray()
    {
        return is_object(self::$transaction) && method_exists(self::$transaction, 'toArray')
            ? self::$transaction->toArray()
            : (array) self::$transaction;
    }
    public static function getAvailableGateway($amount = null, $user_id, $mode_id)
    {
        $companies = ApiPartnerModeCompany::selectRaw('pg_company_id, MIN(c_per_day_limit) as c_per_day_limit')
            ->where(['user_id' => $user_id, 'mode_id' => $mode_id])
            ->groupBy('pg_company_id')
            ->get()
            ->map(function ($company) use ($user_id, $mode_id) {
                $modeLimits = ApiPartnerModeCompany::where([
                    'user_id' => $user_id,
                    'pg_company_id' => $company->pg_company_id,
                    'mode_id' => $mode_id,
                ])->pluck('mode_limit', 'mode_id')->toArray();

                $company->mode_limits = $modeLimits;
                
                $company->apiPgCredentials = $company->company->apiPgCred()->wherePgId($company->pg_company_id)->first();

                return $company;
            });

            

        foreach ($companies as $company) {
            $pgCompanyId = $company->pg_company_id;

            $today_pg_amount = PaymentTransaction::where([
                'user_id' => $user_id,
                'pg_company_id' => $pgCompanyId
            ])
                ->whereDate('created_at', today())
                ->sum('amount');
            
            $today_mode_amount = PaymentTransaction::where([
                'user_id' => $user_id,
                'pg_company_id' => $pgCompanyId,
                'mode_id' => $mode_id
            ])
                ->whereDate('created_at', today())
                ->sum('amount');
                //dd($today_mode_amount + $amount);
            if (
                $today_pg_amount + $amount <= $company->c_per_day_limit &&
                isset($company->mode_limits[$mode_id]) &&
                $today_mode_amount + $amount <= $company->mode_limits[$mode_id]
            ) {
                return $company?->apiPgCredentials ? $company : throw new Exception('Api Partner Credentials Not Found!',28);
            }
        }

        throw new Exception('No Eligible PG Found for Api Partner',13); // no eligible PG found
    }


}
