<?php

namespace App\Modules\LearningPath\DTO\Output;

use App\Modules\Curriculum\Enums\Track;
use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class TrackOptionData implements RespondsAsArray
{
    public function __construct(
        public string $track,
        public string $label,
        public bool $hasContent,
    ) {}

    public static function fromTrack(Track $track, bool $hasContent): self
    {
        return new self(
            track: $track->value,
            label: self::labelFor($track),
            hasContent: $hasContent,
        );
    }

    public function toArray(): array
    {
        return [
            'track' => $this->track,
            'label' => $this->label,
            'has_content' => $this->hasContent,
        ];
    }

    private static function labelFor(Track $track): string
    {
        return match ($track) {
            Track::Php => 'PHP',
            Track::Sql => 'SQL',
            Track::Javascript => 'JavaScript',
            Track::Python => 'Python',
            Track::Go => 'Go',
            Track::Algorithms => 'Algorithms',
        };
    }
}
