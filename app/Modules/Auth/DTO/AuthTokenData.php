<?php

namespace App\Modules\Auth\DTO;

use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class AuthTokenData implements RespondsAsArray
{
    public function __construct(
        public UserData $user,
        public string $token,
        public string $tokenType = 'Bearer',
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user' => $this->user->toArray(),
            'token' => $this->token,
            'token_type' => $this->tokenType,
        ];
    }
}
