<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PgCompany extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "name",
        "key_path",
        "pg_config",
        "service_class_name",
        "service_class_path",
        "updated_at",
        'status'
    ];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'key_path' => 'array',
            'pg_config' => 'array'
        ];
    }

    public function defaultConfig()
    {
        return $this->hasMany(PgDefaultConfig::class, 'pg_company_id', 'id');
    }

    public function apiPgCred()
    {
        return $this->hasOne(UserPgCredential::class, 'pg_id', 'id');
    }
}
