<?php

namespace App\Modules\Coach\Models;

use App\Models\User;
use App\Modules\Coach\Enums\PlanMode;
use App\Modules\Coach\Enums\PlanSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachDailyPlan extends Model
{
    protected $fillable = [
        'user_id',
        'plan_date',
        'mode',
        'source',
        'plan',
    ];

    protected function casts(): array
    {
        return [
            'plan_date' => 'date',
            'mode' => PlanMode::class,
            'source' => PlanSource::class,
            'plan' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
