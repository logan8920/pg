<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\UserService;
use function PHPUnit\Framework\returnArgument;

class Pgtxn extends Model
{
    protected $table = 'pgtxns';

    protected $fillable = [
        'user_id',
        'txnid',
        'txnno',
        'refid',
        'amount',
        'charge',
        'amt_after_deduction',
        'gst',
        'api',
        'mobile',
        'status',
        'email',
        "pg_company_id",
        'mode_id',
        'addeddate',
        'dateadded',
        'dateupdated',
        'refunded',
        'refundtxnid',
        'daterefunded',
        'mode_pg',
        'processby',
        'ipaddress',
        'remarks',
        'refund_remarks',
        'utr',
        'order_id',
        'card',
        'sub_type',
        'name',
        'encdata'
    ];

    public function initiate(array $data)
    {
        $result = [];

        try {

            $transaction = $this->create($data);
            $txnid = $this->generateTxnId($transaction->id);
            $transaction->update(['txnno' => $txnid]);

            $result['status'] = true;
            $result['txnno'] = $txnid;
            $result['modal'] = $transaction;

        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }


        return $result;
    }

    /**
     * Generate a unique transaction ID
     */
    protected function generateTxnId($id = null)
    {
        do {
            // You can use a format like: TXN-<DATE>-<RANDOM>-<ID>
            $txnid = 'TXN-' . now()->format('YmdHis') . '-' . mt_rand(1000, 9999) . ($id ? "-$id" : '');

            // Check if this txnid already exists
            $exists = $this->where('txnid', $txnid)->exists();
        } while ($exists);

        return $txnid;
    }

    public function mode() {
        return $this->hasOne(Mode::class, 'id' , 'mode_id');
    }

    public function paymentGateway() {
        return $this->hasOne(PgCompany::class, 'id' , 'pg_company_id');
    }

    public function successTxnDetails() {
        return $this->hasOne(PaymentTransaction::class, 'id' , 'txnid');
    }

    public function user() {
        return $this->hasOne(User::class, 'id' , 'user_id');
    }

}
