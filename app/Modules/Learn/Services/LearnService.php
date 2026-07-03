<?php

namespace App\Modules\Learn\Services;

use App\Models\User;
use App\Modules\Coach\DTO\Input\GetDailyPlanData;
use App\Modules\Coach\Services\CoachService;
use App\Modules\Learn\DTO\Output\CurrentLessonData;
use App\Modules\Learn\DTO\Output\TodayData;
use App\Modules\LearningPath\DTO\Output\PathProgressData;
use App\Modules\LearningPath\Exceptions\LearningPathException;
use App\Modules\LearningPath\Services\LearningPathService;
use App\Modules\Onboarding\Services\OnboardingService;

class LearnService
{
    public function __construct(
        private readonly OnboardingService $onboarding,
        private readonly CoachService $coach,
        private readonly LearningPathService $learningPath,
    ) {}

    public function getToday(User $user): TodayData
    {
        return new TodayData(
            onboarding: $this->onboarding->getStatus($user),
            dailyPlan: $this->coach->getDailyPlan($user, new GetDailyPlanData(date: null, refresh: false)),
            progress: $this->safeProgress($user),
        );
    }

    private function safeProgress(User $user): ?PathProgressData
    {
        try {
            return $this->learningPath->getProgress($user);
        } catch (LearningPathException) {
            return null;
        }
    }

    public function getCurrentLesson(User $user): CurrentLessonData
    {
        return new CurrentLessonData(
            lesson: $this->learningPath->getCurrentStep($user, withContent: true),
        );
    }
}
