<?php

namespace App\Modules\Practice\DTO\Output;

use App\Modules\Practice\Enums\AttemptVerdict;
use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class AttemptResultData implements RespondsAsArray
{
    public function __construct(
        public AttemptVerdict $verdict,
        public int $passedTests,
        public int $totalTests,
        public ?string $stdout,
        public ?string $stderr,
        public int $attemptId,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'verdict' => $this->verdict->value,
            'passed_tests' => $this->passedTests,
            'total_tests' => $this->totalTests,
            'stdout' => $this->stdout,
            'stderr' => $this->stderr,
            'attempt_id' => $this->attemptId,
        ];
    }
}
