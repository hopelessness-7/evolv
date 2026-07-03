<?php

namespace App\Modules\Practice\DTO;

use App\Modules\Practice\Enums\AttemptVerdict;

final readonly class ExecutionResultData
{
    /**
     * @param  array<string, mixed>|null  $judge0Response
     */
    public function __construct(
        public AttemptVerdict $verdict,
        public ?string $stdout = null,
        public ?string $stderr = null,
        public ?int $durationMs = null,
        public ?array $judge0Response = null,
    ) {}
}
