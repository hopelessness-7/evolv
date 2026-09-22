<?php

namespace App\Modules\Practice\Contracts;

use App\Models\User;
use App\Modules\Practice\Enums\AttemptKind;
use App\Modules\Practice\Models\Attempt;

interface AttemptRepositoryInterface
{
    public function save(Attempt $attempt): Attempt;

    public function findLatestForUserAndNode(User $user, int $nodeId): ?Attempt;

    /**
     * @return list<int>
     */
    public function acceptedQuizAtomIds(User $user, int $nodeId): array;

    public function hasAcceptedKind(User $user, int $nodeId, AttemptKind $kind): bool;
}
