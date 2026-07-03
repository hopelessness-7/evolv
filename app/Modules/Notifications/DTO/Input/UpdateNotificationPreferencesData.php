<?php

namespace App\Modules\Notifications\DTO\Input;

use App\Modules\Shared\Contracts\FromValidated;

final readonly class UpdateNotificationPreferencesData implements FromValidated
{
    public function __construct(
        public bool $emailEnabled,
    ) {}

    public static function fromValidated(array $validated): static
    {
        return new self(
            emailEnabled: (bool) $validated['email_enabled'],
        );
    }
}
