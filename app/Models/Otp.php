<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $fillable = [
        'otp_for',
        'status',
        'otp',
        'phone'
    ];
}
