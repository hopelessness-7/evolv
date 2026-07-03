<?php

namespace App\Modules\Practice\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSkill extends Model
{
    protected $fillable = [
        'user_id',
        'node_id',
        'mastery',
        'last_practiced_at',
    ];

    protected function casts(): array
    {
        return [
            'mastery' => 'integer',
            'last_practiced_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
