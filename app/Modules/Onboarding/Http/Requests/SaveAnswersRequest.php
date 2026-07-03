<?php

namespace App\Modules\Onboarding\Http\Requests;

use App\Modules\Onboarding\DTO\Input\SaveAnswersData;
use App\Modules\Shared\Http\Requests\DtoRequest;

class SaveAnswersRequest extends DtoRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
        ];
    }

    public function getDto(): SaveAnswersData
    {
        return $this->toDto(SaveAnswersData::class);
    }
}
