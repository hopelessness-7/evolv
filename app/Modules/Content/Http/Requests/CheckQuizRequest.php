<?php

namespace App\Modules\Content\Http\Requests;

use App\Modules\Content\DTO\Input\CheckQuizData;
use App\Modules\Shared\Http\Requests\DtoRequest;

class CheckQuizRequest extends DtoRequest
{
    public function rules(): array
    {
        return [
            'atom_id' => ['required', 'integer', 'min:1'],
            'answer' => ['required', 'string', 'max:1000'],
        ];
    }

    public function getDto(): CheckQuizData
    {
        return $this->toDto(CheckQuizData::class);
    }
}
