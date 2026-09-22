<?php

namespace App\Modules\Gamification\Contracts;

use App\Models\User;
use App\Modules\Gamification\DTO\Output\GamificationProfileData;

interface GamificationReaderInterface
{
    public function getProfile(User $user): GamificationProfileData;
}
