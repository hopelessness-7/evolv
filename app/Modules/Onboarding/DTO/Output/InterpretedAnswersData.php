<?php

namespace App\Modules\Onboarding\DTO\Output;

use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class InterpretedAnswersData implements RespondsAsArray
{
    /**
     * @param  array<string, mixed>  $facets
     * @param  list<string>  $tags
     */
    public function __construct(
        public array $facets,
        public array $tags = [],
    ) {}

    public function toArray(): array
    {
        return [
            'facets' => $this->facets,
            'tags' => $this->tags,
        ];
    }
}
