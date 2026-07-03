<?php

namespace App\Modules\Onboarding\Http\Requests;

use App\Modules\Onboarding\DTO\Input\StartSessionData;
use App\Modules\Shared\Http\Requests\DtoRequest;

class StartSessionRequest extends DtoRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'questionnaire_code' => ['required', 'string', 'max:64'],
            'force_new' => ['sometimes', 'boolean'],
        ];
    }

    public function getDto(): StartSessionData
    {
        return $this->toDto(StartSessionData::class);
    }
}
