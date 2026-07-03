<?php

namespace App\Modules\Onboarding\Exceptions;

use App\Modules\Shared\Exceptions\ApiException;

class OnboardingException extends ApiException
{
    public static function questionnaireNotFound(string $code): self
    {
        return new self("Questionnaire [{$code}] not found.", 404, 'questionnaire_not_found');
    }

    public static function sessionNotFound(): self
    {
        return new self('Onboarding session not found.', 404, 'session_not_found');
    }

    public static function sessionNotInProgress(): self
    {
        return new self('Session is not in progress.', 409, 'session_not_in_progress');
    }

    public static function sessionAlreadyCompleted(): self
    {
        return new self('Session is already completed.', 409, 'session_already_completed');
    }
}
