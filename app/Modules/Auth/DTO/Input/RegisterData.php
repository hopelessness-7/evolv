<?php

namespace App\Modules\Auth\DTO\Input;

use App\Modules\Shared\Contracts\FromValidated;

final readonly class RegisterData implements FromValidated
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}

    public static function fromValidated(array $validated): static
    {
        return new self(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'],
        );
    }
}
