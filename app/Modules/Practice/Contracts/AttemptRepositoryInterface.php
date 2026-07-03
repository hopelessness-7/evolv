<?php

namespace App\Modules\Practice\Contracts;

use App\Models\User;
use App\Modules\Practice\Models\Attempt;

interface AttemptRepositoryInterface
{
    public function save(Attempt $attempt): Attempt;

    public function findLatestForUserAndNode(User $user, int $nodeId): ?Attempt;
}
