<?php

namespace App\Modules\Onboarding\Repositories;

use App\Models\User;
use App\Modules\Onboarding\Contracts\SessionRepositoryInterface;
use App\Modules\Onboarding\Enums\SessionStatus;
use App\Modules\Onboarding\Models\OnboardingSession;
use Illuminate\Support\Collection;

class SessionRepository implements SessionRepositoryInterface
{
    public function findByIdForUser(int $id, User $user): ?OnboardingSession
    {
        return OnboardingSession::query()
            ->whereKey($id)
            ->where('user_id', $user->id)
            ->first();
    }

    public function findInProgressForUserAndCode(User $user, string $code): ?OnboardingSession
    {
        return OnboardingSession::query()
            ->where('user_id', $user->id)
            ->where('questionnaire_code', $code)
            ->where('status', SessionStatus::InProgress)
            ->latest('id')
            ->first();
    }

    public function startForUser(User $user, int $questionnaireId, string $questionnaireCode, string $questionnaireVersion): OnboardingSession
    {
        $session = new OnboardingSession([
            'user_id' => $user->id,
            'questionnaire_id' => $questionnaireId,
            'questionnaire_code' => $questionnaireCode,
            'questionnaire_version' => $questionnaireVersion,
            'status' => SessionStatus::InProgress,
            'answers' => [],
        ]);

        $session->save();

        return $session;
    }

    public function save(OnboardingSession $session): OnboardingSession
    {
        $session->save();

        return $session;
    }

    public function listForUser(User $user, ?SessionStatus $status = null): Collection
    {
        return OnboardingSession::query()
            ->where('user_id', $user->id)
            ->when($status, fn ($q) => $q->where('status', $status->value))
            ->orderByDesc('id')
            ->get();
    }

    public function findLatestCoachSystemPrompt(User $user): ?string
    {
        $sessions = OnboardingSession::query()
            ->where('user_id', $user->id)
            ->where('status', SessionStatus::Completed)
            ->whereNotNull('composed_prompts')
            ->orderByDesc('completed_at')
            ->get();

        foreach ($sessions as $session) {
            $prompt = $session->composed_prompts['prompts']['coach_system'] ?? null;

            if (is_string($prompt) && $prompt !== '') {
                return $prompt;
            }
        }

        return null;
    }
}
