<?php

namespace App\Modules\Onboarding\Contracts;

use App\Models\User;
use App\Modules\Onboarding\DTO\Output\AvailableQuestionnaireData;
use App\Modules\Onboarding\DTO\Output\OnboardingStatusData;
use App\Modules\Onboarding\Models\UserProfile;
use Illuminate\Support\Collection;

interface QuestionnaireSelectorInterface
{
    /** @return list<AvailableQuestionnaireData> */
    public function availableFor(User $user, ?UserProfile $profile, Collection $completedSessions): array;
}
