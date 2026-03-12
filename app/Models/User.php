<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;  
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes; 

    protected $fillable = [
    'name',
    'email',
    'password',
    'role',
    'two_factor_type',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'two_factor_confirmed_at',
    'two_factor_otp',
    'two_factor_otp_expires_at',
    'last_login_ip',
];

    protected $hidden = [
        'password',
        'remember_token',
    ];

   protected function casts(): array
    {
        return [
            'email_verified_at'         => 'datetime',
            'password'                  => 'hashed',
            'deleted_at'                => 'datetime',
            'two_factor_recovery_codes' => 'array',
            'two_factor_confirmed_at'   => 'datetime',
            'two_factor_otp_expires_at' => 'datetime',
            'two_factor_secret'         => 'encrypted',
        ];
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Check if user has purchased a specific book
     */
    public function hasPurchased($bookId)
    {
        return $this->orders()
            ->whereHas('orderItems', function ($query) use ($bookId) {
                $query->where('book_id', $bookId);
            })
            ->exists();
    }
    public function hasTwoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_type);
    }
    public function addresses()
    {
        return $this->hasMany(\App\Models\Address::class)->orderByDesc('is_default');
    }
}