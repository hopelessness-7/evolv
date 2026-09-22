<?php

namespace App\Modules\Gamification\Contracts;

use App\Models\User;
use App\Modules\Gamification\Models\GamificationProfile;

interface GamificationProfileRepositoryInterface
{
    public function findForUser(User $user): ?GamificationProfile;

    public function createForUser(User $user): GamificationProfile;

    public function save(GamificationProfile $profile): GamificationProfile;
}
