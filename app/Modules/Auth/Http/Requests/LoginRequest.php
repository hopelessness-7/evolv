<?php

namespace App\Modules\Auth\Http\Requests;

use App\Modules\Auth\DTO\Input\LoginData;
use App\Modules\Shared\Http\Requests\DtoRequest;

class LoginRequest extends DtoRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function getDto(): LoginData
    {
        return $this->toDto(LoginData::class);
    }
}
