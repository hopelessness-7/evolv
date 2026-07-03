<?php

namespace App\Modules\Onboarding\Contracts;

use App\Modules\Onboarding\DTO\Output\ComposedPromptsData;
use App\Modules\Onboarding\DTO\Output\InterpretedAnswersData;
use App\Modules\Onboarding\Models\OnboardingSession;
use App\Modules\Onboarding\Models\Questionnaire;
use App\Modules\Onboarding\Models\UserProfile;

interface OnboardingPromptComposerInterface
{
    public function compose(OnboardingSession $session, Questionnaire $questionnaire, InterpretedAnswersData $interpreted, ?UserProfile $profile = null): ComposedPromptsData;
}
