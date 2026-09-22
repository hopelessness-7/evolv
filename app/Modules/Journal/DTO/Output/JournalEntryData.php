<?php

namespace App\Modules\Journal\DTO\Output;

use App\Modules\Journal\Models\JournalEntry;
use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class JournalEntryData implements RespondsAsArray
{
    public function __construct(
        public int $id,
        public string $kind,
        public string $body,
        public ?string $nodeSlug,
        public ?string $planDate,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromModel(JournalEntry $entry): self
    {
        return new self(
            id: $entry->id,
            kind: $entry->kind->value,
            body: $entry->body,
            nodeSlug: $entry->node_slug,
            planDate: $entry->plan_date?->toDateString(),
            createdAt: $entry->created_at?->toIso8601String() ?? '',
            updatedAt: $entry->updated_at?->toIso8601String() ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'kind' => $this->kind,
            'body' => $this->body,
            'node_slug' => $this->nodeSlug,
            'plan_date' => $this->planDate,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
