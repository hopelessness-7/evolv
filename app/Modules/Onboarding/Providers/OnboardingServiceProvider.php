<?php

namespace App\Modules\Onboarding\Providers;

use App\Modules\Onboarding\Contracts\AnalyticsEventRepositoryInterface;
use App\Modules\Onboarding\Contracts\AnalyticsRecorderInterface;
use App\Modules\Onboarding\Contracts\AnswerInterpreterInterface;
use App\Modules\Onboarding\Contracts\AnswerSchemaValidatorInterface;
use App\Modules\Onboarding\Contracts\OnboardingProfileReaderInterface;
use App\Modules\Onboarding\Contracts\OnboardingProgressEvaluatorInterface;
use App\Modules\Onboarding\Contracts\OnboardingPromptComposerInterface;
use App\Modules\Onboarding\Contracts\QuestionnaireRepositoryInterface;
use App\Modules\Onboarding\Contracts\QuestionnaireSelectorInterface;
use App\Modules\Onboarding\Contracts\SessionRepositoryInterface;
use App\Modules\Onboarding\Contracts\UserProfileRepositoryInterface;
use App\Modules\Onboarding\Repositories\AnalyticsEventRepository;
use App\Modules\Onboarding\Repositories\QuestionnaireRepository;
use App\Modules\Onboarding\Repositories\SessionRepository;
use App\Modules\Onboarding\Repositories\UserProfileRepository;
use App\Modules\Onboarding\Services\AnalyticsRecorder;
use App\Modules\Onboarding\Services\AnswerSchemaValidator;
use App\Modules\Onboarding\Services\AnswerInterpreter;
use App\Modules\Onboarding\Services\Answers\CoreAnswerInterpreter;
use App\Modules\Onboarding\Services\Answers\CraftLiteAnswerInterpreter;
use App\Modules\Onboarding\Services\Answers\MindExtendedAnswerInterpreter;
use App\Modules\Onboarding\Services\Answers\MindLiteAnswerInterpreter;
use App\Modules\Onboarding\Services\Answers\PresenceLiteAnswerInterpreter;
use App\Modules\Onboarding\Services\OnboardingProfileReader;
use App\Modules\Onboarding\Services\OnboardingService;
use App\Modules\Onboarding\Services\ProfileFacetMerger;
use App\Modules\Onboarding\Services\ProgressEvaluator;
use App\Modules\Onboarding\Services\PromptComposer;
use App\Modules\Onboarding\Services\QuestionnaireSelector;
use Illuminate\Support\ServiceProvider;

class OnboardingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(QuestionnaireRepositoryInterface::class, QuestionnaireRepository::class);
        $this->app->singleton(SessionRepositoryInterface::class, SessionRepository::class);
        $this->app->singleton(UserProfileRepositoryInterface::class, UserProfileRepository::class);
        $this->app->singleton(AnalyticsEventRepositoryInterface::class, AnalyticsEventRepository::class);
        $this->app->singleton(AnalyticsRecorderInterface::class, AnalyticsRecorder::class);

        $this->app->singleton(QuestionnaireSelectorInterface::class, QuestionnaireSelector::class);
        $this->app->singleton(OnboardingPromptComposerInterface::class, PromptComposer::class);
        $this->app->singleton(OnboardingProgressEvaluatorInterface::class, ProgressEvaluator::class);
        $this->app->singleton(ProfileFacetMerger::class);
        $this->app->singleton(AnswerSchemaValidatorInterface::class, AnswerSchemaValidator::class);

        foreach ($this->answerInterpreterClasses() as $class) {
            $this->app->singleton($class);
        }

        $this->app->singleton(AnswerInterpreterInterface::class, function ($app) {
            $interpreters = array_map(
                fn (string $class) => $app->make($class),
                $this->answerInterpreterClasses(),
            );

            foreach ($this->mindExtendedQuestionnaireCodes() as $code) {
                $interpreters[] = new MindExtendedAnswerInterpreter($code);
            }

            return new AnswerInterpreter($interpreters);
        });

        $this->app->singleton(OnboardingProfileReaderInterface::class, OnboardingProfileReader::class);
        $this->app->singleton(OnboardingService::class);
    }

    /** @return list<class-string> */
    private function answerInterpreterClasses(): array
    {
        return [
            CoreAnswerInterpreter::class,
            CraftLiteAnswerInterpreter::class,
            MindLiteAnswerInterpreter::class,
            PresenceLiteAnswerInterpreter::class,
        ];
    }

    /** @return list<string> */
    private function mindExtendedQuestionnaireCodes(): array
    {
        return [
            'mind_focus',
            'mind_habits',
            'mind_cognitive',
            'mind_wellbeing',
            'mind_rhythm',
        ];
    }
}
