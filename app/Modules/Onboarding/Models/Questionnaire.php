<?php

namespace App\Modules\Onboarding\Models;

use App\Modules\Onboarding\Enums\Pillar;
use App\Modules\Onboarding\Enums\Tier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Questionnaire extends Model
{
    protected $table = 'onboarding_questionnaires';

    protected $fillable = [
        'code',
        'version',
        'pillar',
        'tier',
        'title',
        'description',
        'schema',
        'prompt_templates',
        'is_current',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'schema' => 'array',
            'prompt_templates' => 'array',
            'is_current' => 'boolean',
            'published_at' => 'datetime',
            'pillar' => Pillar::class,
            'tier' => Tier::class,
        ];
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(OnboardingSession::class, 'questionnaire_id');
    }
}
