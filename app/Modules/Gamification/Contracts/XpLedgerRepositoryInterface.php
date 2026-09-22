<?php

namespace App\Modules\Gamification\Contracts;

use App\Models\User;
use App\Modules\Gamification\Enums\XpSource;
use App\Modules\Gamification\Models\XpLedgerEntry;

interface XpLedgerRepositoryInterface
{
    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function append(User $user, XpSource $source, int $amount, ?array $meta = null): XpLedgerEntry;
}
