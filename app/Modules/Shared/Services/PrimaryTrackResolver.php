<?php

namespace App\Modules\Shared\Services;

use App\Models\User;
use App\Modules\Curriculum\Enums\Track;
use App\Modules\Onboarding\Contracts\OnboardingProfileReaderInterface;

class PrimaryTrackResolver
{
    /**
     * @var array<string, Track>
     */
    private const LANGUAGE_TRACKS = [
        'php' => Track::Php,
        'sql' => Track::Sql,
        'javascript' => Track::Javascript,
        'python' => Track::Python,
        'go' => Track::Go,
    ];

    public function __construct(
        private readonly OnboardingProfileReaderInterface $onboarding,
    ) {}

    public function resolve(User $user): Track
    {
        $context = $this->onboarding->readForCoach($user);
        $craftFacets = $context->profileSummary['facets']['craft_lite'] ?? [];
        $targetLanguages = $this->stringList($craftFacets['target_languages'] ?? ['php']);

        foreach ($targetLanguages as $language) {
            if (isset(self::LANGUAGE_TRACKS[$language])) {
                return self::LANGUAGE_TRACKS[$language];
            }
        }

        return Track::Php;
    }

    /**
     * @return list<string>
     */
    private function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(
            array_map(strval(...), $value),
            fn (string $item) => $item !== '' && $item !== 'none',
        ));
    }
}
