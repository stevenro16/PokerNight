<?php

namespace App\Models;

use App\Traits\HasUuidKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PokerGroup extends Model
{
    use HasUuidKey;

    protected $table = 'poker_groups';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'name',
        'description',
        'owner_id',
        'invite_code',
        'avatar_path',
        'isActive',
    ];

    public function avatarUrl(): ?string
    {
        return $this->avatar_path ? asset('storage/' . $this->avatar_path) : null;
    }

    protected function casts(): array
    {
        return ['isActive' => 'boolean'];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($group) {
            if (empty($group->invite_code)) {
                $group->invite_code = strtoupper(Str::random(8));
            }
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function memberships()
    {
        return $this->hasMany(GroupMember::class, 'group_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'group_members', 'group_id', 'user_id')
                    ->withPivot('role', 'joined_at');
    }

    public function pokerNights()
    {
        return $this->hasMany(PokerNight::class, 'group_id')->orderByDesc('scheduled_at');
    }

    public function players()
    {
        return $this->hasMany(GroupPlayer::class, 'group_id')->orderByRaw("CASE role WHEN 'CORE' THEN 0 ELSE 1 END")->orderBy('name');
    }
}
