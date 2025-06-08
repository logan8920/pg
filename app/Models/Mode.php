<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mode extends Model
{
    protected $fillable = [
        "name",
        "description",
        "created_at",
        "updated_at	"
    ];
}
