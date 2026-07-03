<?php

namespace App\Modules\LearningPath\Models;

use App\Modules\Curriculum\Models\KnowledgeNode;
use App\Modules\LearningPath\Enums\StepStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningPlanStep extends Model
{
    protected $fillable = [
        'plan_id',
        'node_id',
        'order_in_plan',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => StepStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(LearningPlan::class, 'plan_id');
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(KnowledgeNode::class, 'node_id');
    }

    public function isCompletable(): bool
    {
        return in_array($this->status, [StepStatus::Available, StepStatus::InProgress], true);
    }
}
