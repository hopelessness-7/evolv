<?php

namespace App\Modules\Gamification\Contracts;

use App\Models\User;
use App\Modules\Gamification\DTO\Output\GamificationProfileData;
use App\Modules\Gamification\Enums\XpSource;

interface GamificationRecorderInterface
{
    /**
     * @param  array<string, mixed>  $meta
     * @param  list<string>  $puzzlePieces
     */
    public function awardXp(
        User $user,
        XpSource $source,
        array $meta = [],
        array $puzzlePieces = [],
    ): GamificationProfileData;
}
