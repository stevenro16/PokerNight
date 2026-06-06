<?php

namespace App\Models;

use App\Traits\HasUuidKey;
use Illuminate\Database\Eloquent\Model;

class NightComment extends Model
{
    use HasUuidKey;

    protected $table = 'night_comments';

    public $timestamps = false;

    const CREATED_AT = 'createdAt';

    protected $fillable = [
        'poker_night_id',
        'user_id',
        'message',
    ];

    protected function casts(): array
    {
        return ['createdAt' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pokerNight()
    {
        return $this->belongsTo(PokerNight::class, 'poker_night_id');
    }
}
