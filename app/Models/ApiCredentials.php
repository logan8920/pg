<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiCredentials extends Model
{
    protected $fillable = [
        'user_id',
        'ipaddress',
        'status',
        'key',
        'iv',
        'date_added',
        'added_date',
        'token',
        'pg',
        'remarks',
        'created_at',
        'updated_at',
        'deleted_at'
    ];


    // protected function casts(): array
    // {
    //     return [
    //         'ipaddress' => 'json'
    //     ];
    // }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function checkKey($key): bool
    {
        return $this->key === $key;
    }
}
