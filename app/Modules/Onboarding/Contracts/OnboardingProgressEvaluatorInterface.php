<?php

namespace App\Modules\Onboarding\Contracts;

use App\Models\User;
use App\Modules\Onboarding\DTO\Output\OnboardingStatusData;
use App\Modules\Onboarding\Models\UserProfile;
use Illuminate\Support\Collection;

interface OnboardingProgressEvaluatorInterface
{
    public function evaluate(User $user, ?UserProfile $profile, Collection $sessions): OnboardingStatusData;
}
