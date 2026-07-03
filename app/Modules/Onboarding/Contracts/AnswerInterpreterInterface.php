<?php

namespace App\Modules\Onboarding\Contracts;

use App\Modules\Onboarding\DTO\Output\InterpretedAnswersData;
use App\Modules\Onboarding\Models\OnboardingSession;
use App\Modules\Onboarding\Models\Questionnaire;

interface AnswerInterpreterInterface
{
    public function interpret(OnboardingSession $session, Questionnaire $questionnaire): InterpretedAnswersData;
}
