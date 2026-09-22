<?php

namespace App\Modules\Journal\DTO\Output;

use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class JournalEntryListData implements RespondsAsArray
{
    /**
     * @param  list<JournalEntryData>  $entries
     */
    public function __construct(
        public array $entries,
        public int $currentPage,
        public int $perPage,
        public int $total,
        public int $lastPage,
    ) {}

    public function toArray(): array
    {
        return [
            'entries' => array_map(
                fn (JournalEntryData $entry) => $entry->toArray(),
                $this->entries,
            ),
            'meta' => [
                'current_page' => $this->currentPage,
                'per_page' => $this->perPage,
                'total' => $this->total,
                'last_page' => $this->lastPage,
            ],
        ];
    }
}
