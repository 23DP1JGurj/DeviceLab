<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_GUEST = 'guest';
    public const ROLE_CLIENT = 'client';
    public const ROLE_STAFF = 'staff';
    public const ROLE_ADMIN = 'admin';

    public const ROLES = [
        self::ROLE_CLIENT,
        self::ROLE_STAFF,
        self::ROLE_ADMIN,
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'specialization',
        'branch_id',
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
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function assignedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'assigned_staff_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function staffReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'staff_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }
}
