<?php

namespace App\Modules\Shared\Services;

use App\Models\User;
use App\Modules\Curriculum\Enums\Track;
use App\Modules\Onboarding\Contracts\OnboardingProfileReaderInterface;
use App\Modules\Onboarding\Contracts\UserProfileRepositoryInterface;

class PrimaryTrackResolver
{
    /**
     * @var array<string, Track>
     */
    private const LANGUAGE_TRACKS = [
        'php' => Track::Php,
        'laravel' => Track::Laravel,
        'sql' => Track::Sql,
        'javascript' => Track::Javascript,
        'python' => Track::Python,
        'go' => Track::Go,
    ];

    public function __construct(
        private readonly OnboardingProfileReaderInterface $onboarding,
        private readonly UserProfileRepositoryInterface $profiles,
    ) {}

    public function resolve(User $user): Track
    {
        $context = $this->onboarding->readForCoach($user);
        $facets = $context->profileSummary['facets'] ?? [];

        $override = is_array($facets) ? ($facets['path']['primary_track'] ?? null) : null;
        if (is_string($override) && $override !== '') {
            $track = Track::tryFrom($override);
            if ($track !== null) {
                return $track;
            }
        }

        $craftFacets = is_array($facets['craft_lite'] ?? null) ? $facets['craft_lite'] : [];
        $targetLanguages = $this->stringList($craftFacets['target_languages'] ?? ['php']);

        foreach ($targetLanguages as $language) {
            if (isset(self::LANGUAGE_TRACKS[$language])) {
                return self::LANGUAGE_TRACKS[$language];
            }
        }

        return Track::Php;
    }

    public function setPrimary(User $user, Track $track): void
    {
        $profile = $this->profiles->firstOrCreate($user);
        $facets = is_array($profile->facets) ? $profile->facets : [];
        $path = is_array($facets['path'] ?? null) ? $facets['path'] : [];
        $path['primary_track'] = $track->value;
        $facets['path'] = $path;
        $profile->facets = $facets;
        $this->profiles->save($profile);
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
