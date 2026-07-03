<?php

namespace App\Modules\Content\DTO\Output;

use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class QuizCheckResultData implements RespondsAsArray
{
    public function __construct(
        public int $atomId,
        public bool $correct,
    ) {}

    public function toArray(): array
    {
        return [
            'atom_id' => $this->atomId,
            'correct' => $this->correct,
        ];
    }
}
