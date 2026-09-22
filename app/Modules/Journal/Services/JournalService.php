<?php

namespace App\Modules\Journal\Services;

use App\Models\User;
use App\Modules\Journal\Contracts\JournalEntryRepositoryInterface;
use App\Modules\Journal\DTO\Output\JournalEntryData;
use App\Modules\Journal\DTO\Output\JournalEntryListData;
use App\Modules\Journal\Enums\EntryKind;
use App\Modules\Journal\Exceptions\JournalException;
use App\Modules\Journal\Models\JournalEntry;

class JournalService
{
    public function __construct(
        private readonly JournalEntryRepositoryInterface $entries,
    ) {}

    public function list(
        User $user,
        ?string $nodeSlug = null,
        ?string $planDate = null,
        int $page = 1,
        int $perPage = 10,
    ): JournalEntryListData {
        $paginator = $this->entries->paginateForUser($user, $nodeSlug, $planDate, $page, $perPage);

        $items = collect($paginator->items())
            ->map(fn (JournalEntry $entry) => JournalEntryData::fromModel($entry))
            ->values()
            ->all();

        return new JournalEntryListData(
            entries: $items,
            currentPage: $paginator->currentPage(),
            perPage: $paginator->perPage(),
            total: $paginator->total(),
            lastPage: $paginator->lastPage(),
        );
    }

    public function create(
        User $user,
        EntryKind $kind,
        string $body,
        ?string $nodeSlug = null,
        ?string $planDate = null,
    ): JournalEntryData {
        $entry = new JournalEntry([
            'user_id' => $user->id,
            'kind' => $kind,
            'body' => $body,
            'node_slug' => $nodeSlug,
            'plan_date' => $planDate,
        ]);

        $this->entries->save($entry);

        return JournalEntryData::fromModel($entry->fresh() ?? $entry);
    }

    public function update(
        User $user,
        int $id,
        ?EntryKind $kind = null,
        ?string $body = null,
        ?string $nodeSlug = null,
        ?string $planDate = null,
        bool $clearNodeSlug = false,
        bool $clearPlanDate = false,
    ): JournalEntryData {
        $entry = $this->entries->findForUser($user, $id)
            ?? throw JournalException::notFound($id);

        if ($kind !== null) {
            $entry->kind = $kind;
        }

        if ($body !== null) {
            $entry->body = $body;
        }

        if ($clearNodeSlug) {
            $entry->node_slug = null;
        } elseif ($nodeSlug !== null) {
            $entry->node_slug = $nodeSlug;
        }

        if ($clearPlanDate) {
            $entry->plan_date = null;
        } elseif ($planDate !== null) {
            $entry->plan_date = $planDate;
        }

        $this->entries->save($entry);

        return JournalEntryData::fromModel($entry->fresh() ?? $entry);
    }

    public function delete(User $user, int $id): void
    {
        $entry = $this->entries->findForUser($user, $id)
            ?? throw JournalException::notFound($id);

        $this->entries->delete($entry);
    }
}
