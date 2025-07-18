<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = [
        "user_id",
        "txnno",
        "order_id",
        "refid",
        "transfertype",
        "remarks",
        "comment",
        "amount",
        "charge",
        "amt_after_deduction",
        "mode",
        "gateway",
        "previous_balance",
        "balance",
        "gst",
        "mode_id",
        "pg_company_id",
        "utr",
        "dateupdate"
    ];

    public function user() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function refundDetail() {
        return $this->hasOne(Pgtxn::class, "refundtxnid", "id");
    }
}
