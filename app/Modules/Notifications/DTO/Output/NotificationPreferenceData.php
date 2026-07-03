<?php

namespace App\Modules\Notifications\DTO\Output;

use App\Modules\Notifications\Models\NotificationPreference;
use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class NotificationPreferenceData implements RespondsAsArray
{
    public function __construct(
        public bool $emailEnabled,
    ) {}

    public static function fromModel(NotificationPreference $preference): self
    {
        return new self(
            emailEnabled: $preference->email_enabled,
        );
    }

    public function toArray(): array
    {
        return [
            'email_enabled' => $this->emailEnabled,
        ];
    }
}
