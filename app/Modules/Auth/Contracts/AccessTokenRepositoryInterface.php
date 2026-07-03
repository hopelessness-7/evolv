<?php

namespace App\Modules\Auth\Contracts;

use App\Models\User;

interface AccessTokenRepositoryInterface
{
    public function issueForUser(User $user, string $name = 'api'): string;

    public function revokeByPlainText(?string $plainTextToken): void;
}
