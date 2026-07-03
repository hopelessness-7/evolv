<?php

namespace App\Modules\Onboarding\Models;

use App\Models\User;
use App\Modules\Onboarding\Enums\SessionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnboardingSession extends Model
{
    protected $table = 'onboarding_sessions';

    protected $fillable = [
        'user_id',
        'questionnaire_id',
        'questionnaire_code',
        'questionnaire_version',
        'status',
        'answers',
        'interpreted',
        'composed_prompts',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'interpreted' => 'array',
            'composed_prompts' => 'array',
            'status' => SessionStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function analyticsEvents(): HasMany
    {
        return $this->hasMany(AnalyticsEvent::class, 'session_id');
    }

    public function isInProgress(): bool
    {
        return $this->status === SessionStatus::InProgress;
    }

    public function isCompleted(): bool
    {
        return $this->status === SessionStatus::Completed;
    }
}
