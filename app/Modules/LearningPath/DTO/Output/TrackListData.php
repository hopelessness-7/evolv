<?php

namespace App\Modules\LearningPath\DTO\Output;

use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class TrackListData implements RespondsAsArray
{
    /**
     * @param  list<TrackOptionData>  $tracks
     */
    public function __construct(
        public array $tracks,
    ) {}

    public function toArray(): array
    {
        return [
            'tracks' => array_map(
                fn (TrackOptionData $track) => $track->toArray(),
                $this->tracks,
            ),
        ];
    }
}
