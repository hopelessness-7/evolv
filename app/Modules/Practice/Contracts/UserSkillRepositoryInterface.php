<?php

namespace App\Modules\Practice\Contracts;

use App\Models\User;
use App\Modules\Practice\Models\UserSkill;

interface UserSkillRepositoryInterface
{
    public function findForUserAndNode(User $user, int $nodeId): ?UserSkill;

    public function save(UserSkill $skill): UserSkill;
}
