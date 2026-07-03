<?php

namespace App\Modules\Auth\Repositories;

use App\Models\User;
use App\Modules\Auth\Contracts\AccessTokenRepositoryInterface;
use Laravel\Sanctum\PersonalAccessToken;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    public function issueForUser(User $user, string $name = 'api'): string
    {
        return $user->createToken($name)->plainTextToken;
    }

    public function revokeByPlainText(?string $plainTextToken): void
    {
        if ($plainTextToken === null) {
            return;
        }

        PersonalAccessToken::findToken($plainTextToken)?->delete();
    }
}
