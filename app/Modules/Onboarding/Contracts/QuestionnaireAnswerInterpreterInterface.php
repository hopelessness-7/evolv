<?php

namespace App\Modules\Onboarding\Contracts;

use App\Modules\Onboarding\DTO\Output\InterpretedAnswersData;

interface QuestionnaireAnswerInterpreterInterface
{
    public function code(): string;

    /**
     * @param  array<string, mixed>  $answers
     */
    public function interpret(array $answers): InterpretedAnswersData;
}
