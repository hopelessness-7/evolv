<?php

namespace App\Modules\Onboarding\Contracts;

use App\Models\User;
use App\Modules\Onboarding\DTO\Output\OnboardingCoachContextData;

interface OnboardingProfileReaderInterface
{
    public function readForCoach(User $user): OnboardingCoachContextData;
}
