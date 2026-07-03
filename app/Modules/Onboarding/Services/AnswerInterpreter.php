<?php

namespace App\Modules\Onboarding\Services;

use App\Modules\Onboarding\Contracts\AnswerInterpreterInterface;
use App\Modules\Onboarding\Contracts\QuestionnaireAnswerInterpreterInterface;
use App\Modules\Onboarding\DTO\Output\InterpretedAnswersData;
use App\Modules\Onboarding\Models\OnboardingSession;
use App\Modules\Onboarding\Models\Questionnaire;

class AnswerInterpreter implements AnswerInterpreterInterface
{
    /** @var array<string, QuestionnaireAnswerInterpreterInterface> */
    private array $interpretersByCode;

    /**
     * @param  iterable<QuestionnaireAnswerInterpreterInterface>  $interpreters
     */
    public function __construct(iterable $interpreters)
    {
        foreach ($interpreters as $interpreter) {
            $this->interpretersByCode[$interpreter->code()] = $interpreter;
        }
    }

    public function interpret(OnboardingSession $session, Questionnaire $questionnaire): InterpretedAnswersData
    {
        $answers = $session->answers ?? [];
        $interpreter = $this->interpretersByCode[$questionnaire->code] ?? null;

        if ($interpreter !== null) {
            return $interpreter->interpret($answers);
        }

        return new InterpretedAnswersData(
            facets: ['questionnaire_code' => $questionnaire->code, 'raw' => $answers],
            tags: [],
        );
    }
}
