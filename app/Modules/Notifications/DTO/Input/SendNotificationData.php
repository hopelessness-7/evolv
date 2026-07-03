<?php

namespace App\Modules\Notifications\DTO\Input;

use App\Modules\Notifications\Enums\NotificationType;

final readonly class SendNotificationData
{
    /**
     * @param  array<string, mixed>|null  $data
     */
    public function __construct(
        public NotificationType $type,
        public string $title,
        public string $body,
        public ?array $data = null,
        public bool $sendEmail = true,
    ) {}
}
