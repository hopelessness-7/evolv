<?php

namespace App\Modules\Gamification\Repositories;

use App\Models\User;
use App\Modules\Gamification\Contracts\GamificationProfileRepositoryInterface;
use App\Modules\Gamification\Models\GamificationProfile;

class GamificationProfileRepository implements GamificationProfileRepositoryInterface
{
    public function findForUser(User $user): ?GamificationProfile
    {
        return GamificationProfile::query()->whereKey($user->id)->first();
    }

    public function createForUser(User $user): GamificationProfile
    {
        return GamificationProfile::query()->create([
            'user_id' => $user->id,
            'xp' => 0,
            'current_streak' => 0,
            'longest_streak' => 0,
            'last_active_on' => null,
            'unlocked_pieces' => [],
        ]);
    }

    public function save(GamificationProfile $profile): GamificationProfile
    {
        $profile->save();

        return $profile;
    }
}
