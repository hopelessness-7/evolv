<?php

namespace App\Modules\Practice\Drivers;

use App\Modules\Practice\Contracts\CodeExecutionDriverInterface;
use App\Modules\Practice\DTO\ExecutionResultData;
use App\Modules\Practice\Enums\AttemptVerdict;

/**
 * Prefer Judge0; fall back to LocalPhpDriver for PHP when Judge0 is down or returns Internal Error.
 */
class ResilientCodeExecutionDriver implements CodeExecutionDriverInterface
{
    public function __construct(
        private readonly Judge0Driver $judge0,
        private readonly LocalPhpDriver $localPhp,
    ) {}

    public function execute(
        string $sourceCode,
        int $languageId,
        ?string $stdin = null,
        ?string $expectedOutput = null,
    ): ExecutionResultData {
        $preferred = (string) config('judge0.driver', 'auto');

        if ($preferred === 'local') {
            return $this->localPhp->execute($sourceCode, $languageId, $stdin, $expectedOutput);
        }

        if ($preferred === 'judge0') {
            return $this->judge0->execute($sourceCode, $languageId, $stdin, $expectedOutput);
        }

        $result = $this->judge0->execute($sourceCode, $languageId, $stdin, $expectedOutput);

        if ($this->shouldFallbackToLocal($result, $languageId)) {
            return $this->localPhp->execute($sourceCode, $languageId, $stdin, $expectedOutput);
        }

        return $result;
    }

    private function shouldFallbackToLocal(ExecutionResultData $result, int $languageId): bool
    {
        $phpLanguageId = (int) config('judge0.language_ids.php', 68);

        if ($languageId !== $phpLanguageId) {
            return false;
        }

        if ($result->verdict === AttemptVerdict::JudgeUnavailable) {
            return true;
        }

        $statusId = (int) data_get($result->judge0Response, 'status.id', 0);

        // 13 = Internal Error (isolate/box failures on Docker Desktop / WSL)
        return $statusId === 13;
    }
}
