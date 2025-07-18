<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PgDefaultConfig extends Model
{
    protected $fillable = [
        "pg_company_id",
        "mode_id",
        "c_per_day_limit",
        "mode_limit",
        "charges",
        "created_at",
        "updated_at"
    ];

    protected function casts(): array
    {
        return [
            'charges' => 'array'
        ];
    }

    public function company()  {
        return $this->hasOne(PgCompany::class, 'id','pg_company_id');
    }

    public function mode()  {
        return $this->hasOne(Mode::class, 'id','mode_id');
    }
}
