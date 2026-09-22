<?php

namespace App\Modules\Practice\Repositories;

use App\Models\User;
use App\Modules\Practice\Contracts\AttemptRepositoryInterface;
use App\Modules\Practice\Enums\AttemptKind;
use App\Modules\Practice\Enums\AttemptVerdict;
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

    public function acceptedQuizAtomIds(User $user, int $nodeId): array
    {
        $ids = Attempt::query()
            ->where('user_id', $user->id)
            ->where('node_id', $nodeId)
            ->where('kind', AttemptKind::Quiz)
            ->where('verdict', AttemptVerdict::Accepted)
            ->pluck('payload')
            ->map(function ($payload) {
                $atomId = is_array($payload) ? ($payload['atom_id'] ?? null) : null;

                return is_int($atomId) || is_numeric($atomId) ? (int) $atomId : null;
            })
            ->filter(fn (?int $id) => $id !== null)
            ->unique()
            ->values()
            ->all();

        return array_values($ids);
    }

    public function hasAcceptedKind(User $user, int $nodeId, AttemptKind $kind): bool
    {
        return Attempt::query()
            ->where('user_id', $user->id)
            ->where('node_id', $nodeId)
            ->where('kind', $kind)
            ->where('verdict', AttemptVerdict::Accepted)
            ->exists();
    }
}
