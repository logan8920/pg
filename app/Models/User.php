<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'firmname',
        'business_name',
        'username',
        'phone',
        'status',
        'balance',
        'created_by',
        'api_partner',
        'datetime'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function unicode() {
        return 'TXN-' . now()->format('YmdHis') . '-' . mt_rand(1000, 9999);
    }

    public function createdBy() {
        return $this->hasOne(User::class, 'id', 'created_by');
    }
    public function apiCredentials() {
        return $this->hasOne(ApiCredentials::class, 'user_id', 'id');
    }

    public function apiConfig() {
        return $this->hasMany(ApiPartnerModeCompany::class, 'user_id', 'id');
    }

    public function checkPartner($partnerId): bool
    {
        return $this->username === $partnerId;
    }

}
