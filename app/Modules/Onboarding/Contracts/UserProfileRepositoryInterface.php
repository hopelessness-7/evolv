<?php

namespace App\Modules\Onboarding\Contracts;

use App\Models\User;
use App\Modules\Onboarding\Models\UserProfile;

interface UserProfileRepositoryInterface
{
    public function findByUser(User $user): ?UserProfile;

    public function firstOrCreate(User $user): UserProfile;

    public function save(UserProfile $profile): UserProfile;
}
