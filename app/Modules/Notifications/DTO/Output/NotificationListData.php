<?php

namespace App\Modules\Notifications\DTO\Output;

use App\Modules\Shared\Contracts\RespondsAsArray;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class NotificationListData implements RespondsAsArray
{
    /**
     * @param  list<NotificationData>  $items
     */
    public function __construct(
        public array $items,
        public int $currentPage,
        public int $perPage,
        public int $total,
        public int $unreadCount,
    ) {}

    public static function fromPaginator(LengthAwarePaginator $paginator, int $unreadCount): self
    {
        return new self(
            items: collect($paginator->items())
                ->map(fn ($item) => NotificationData::fromModel($item))
                ->all(),
            currentPage: $paginator->currentPage(),
            perPage: $paginator->perPage(),
            total: $paginator->total(),
            unreadCount: $unreadCount,
        );
    }

    public function toArray(): array
    {
        return [
            'notifications' => array_map(
                fn (NotificationData $item) => $item->toArray(),
                $this->items,
            ),
            'meta' => [
                'current_page' => $this->currentPage,
                'per_page' => $this->perPage,
                'total' => $this->total,
                'unread_count' => $this->unreadCount,
            ],
        ];
    }
}
