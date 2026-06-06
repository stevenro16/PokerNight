<?php

namespace App\Models;

use App\Traits\HasUuidKey;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasUuidKey, Notifiable;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'isActive',
        'avatar_url',
    ];

    public function avatarUrl(): ?string
    {
        return $this->avatar_url ? asset('storage/' . $this->avatar_url) : null;
    }

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'isActive' => 'boolean',
            'is_cover'  => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['ADMIN', 'SUPERADMIN']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'SUPERADMIN';
    }

    public function groups()
    {
        return $this->hasManyThrough(PokerGroup::class, GroupMember::class, 'user_id', 'id', 'id', 'group_id');
    }

    public function memberships()
    {
        return $this->hasMany(GroupMember::class, 'user_id');
    }
}
