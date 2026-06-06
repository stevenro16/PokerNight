<?php

namespace App\Models;

use App\Traits\HasUuidKey;
use Illuminate\Database\Eloquent\Model;

class GameImage extends Model
{
    use HasUuidKey;

    protected $table = 'game_images';

    public $timestamps = false;

    protected $fillable = [
        'poker_night_id',
        'uploaded_by',
        'file_path',
        'caption',
        'is_cover',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_cover'   => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function pokerNight()
    {
        return $this->belongsTo(PokerNight::class, 'poker_night_id');
    }

    public function url(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
