<?php

namespace App\Modules\Practice\Repositories;

use App\Models\User;
use App\Modules\Practice\Contracts\UserSkillRepositoryInterface;
use App\Modules\Practice\Models\UserSkill;

class UserSkillRepository implements UserSkillRepositoryInterface
{
    public function findForUserAndNode(User $user, int $nodeId): ?UserSkill
    {
        return UserSkill::query()
            ->where('user_id', $user->id)
            ->where('node_id', $nodeId)
            ->first();
    }

    public function save(UserSkill $skill): UserSkill
    {
        $skill->save();

        return $skill;
    }
}
