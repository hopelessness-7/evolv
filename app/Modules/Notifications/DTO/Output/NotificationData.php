<?php

namespace App\Modules\Notifications\DTO\Output;

use App\Modules\Notifications\Models\UserNotification;
use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class NotificationData implements RespondsAsArray
{
    public function __construct(
        public int $id,
        public string $type,
        public string $title,
        public string $body,
        public ?array $data,
        public ?string $readAt,
        public ?string $emailedAt,
        public string $createdAt,
    ) {}

    public static function fromModel(UserNotification $notification): self
    {
        return new self(
            id: $notification->id,
            type: $notification->type->value,
            title: $notification->title,
            body: $notification->body,
            data: $notification->data,
            readAt: $notification->read_at?->toIso8601String(),
            emailedAt: $notification->emailed_at?->toIso8601String(),
            createdAt: $notification->created_at->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
            'read_at' => $this->readAt,
            'emailed_at' => $this->emailedAt,
            'created_at' => $this->createdAt,
            'is_read' => $this->readAt !== null,
        ];
    }
}
