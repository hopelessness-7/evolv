<?php

namespace App\Modules\Auth\DTO\Input;

final readonly class LogoutData
{
    public function __construct(
        public ?string $bearerToken,
    ) {}
}
