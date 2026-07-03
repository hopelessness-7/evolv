<?php

namespace App\Modules\Auth\Http\Requests;

use App\Modules\Auth\DTO\Input\LoginData;
use App\Modules\Auth\DTO\Input\RegisterData;
use App\Modules\Shared\Http\Requests\DtoRequest;

class RegisterRequest extends DtoRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function getDto(): RegisterData
    {
        return $this->toDto(RegisterData::class);
    }
}
