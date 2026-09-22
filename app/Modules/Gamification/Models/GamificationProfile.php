<?php

namespace App\Modules\Gamification\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamificationProfile extends Model
{
    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'xp',
        'current_streak',
        'longest_streak',
        'last_active_on',
        'unlocked_pieces',
    ];

    protected function casts(): array
    {
        return [
            'xp' => 'integer',
            'current_streak' => 'integer',
            'longest_streak' => 'integer',
            'last_active_on' => 'date',
            'unlocked_pieces' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
