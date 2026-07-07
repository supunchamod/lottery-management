<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSubAdmin(): bool
    {
        return $this->role === 'sub-admin';
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)
            ->withPivot('is_active')
            ->withTimestamps();
    }

    /**
     * Admins always have every feature. Sub-admins only have features
     * explicitly activated for them via the permission_user pivot.
     */
    public function hasFeature(string $key): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->permissions()
            ->where('key', $key)
            ->wherePivot('is_active', true)
            ->exists();
    }
}
