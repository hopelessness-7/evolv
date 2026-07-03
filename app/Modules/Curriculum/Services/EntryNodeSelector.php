<?php

namespace App\Modules\Curriculum\Services;

use App\Models\User;
use App\Modules\Curriculum\Contracts\NodeRepositoryInterface;
use App\Modules\Curriculum\Enums\NodeStatus;
use App\Modules\Curriculum\Enums\Track;
use App\Modules\Curriculum\Models\KnowledgeNode;
use App\Modules\Onboarding\Contracts\OnboardingProfileReaderInterface;
use App\Modules\Shared\Services\PrimaryTrackResolver;

class EntryNodeSelector
{
    public function __construct(
        private readonly OnboardingProfileReaderInterface $onboarding,
        private readonly NodeRepositoryInterface $nodes,
        private readonly PrimaryTrackResolver $primaryTrack,
    ) {}

    /**
     * @return list<KnowledgeNode>
     */
    public function suggest(User $user): array
    {
        $entry = $this->entryNodeForTrack($user, $this->primaryTrack->resolve($user));

        return $entry !== null ? [$entry] : [];
    }

    public function entryNodeForTrack(User $user, Track $track): ?KnowledgeNode
    {
        $difficultyBand = $this->difficultyBandForUser($user);
        $slug = $this->entrySlugForTrack($track, $difficultyBand);

        $node = $this->nodes->findBySlug($slug);

        if ($node !== null && $node->status === NodeStatus::Published) {
            return $node;
        }

        $fallbackSlug = $track->value.'.intro';
        $fallback = $this->nodes->findBySlug($fallbackSlug);

        if ($fallback !== null && $fallback->status === NodeStatus::Published) {
            return $fallback;
        }

        return null;
    }

    public function entrySlugForTrack(Track $track, string $difficultyBand): string
    {
        return $track->value.'.'.$this->entrySuffix($difficultyBand);
    }

    private function difficultyBandForUser(User $user): string
    {
        $context = $this->onboarding->readForCoach($user);
        $craftFacets = $context->profileSummary['facets']['craft_lite'] ?? [];

        return (string) ($craftFacets['difficulty_band'] ?? 'beginner');
    }

    private function entrySuffix(string $difficultyBand): string
    {
        return match ($difficultyBand) {
            'advanced' => 'overview',
            default => 'intro',
        };
    }
}
