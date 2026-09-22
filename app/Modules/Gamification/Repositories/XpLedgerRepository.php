<?php

namespace App\Modules\Gamification\Repositories;

use App\Models\User;
use App\Modules\Gamification\Contracts\XpLedgerRepositoryInterface;
use App\Modules\Gamification\Enums\XpSource;
use App\Modules\Gamification\Models\XpLedgerEntry;

class XpLedgerRepository implements XpLedgerRepositoryInterface
{
    public function append(User $user, XpSource $source, int $amount, ?array $meta = null): XpLedgerEntry
    {
        return XpLedgerEntry::query()->create([
            'user_id' => $user->id,
            'source' => $source,
            'amount' => $amount,
            'meta' => $meta,
        ]);
    }
}
