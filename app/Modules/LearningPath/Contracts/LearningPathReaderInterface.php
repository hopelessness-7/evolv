<?php

namespace App\Modules\LearningPath\Contracts;

use App\Models\User;

interface LearningPathReaderInterface
{
    /**
     * @return array{id: int, slug: string, title: string}|null
     */
    public function nextAvailableNode(User $user): ?array;
}
