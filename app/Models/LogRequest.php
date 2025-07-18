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
        "response",
        "partner_id"
    ];

     /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'body' => 'array',
            'request' => 'array',
            "response" => 'array'
        ];
    }


}
