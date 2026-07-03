<?php

namespace App\Modules\Onboarding\Services;

use App\Models\User;
use App\Modules\Onboarding\Contracts\QuestionnaireSelectorInterface;
use App\Modules\Onboarding\DTO\Output\AvailableQuestionnaireData;
use App\Modules\Onboarding\Models\OnboardingSession;
use App\Modules\Onboarding\Models\UserProfile;
use Illuminate\Support\Collection;

class QuestionnaireSelector implements QuestionnaireSelectorInterface
{
    public function availableFor(User $user, ?UserProfile $profile, Collection $completedSessions): array
    {
        $completedCodes = $completedSessions
            ->map(fn (OnboardingSession $s) => $s->questionnaire_code)
            ->unique()
            ->values()
            ->all();

        $available = [];

        if (! in_array('core', $completedCodes, true)) {
            $available[] = new AvailableQuestionnaireData(
                code: 'core',
                reason: 'Required first step',
                required: true,
            );

            return $available;
        }

        $pillars = $profile?->enabled_pillars ?? ['craft'];

        if (in_array('craft', $pillars, true) && !in_array('craft_lite', $completedCodes, true)) {
            $available[] = new AvailableQuestionnaireData(
                code: 'craft_lite',
                reason: 'Programming goals and experience',
                required: true,
            );
        }

        if (in_array('mind', $pillars, true) && !in_array('mind_lite', $completedCodes, true)) {
            $available[] = new AvailableQuestionnaireData(
                code: 'mind_lite',
                reason: 'Cognitive and wellbeing focus areas',
                required: false,
            );
        }

        if (in_array('presence', $pillars, true) && !in_array('presence_lite', $completedCodes, true)) {
            $available[] = new AvailableQuestionnaireData(
                code: 'presence_lite',
                reason: 'Session format preferences',
                required: false,
            );
        }

        if (in_array('mind_lite', $completedCodes, true)) {
            $this->appendMindExtendedPacks($available, $profile, $completedCodes);
        }

        return $available;
    }

    /**
     * @param  list<AvailableQuestionnaireData>  $available
     * @param  list<string>  $completedCodes
     */
    private function appendMindExtendedPacks(array &$available, ?UserProfile $profile, array $completedCodes): void
    {
        $packs = $profile?->facets['mind_lite']['suggested_extended_packs'] ?? [];

        if (! is_array($packs)) {
            return;
        }

        foreach ($packs as $packCode) {
            if (! is_string($packCode) || in_array($packCode, $completedCodes, true)) {
                continue;
            }

            $available[] = new AvailableQuestionnaireData(
                code: $packCode,
                reason: $this->mindExtendedReason($packCode),
                required: false,
            );
        }
    }

    private function mindExtendedReason(string $packCode): string
    {
        return match ($packCode) {
            'mind_focus' => 'Deep dive into focus',
            'mind_habits' => 'Habits and consistency',
            'mind_cognitive' => 'Memory and cognitive exercises',
            'mind_wellbeing' => 'Stress and self-reflection',
            'mind_rhythm' => 'Energy and sleep rhythm',
            default => 'Extended mind questionnaire',
        };
    }
}
