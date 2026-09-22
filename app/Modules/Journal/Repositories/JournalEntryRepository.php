<?php

namespace App\Modules\Journal\Repositories;

use App\Models\User;
use App\Modules\Journal\Contracts\JournalEntryRepositoryInterface;
use App\Modules\Journal\Models\JournalEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class JournalEntryRepository implements JournalEntryRepositoryInterface
{
    public function paginateForUser(
        User $user,
        ?string $nodeSlug = null,
        ?string $planDate = null,
        int $page = 1,
        int $perPage = 10,
    ): LengthAwarePaginator {
        $query = JournalEntry::query()
            ->where('user_id', $user->id)
            ->orderByDesc('id');

        if ($nodeSlug !== null && $nodeSlug !== '') {
            $query->where('node_slug', $nodeSlug);
        }

        if ($planDate !== null && $planDate !== '') {
            $query->whereDate('plan_date', $planDate);
        }

        return $query->paginate(
            perPage: max(1, min(50, $perPage)),
            page: max(1, $page),
        );
    }

    public function findForUser(User $user, int $id): ?JournalEntry
    {
        return JournalEntry::query()
            ->where('user_id', $user->id)
            ->whereKey($id)
            ->first();
    }

    public function save(JournalEntry $entry): JournalEntry
    {
        $entry->save();

        return $entry;
    }

    public function delete(JournalEntry $entry): void
    {
        $entry->delete();
    }
}
