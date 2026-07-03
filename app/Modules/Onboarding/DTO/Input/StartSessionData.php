<?php

namespace App\Modules\Onboarding\DTO\Input;

use App\Modules\Shared\Contracts\FromValidated;

final readonly class StartSessionData implements FromValidated
{
    public function __construct(
        public string $questionnaireCode,
        public bool $forceNew,
    ) {}

    public static function fromValidated(array $validated): static
    {
        return new self(
            questionnaireCode: $validated['questionnaire_code'],
            forceNew: (bool) ($validated['force_new'] ?? false),
        );
    }
}
