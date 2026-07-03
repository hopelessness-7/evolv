<?php

namespace App\Modules\Practice\Services;

use App\Models\User;
use App\Modules\Practice\Contracts\UserSkillRepositoryInterface;
use App\Modules\Practice\Models\UserSkill;
use Carbon\CarbonImmutable;

class UserSkillUpdater
{
    private const MASTERY_INCREMENT = 15;

    private const MASTERY_CAP = 100;

    public function __construct(
        private readonly UserSkillRepositoryInterface $skills,
    ) {}

    public function recordAcceptedPractice(User $user, int $nodeId): UserSkill
    {
        $skill = $this->skills->findForUserAndNode($user, $nodeId);

        if ($skill === null) {
            $skill = new UserSkill([
                'user_id' => $user->id,
                'node_id' => $nodeId,
                'mastery' => 0,
            ]);
        }

        $skill->mastery = min(self::MASTERY_CAP, (int) $skill->mastery + self::MASTERY_INCREMENT);
        $skill->last_practiced_at = CarbonImmutable::now();

        return $this->skills->save($skill);
    }
}
