<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    const ROLE_ADMIN = 'admin';

    const ROLE_PENJUAL = 'penjual';

    const ROLE_PEMBELI = 'pembeli';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_blacklisted',
        'blacklist_reason',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_blacklisted' => 'boolean',
        ];
    }

    // --- Role Checks ---
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isPenjual(): bool
    {
        return $this->role === self::ROLE_PENJUAL;
    }

    public function isPembeli(): bool
    {
        return $this->role === self::ROLE_PEMBELI;
    }

    public function canManageProducts(): bool
    {
        return $this->isAdmin() || ($this->isMember() && ! $this->is_blacklisted);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function isMember(): bool
    {
        return in_array($this->role, [self::ROLE_PENJUAL, self::ROLE_PEMBELI]);
    }
}
