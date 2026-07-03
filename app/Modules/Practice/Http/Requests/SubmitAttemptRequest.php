<?php

namespace App\Modules\Practice\Http\Requests;

use App\Modules\Practice\DTO\Input\SubmitAttemptData;
use App\Modules\Shared\Http\Requests\DtoRequest;

class SubmitAttemptRequest extends DtoRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string'],
            'atom_id' => ['required', 'integer', 'min:1'],
        ];
    }

    public function getDto(): SubmitAttemptData
    {
        return $this->toDto(SubmitAttemptData::class);
    }
}
