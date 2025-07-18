<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Casts\EncryptedJson;
class UserPgCredential extends Model
{
    use SoftDeletes;

    protected $fillables = [
        "user_id",
        "pg_id",
        "pg_credentials"
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pg_credentials' => EncryptedJson::class
        ];
    }
}
