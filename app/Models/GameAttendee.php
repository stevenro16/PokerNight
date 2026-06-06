<?php

namespace App\Models;

use App\Traits\HasUuidKey;
use Illuminate\Database\Eloquent\Model;

class GameAttendee extends Model
{
    use HasUuidKey;

    protected $table = 'game_attendees';

    public $timestamps = false;

    protected $fillable = [
        'poker_night_id',
        'user_id',
        'group_player_id',
        'placement',
        'rsvp',
    ];

    protected function casts(): array
    {
        return ['placement' => 'integer'];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function groupPlayer()
    {
        return $this->belongsTo(GroupPlayer::class, 'group_player_id');
    }

    public function player(): GroupPlayer|User|null
    {
        return $this->groupPlayer ?? $this->user;
    }

    public function pokerNight()
    {
        return $this->belongsTo(PokerNight::class, 'poker_night_id');
    }
}
