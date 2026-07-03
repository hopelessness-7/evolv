<?php

namespace App\Modules\Onboarding\DTO\Output;

use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class AvailableQuestionnaireData implements RespondsAsArray
{
    public function __construct(
        public string $code,
        public string $reason,
        public bool $required,
    ) {}

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'reason' => $this->reason,
            'required' => $this->required,
        ];
    }
}
