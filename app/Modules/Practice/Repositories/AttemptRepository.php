<?php

namespace App\Modules\Practice\Repositories;

use App\Models\User;
use App\Modules\Practice\Contracts\AttemptRepositoryInterface;
use App\Modules\Practice\Models\Attempt;

class AttemptRepository implements AttemptRepositoryInterface
{
    public function save(Attempt $attempt): Attempt
    {
        $attempt->save();

        return $attempt;
    }

    public function findLatestForUserAndNode(User $user, int $nodeId): ?Attempt
    {
        return Attempt::query()
            ->where('user_id', $user->id)
            ->where('node_id', $nodeId)
            ->latest('created_at')
            ->first();
    }
}
