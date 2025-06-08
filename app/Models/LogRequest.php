<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogRequest extends Model
{
    protected $fillable = [
        "method",
        "body",
        "request",
        "reqid",
        "partner_id"
    ];
}
