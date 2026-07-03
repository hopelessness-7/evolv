<?php

namespace App\Modules\Onboarding\DTO\Input;

use App\Modules\Shared\Contracts\FromValidated;

final readonly class CompleteCoreData implements FromValidated
{
    /**
     * @param  array<string, mixed>  $answers
     */
    public function __construct(
        public array $answers,
    ) {}

    public static function fromValidated(array $validated): static
    {
        return new self(
            answers: $validated['answers'],
        );
    }
}
