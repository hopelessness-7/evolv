<?php

namespace App\Modules\Practice\Services;

use App\Modules\Practice\Contracts\CodeExecutionDriverInterface;
use App\Modules\Practice\DTO\ExerciseData;
use App\Modules\Practice\DTO\ExecutionResultData;
use App\Modules\Practice\Enums\AttemptVerdict;

class ExerciseRunner
{
    public function __construct(
        private readonly CodeExecutionDriverInterface $executor,
    ) {}

    /**
     * @return array{
     *     verdict: AttemptVerdict,
     *     passed_tests: int,
     *     total_tests: int,
     *     stdout: ?string,
     *     stderr: ?string,
     *     duration_ms: int,
     *     results: list<ExecutionResultData>
     * }
     */
    public function run(ExerciseData $exercise, string $sourceCode): array
    {
        $languageId = $exercise->languageId();
        $results = [];
        $passed = 0;
        $total = count($exercise->tests);
        $lastStdout = null;
        $lastStderr = null;
        $totalDurationMs = 0;
        $overallVerdict = AttemptVerdict::Accepted;

        foreach ($exercise->tests as $test) {
            $result = $this->executor->execute(
                sourceCode: $sourceCode,
                languageId: $languageId,
                stdin: $test->stdin !== '' ? $test->stdin : null,
                expectedOutput: $test->expectedOutput,
            );

            $results[] = $result;
            $lastStdout = $result->stdout ?? $lastStdout;
            $lastStderr = $result->stderr ?? $lastStderr;
            $totalDurationMs += $result->durationMs ?? 0;

            if ($result->verdict === AttemptVerdict::Accepted) {
                $passed++;
            } elseif ($overallVerdict === AttemptVerdict::Accepted) {
                $overallVerdict = $result->verdict;
            }
        }

        if ($passed < $total && $overallVerdict === AttemptVerdict::Accepted) {
            $overallVerdict = AttemptVerdict::WrongAnswer;
        }

        return [
            'verdict' => $overallVerdict,
            'passed_tests' => $passed,
            'total_tests' => $total,
            'stdout' => $lastStdout,
            'stderr' => $lastStderr,
            'duration_ms' => $totalDurationMs,
            'results' => $results,
        ];
    }
}
