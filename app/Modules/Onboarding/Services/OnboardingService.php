<?php

namespace App\Modules\Onboarding\Services;

use App\Models\User;
use App\Modules\Onboarding\Services\ProfileFacetMerger;
use App\Modules\Onboarding\Contracts\AnalyticsRecorderInterface;
use App\Modules\Onboarding\Contracts\AnswerInterpreterInterface;
use App\Modules\Onboarding\Contracts\AnswerSchemaValidatorInterface;
use App\Modules\Onboarding\Contracts\OnboardingProgressEvaluatorInterface;
use App\Modules\Onboarding\Contracts\OnboardingPromptComposerInterface;
use App\Modules\Onboarding\Contracts\QuestionnaireRepositoryInterface;
use App\Modules\Onboarding\Contracts\SessionRepositoryInterface;
use App\Modules\Onboarding\Contracts\UserProfileRepositoryInterface;
use App\Modules\Onboarding\DTO\Input\CompleteCoreData;
use App\Modules\Onboarding\DTO\Input\SaveAnswersData;
use App\Modules\Onboarding\DTO\Input\StartSessionData;
use App\Modules\Onboarding\DTO\Output\OnboardingStatusData;
use App\Modules\Onboarding\DTO\Output\QuestionnaireData;
use App\Modules\Onboarding\DTO\Output\SessionData;
use App\Modules\Onboarding\DTO\Output\SessionStartResult;
use App\Modules\Onboarding\Enums\AnalyticsEvent;
use App\Modules\Onboarding\Enums\Pillar;
use App\Modules\Onboarding\Enums\SessionStatus;
use App\Modules\Onboarding\Enums\Tier;
use App\Modules\Onboarding\Exceptions\OnboardingException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OnboardingService
{
    public function __construct(
        private readonly QuestionnaireRepositoryInterface $questionnaires,
        private readonly SessionRepositoryInterface $sessions,
        private readonly UserProfileRepositoryInterface $profiles,
        private readonly AnalyticsRecorderInterface $analytics,
        private readonly OnboardingProgressEvaluatorInterface $progressEvaluator,
        private readonly AnswerInterpreterInterface $answerInterpreter,
        private readonly OnboardingPromptComposerInterface $promptComposer,
        private readonly ProfileFacetMerger $profileFacetMerger,
        private readonly AnswerSchemaValidatorInterface $answerValidator,
    ) {}

    public function getStatus(User $user): OnboardingStatusData
    {
        $profile = $this->profiles->firstOrCreate($user);
        $sessions = $this->sessions->listForUser($user);

        $status = $this->progressEvaluator->evaluate($user, $profile, $sessions);

        $this->analytics->record(AnalyticsEvent::StatusViewed, $user, null, [
            'phase' => $status->phase,
        ]);

        return $status;
    }

    /** @return Collection<int, QuestionnaireData> */
    public function listQuestionnaires(?Pillar $pillar, ?Tier $tier): Collection
    {
        return $this->questionnaires
            ->listCurrent($pillar, $tier)
            ->map(fn ($q) => QuestionnaireData::fromModel($q));
    }

    public function getQuestionnaire(string $code): QuestionnaireData
    {
        $questionnaire = $this->questionnaires->findCurrentByCode($code)
            ?? throw OnboardingException::questionnaireNotFound($code);

        return QuestionnaireData::fromModel($questionnaire);
    }

    public function startSession(User $user, StartSessionData $data): SessionStartResult
    {
        $questionnaire = $this->questionnaires->findCurrentByCode($data->questionnaireCode)
            ?? throw OnboardingException::questionnaireNotFound($data->questionnaireCode);

        if (! $data->forceNew) {
            $existing = $this->sessions->findInProgressForUserAndCode($user, $data->questionnaireCode);
            if ($existing !== null) {
                $this->analytics->record(AnalyticsEvent::SessionResumed, $user, $existing);

                return new SessionStartResult(
                    session: SessionData::fromModel($existing),
                    resumed: true,
                );
            }
        } else {
            $this->abandonInProgressSessions($user, $data->questionnaireCode);
        }

        $session = $this->sessions->startForUser(
            $user,
            $questionnaire->id,
            $questionnaire->code,
            $questionnaire->version,
        );

        $this->analytics->record(AnalyticsEvent::SessionStarted, $user, $session, [
            'questionnaire_code' => $questionnaire->code,
            'questionnaire_version' => $questionnaire->version,
        ]);

        return new SessionStartResult(
            session: SessionData::fromModel($session),
            resumed: false,
        );
    }

    public function saveAnswers(User $user, int $sessionId, SaveAnswersData $data): SessionData
    {
        $session = $this->sessions->findByIdForUser($sessionId, $user)
            ?? throw OnboardingException::sessionNotFound();

        if ($session->isCompleted()) {
            throw OnboardingException::sessionAlreadyCompleted();
        }

        if (! $session->isInProgress()) {
            throw OnboardingException::sessionNotInProgress();
        }

        $questionnaire = $this->questionnaires->findById($session->questionnaire_id)
            ?? throw OnboardingException::questionnaireNotFound($session->questionnaire_code);

        $mergedAnswers = array_merge($session->answers ?? [], $data->answers);
        $this->answerValidator->validatePatch(
            $questionnaire->schema ?? [],
            $mergedAnswers,
            array_keys($data->answers),
        );

        $session->answers = $mergedAnswers;
        $session = $this->sessions->save($session);

        $this->analytics->record(AnalyticsEvent::AnswersSaved, $user, $session, [
            'answer_keys' => array_keys($data->answers),
        ]);

        return SessionData::fromModel($session);
    }

    public function completeSession(User $user, int $sessionId): SessionData
    {
        return DB::transaction(function () use ($user, $sessionId) {
            $session = $this->sessions->findByIdForUser($sessionId, $user)
                ?? throw OnboardingException::sessionNotFound();

            if ($session->isCompleted()) {
                throw OnboardingException::sessionAlreadyCompleted();
            }

            if (! $session->isInProgress()) {
                throw OnboardingException::sessionNotInProgress();
            }

            $questionnaire = $this->questionnaires->findById($session->questionnaire_id)
                ?? throw OnboardingException::questionnaireNotFound($session->questionnaire_code);

            $this->answerValidator->validateComplete(
                $questionnaire->schema ?? [],
                $session->answers ?? [],
            );

            $profile = $this->profiles->firstOrCreate($user);

            $interpreted = $this->answerInterpreter->interpret($session, $questionnaire);
            $prompts = $this->promptComposer->compose($session, $questionnaire, $interpreted, $profile);

            $session->interpreted = $interpreted->toArray();
            $session->composed_prompts = $prompts->toArray();
            $session->status = SessionStatus::Completed;
            $session->completed_at = now();
            $session = $this->sessions->save($session);

            $profile = $this->profileFacetMerger->merge($profile, $questionnaire->code, $interpreted);

            if ($this->progressEvaluator->evaluate($user, $profile, $this->sessions->listForUser($user))->isComplete) {
                $profile->onboarding_completed_at = now();
            }

            $this->profiles->save($profile);

            $this->analytics->record(AnalyticsEvent::SessionCompleted, $user, $session, [
                'questionnaire_code' => $questionnaire->code,
                'questionnaire_version' => $questionnaire->version,
            ]);

            return SessionData::fromModel($session);
        });
    }

    public function completeCore(User $user, CompleteCoreData $data): SessionData
    {
        $questionnaire = $this->questionnaires->findCurrentByCode('core')
            ?? throw OnboardingException::questionnaireNotFound('core');

        $this->answerValidator->validateComplete($questionnaire->schema ?? [], $data->answers);

        $this->abandonInProgressSessions($user, 'core');

        $session = $this->sessions->startForUser(
            $user,
            $questionnaire->id,
            $questionnaire->code,
            $questionnaire->version,
        );

        $session->answers = $data->answers;
        $session = $this->sessions->save($session);

        $this->analytics->record(AnalyticsEvent::SessionStarted, $user, $session, [
            'questionnaire_code' => $questionnaire->code,
            'questionnaire_version' => $questionnaire->version,
            'via' => 'core_one_shot',
        ]);

        return $this->completeSession($user, $session->id);
    }

    private function abandonInProgressSessions(User $user, string $code): void
    {
        $inProgress = $this->sessions->findInProgressForUserAndCode($user, $code);

        if ($inProgress === null) {
            return;
        }

        $inProgress->status = SessionStatus::Abandoned;
        $this->sessions->save($inProgress);

        $this->analytics->record(AnalyticsEvent::SessionAbandoned, $user, $inProgress);
    }
}
