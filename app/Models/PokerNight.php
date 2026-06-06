<?php

namespace App\Models;

use App\Traits\HasUuidKey;
use Illuminate\Database\Eloquent\Model;

class PokerNight extends Model
{
    use HasUuidKey;

    protected $table = 'poker_nights';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'group_id',
        'created_by',
        'title',
        'notes',
        'scheduled_at',
        'played_at',
        'status',
        'buy_in',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'played_at'    => 'datetime',
            'buy_in'       => 'decimal:2',
        ];
    }

    public function group()
    {
        return $this->belongsTo(PokerGroup::class, 'group_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendees()
    {
        return $this->hasMany(GameAttendee::class, 'poker_night_id')->orderBy('placement');
    }

    public function attendeesWithPlayers()
    {
        return $this->hasMany(GameAttendee::class, 'poker_night_id')
            ->with(['groupPlayer', 'user'])
            ->orderBy('placement');
    }

    public function images()
    {
        return $this->hasMany(GameImage::class, 'poker_night_id')->orderBy('sort_order');
    }

    public function coverImage()
    {
        return $this->hasOne(GameImage::class, 'poker_night_id')->where('is_cover', true);
    }

    public function winner()
    {
        return $this->hasOne(GameAttendee::class, 'poker_night_id')->where('placement', 1);
    }

    public function comments()
    {
        return $this->hasMany(NightComment::class, 'poker_night_id')->orderBy('createdAt');
    }
}
