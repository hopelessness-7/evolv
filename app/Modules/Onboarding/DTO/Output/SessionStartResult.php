<?php

namespace App\Modules\Onboarding\DTO\Output;

use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class SessionStartResult implements RespondsAsArray
{
    public function __construct(
        public SessionData $session,
        public bool $resumed,
    ) {}

    public function toArray(): array
    {
        return array_merge($this->session->toArray(), [
            'resumed' => $this->resumed,
        ]);
    }
}
