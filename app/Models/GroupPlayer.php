<?php

namespace App\Models;

use App\Traits\HasUuidKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GroupPlayer extends Model
{
    use HasUuidKey;

    protected $table = 'group_players';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'group_id',
        'user_id',
        'name',
        'nickname',
        'photo_path',
        'role',
        'email',
        'invite_token',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($player) {
            if ($player->email && empty($player->invite_token)) {
                $player->invite_token = Str::random(40);
            }
        });
    }

    public function group()
    {
        return $this->belongsTo(PokerGroup::class, 'group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function gameAttendances()
    {
        return $this->hasMany(\App\Models\GameAttendee::class, 'group_player_id');
    }

    public function displayName(): string
    {
        return $this->nickname ?? $this->name;
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }

    public function initial(): string
    {
        return strtoupper(substr($this->displayName(), 0, 1));
    }

    public function isLinked(): bool
    {
        return ! is_null($this->user_id);
    }
}
