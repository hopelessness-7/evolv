<?php

namespace App\Modules\Notifications\DTO\Input;

use App\Modules\Notifications\Enums\NotificationType;
use App\Modules\Shared\Contracts\FromValidated;

final readonly class ListNotificationsData implements FromValidated
{
    public function __construct(
        public bool $unreadOnly,
        public int $perPage,
    ) {}

    public static function fromValidated(array $validated): static
    {
        return new self(
            unreadOnly: (bool) ($validated['unread_only'] ?? false),
            perPage: (int) ($validated['per_page'] ?? 20),
        );
    }
}
