<?php

namespace App\Libraries;

use App\Models\PaymentTransaction;
use App\Models\Pgtxn;
use App\Models\ApiPartnerModeCompany;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use DB;
use function PHPUnit\Framework\returnArgument;

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
    public static function withLimits()
    {
        $limits = ApiPartnerModeCompany::select("mode_limit", "charges", "c_per_day_limit")
            ->where(self::$filters)
            ->first();
        self::$transaction->put('mode_limit', $limits?->mode_limit);
        self::$transaction->put('pg_daily_limit', $limits?->c_per_day_limit);

        if ($limits) {
            self::$slab = $limits->charges;
        }

        return new self(self::$transaction);
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
}
