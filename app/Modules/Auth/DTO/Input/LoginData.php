<?php

namespace App\Modules\Auth\DTO\Input;

use App\Modules\Shared\Contracts\FromValidated;

final readonly class LoginData implements FromValidated
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}

    public static function fromValidated(array $validated): static
    {
        return new self(
            email: $validated['email'],
            password: $validated['password'],
        );
    }
}
