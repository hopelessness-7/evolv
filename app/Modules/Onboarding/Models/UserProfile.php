<?php

namespace App\Modules\Onboarding\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserProfile extends Model
{
    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'timezone',
        'daily_minutes',
        'enabled_pillars',
        'facets',
        'core_completed_at',
        'onboarding_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'enabled_pillars' => 'array',
            'facets' => 'array',
            'core_completed_at' => 'datetime',
            'onboarding_completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
