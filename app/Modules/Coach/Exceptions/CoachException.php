<?php

namespace App\Modules\Coach\Exceptions;

use App\Modules\Shared\Exceptions\ApiException;

class CoachException extends ApiException
{
    public static function invalidPlanDate(string $date): self
    {
        return new self("Invalid plan date [{$date}].", 422, 'invalid_plan_date');
    }
}
