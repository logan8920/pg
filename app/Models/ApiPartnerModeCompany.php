<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiPartnerModeCompany extends Model
{
    protected $fillable = [
        "user_id",
        "pg_company_id",
        "mode_id",
        "c_per_day_limit",
        "mode_limit",
        "charges",
        "created_at",
        "updated_at"
    ];

    public function company()
    {
        return $this->hasOne(PgCompany::class, 'id', 'pg_company_id');
    }

    protected function casts(): array
    {
        return [
            'charges' => 'array',
            "c_per_day_limit" => 'float',
            "mode_limit" => 'float',
        ];
    }
}
