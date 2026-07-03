<?php

namespace App\Modules\Onboarding\Services;

use App\Modules\Onboarding\DTO\Output\InterpretedAnswersData;
use App\Modules\Onboarding\Models\UserProfile;

class ProfileFacetMerger
{
    public function merge(UserProfile $profile, string $questionnaireCode, InterpretedAnswersData $interpreted): UserProfile
    {
        $facets = $profile->facets ?? [];
        $facets[$questionnaireCode] = $interpreted->facets;

        $profile->facets = $facets;

        match ($questionnaireCode) {
            'core' => $this->applyCoreToProfile($profile, $interpreted),
            default => null,
        };


        return $profile;
    }

    private function applyCoreToProfile(UserProfile $profile, InterpretedAnswersData $interpreted): void
    {
        $facets = $interpreted->facets;

        if (isset($facets['timezone']) && is_string($facets['timezone'])) {
            $profile->timezone = $facets['timezone'];
        }

        if (isset($facets['daily_minutes'])) {
            $profile->daily_minutes = (int) $facets['daily_minutes'];
        }

        if (isset($facets['enabled_pillars']) && is_array($facets['enabled_pillars'])) {
            $profile->enabled_pillars = array_values($facets['enabled_pillars']);
        }

        $profile->core_completed_at = now();
    }
}
