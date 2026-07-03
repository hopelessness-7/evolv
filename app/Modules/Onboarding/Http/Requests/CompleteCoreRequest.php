<?php

namespace App\Modules\Onboarding\Http\Requests;

use App\Modules\Onboarding\DTO\Input\CompleteCoreData;
use App\Modules\Shared\Http\Requests\DtoRequest;

class CompleteCoreRequest extends DtoRequest
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

    public function getDto(): CompleteCoreData
    {
        return $this->toDto(CompleteCoreData::class);
    }
}
