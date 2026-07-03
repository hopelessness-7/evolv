<?php

namespace App\Modules\Content\DTO\Input;

use App\Modules\Shared\Contracts\FromValidated;

final readonly class CheckQuizData implements FromValidated
{
    public function __construct(
        public int $atomId,
        public string $answer,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            atomId: (int) $validated['atom_id'],
            answer: (string) $validated['answer'],
        );
    }
}
