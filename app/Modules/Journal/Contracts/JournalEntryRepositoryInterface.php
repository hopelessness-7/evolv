<?php

namespace App\Modules\Journal\Contracts;

use App\Models\User;
use App\Modules\Journal\Models\JournalEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface JournalEntryRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, JournalEntry>
     */
    public function paginateForUser(
        User $user,
        ?string $nodeSlug = null,
        ?string $planDate = null,
        int $page = 1,
        int $perPage = 10,
    ): LengthAwarePaginator;

    public function findForUser(User $user, int $id): ?JournalEntry;

    public function save(JournalEntry $entry): JournalEntry;

    public function delete(JournalEntry $entry): void;
}
