<?php

namespace App\Modules\Onboarding\Repositories;

use App\Models\User;
use App\Modules\Onboarding\Contracts\UserProfileRepositoryInterface;
use App\Modules\Onboarding\Models\UserProfile;

class UserProfileRepository implements UserProfileRepositoryInterface
{
    public function findByUser(User $user): ?UserProfile
    {
        return UserProfile::query()->find($user->id);
    }

    public function firstOrCreate(User $user): UserProfile
    {
        return UserProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['timezone' => 'UTC', 'daily_minutes' => 30],
        );
    }

    public function save(UserProfile $profile): UserProfile
    {
        $profile->save();

        return $profile;
    }
}
