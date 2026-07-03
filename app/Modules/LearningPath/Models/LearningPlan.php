<?php

namespace App\Modules\LearningPath\Models;

use App\Models\User;
use App\Modules\LearningPath\Enums\PlanStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningPlan extends Model
{
    protected $fillable = [
        'user_id',
        'track',
        'status',
        'activated_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PlanStatus::class,
            'activated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(LearningPlanStep::class, 'plan_id')->orderBy('order_in_plan');
    }

    public function isActive(): bool
    {
        return $this->status === PlanStatus::Active;
    }
}
