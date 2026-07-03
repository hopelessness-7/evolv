<?php

namespace App\Modules\Shared\Exceptions;

use RuntimeException;

class ApiException extends RuntimeException
{
    public function __construct(
        string $message,
        int $status = 400,
        public readonly ?string $error = null,
    ) {
        parent::__construct($message, $status);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = ['message' => $this->getMessage()];

        if ($this->error !== null) {
            $payload['error'] = $this->error;
        }

        return $payload;
    }
}
