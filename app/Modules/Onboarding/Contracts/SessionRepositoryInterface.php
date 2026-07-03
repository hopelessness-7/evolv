<?php

namespace App\Modules\Onboarding\Contracts;

use App\Models\User;
use App\Modules\Onboarding\Enums\SessionStatus;
use App\Modules\Onboarding\Models\OnboardingSession;
use Illuminate\Support\Collection;

interface SessionRepositoryInterface
{
    public function findByIdForUser(int $id, User $user): ?OnboardingSession;

    public function findInProgressForUserAndCode(User $user, string $code): ?OnboardingSession;

    public function startForUser(User $user, int $questionnaireId, string $questionnaireCode, string $questionnaireVersion): OnboardingSession;

    public function save(OnboardingSession $session): OnboardingSession;

    /** @return Collection<int, OnboardingSession> */
    public function listForUser(User $user, ?SessionStatus $status = null): Collection;

    public function findLatestCoachSystemPrompt(User $user): ?string;
}
