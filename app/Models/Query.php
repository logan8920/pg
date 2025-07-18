<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Query extends Model
{
    protected $fillable = [
        'pgtxn_id',
        'client_request',
        'pg_request',
        'pg_response',
        'client_response'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'client_request' => 'array',
            'pg_request' => 'array',
            'pg_response' => 'array',
            'client_response' => 'array',
        ];
    }

    public function pgtxn()
    {
        return $this->hasOne(Pgtxn::class, 'pgtxn_id', 'id');
    }
}
