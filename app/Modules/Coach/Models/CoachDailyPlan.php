<?php

namespace App\Modules\Coach\Models;

use App\Models\User;
use App\Modules\Coach\Enums\DailyPlanStatus;
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
        'status',
        'plan',
        'plan_base',
    ];

    protected function casts(): array
    {
        return [
            'plan_date' => 'date',
            'mode' => PlanMode::class,
            'source' => PlanSource::class,
            'status' => DailyPlanStatus::class,
            'plan' => 'array',
            'plan_base' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
