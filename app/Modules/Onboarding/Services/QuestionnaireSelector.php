<?php

namespace App\Modules\Onboarding\Services;

use App\Models\User;
use App\Modules\Onboarding\Contracts\QuestionnaireSelectorInterface;
use App\Modules\Onboarding\DTO\Output\AvailableQuestionnaireData;
use App\Modules\Onboarding\Models\OnboardingSession;
use App\Modules\Onboarding\Models\UserProfile;
use App\Modules\Shared\Support\InterfaceLanguage;
use Illuminate\Support\Collection;

class QuestionnaireSelector implements QuestionnaireSelectorInterface
{
    /** @var array<string, array{ru: string, en: string}> */
    private const REASONS = [
        'core' => [
            'ru' => 'Обязательный первый шаг — базовый профиль',
            'en' => 'Required first step',
        ],
        'craft_lite' => [
            'ru' => 'Цели и опыт в программировании',
            'en' => 'Programming goals and experience',
        ],
        'mind_lite' => [
            'ru' => 'Фокус и wellbeing',
            'en' => 'Cognitive and wellbeing focus areas',
        ],
        'presence_lite' => [
            'ru' => 'Предпочтения формата занятий',
            'en' => 'Session format preferences',
        ],
        'mind_focus' => [
            'ru' => 'Углублённо про фокус',
            'en' => 'Deep dive into focus',
        ],
        'mind_habits' => [
            'ru' => 'Привычки и регулярность',
            'en' => 'Habits and consistency',
        ],
        'mind_cognitive' => [
            'ru' => 'Память и когнитивные упражнения',
            'en' => 'Memory and cognitive exercises',
        ],
        'mind_wellbeing' => [
            'ru' => 'Стресс и саморефлексия',
            'en' => 'Stress and self-reflection',
        ],
        'mind_rhythm' => [
            'ru' => 'Энергия и ритм сна',
            'en' => 'Energy and sleep rhythm',
        ],
        'mind_extended' => [
            'ru' => 'Расширенная анкета Mind',
            'en' => 'Extended mind questionnaire',
        ],
    ];

    public function availableFor(User $user, ?UserProfile $profile, Collection $completedSessions): array
    {
        $lang = InterfaceLanguage::fromFacets($profile?->facets);
        $completedCodes = $completedSessions
            ->map(fn (OnboardingSession $s) => $s->questionnaire_code)
            ->unique()
            ->values()
            ->all();

        $available = [];

        if (! in_array('core', $completedCodes, true)) {
            $available[] = new AvailableQuestionnaireData(
                code: 'core',
                reason: $this->reason('core', $lang),
                required: true,
            );

            return $available;
        }

        $pillars = $profile?->enabled_pillars ?? ['craft'];

        if (in_array('craft', $pillars, true) && ! in_array('craft_lite', $completedCodes, true)) {
            $available[] = new AvailableQuestionnaireData(
                code: 'craft_lite',
                reason: $this->reason('craft_lite', $lang),
                required: true,
            );
        }

        if (in_array('mind', $pillars, true) && ! in_array('mind_lite', $completedCodes, true)) {
            $available[] = new AvailableQuestionnaireData(
                code: 'mind_lite',
                reason: $this->reason('mind_lite', $lang),
                required: false,
            );
        }

        if (in_array('presence', $pillars, true) && ! in_array('presence_lite', $completedCodes, true)) {
            $available[] = new AvailableQuestionnaireData(
                code: 'presence_lite',
                reason: $this->reason('presence_lite', $lang),
                required: false,
            );
        }

        if (in_array('mind_lite', $completedCodes, true)) {
            $this->appendMindExtendedPacks($available, $profile, $completedCodes, $lang);
        }

        return $available;
    }

    /**
     * @param  list<AvailableQuestionnaireData>  $available
     * @param  list<string>  $completedCodes
     */
    private function appendMindExtendedPacks(
        array &$available,
        ?UserProfile $profile,
        array $completedCodes,
        string $lang,
    ): void {
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
                reason: $this->reason(
                    array_key_exists($packCode, self::REASONS) ? $packCode : 'mind_extended',
                    $lang,
                ),
                required: false,
            );
        }
    }

    private function reason(string $key, string $lang): string
    {
        return InterfaceLanguage::pick($lang, self::REASONS, $key, self::REASONS['mind_extended']['ru']);
    }
}
