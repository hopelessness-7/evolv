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
            // Run without Judge0 expected_output — compare locally with normalized whitespace.
            $result = $this->executor->execute(
                sourceCode: $sourceCode,
                languageId: $languageId,
                stdin: $test->stdin !== '' ? $test->stdin : null,
                expectedOutput: null,
            );

            if ($result->verdict === AttemptVerdict::Accepted) {
                $expected = $test->expectedOutput;
                if ($expected !== null && $expected !== '' && ! $this->outputsMatch($result->stdout, $expected)) {
                    $result = new ExecutionResultData(
                        verdict: AttemptVerdict::WrongAnswer,
                        stdout: $result->stdout,
                        stderr: $result->stderr,
                        durationMs: $result->durationMs,
                        judge0Response: $result->judge0Response,
                    );
                }
            }

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

    private function outputsMatch(?string $actual, string $expected): bool
    {
        return $this->normalizeOutput($actual ?? '') === $this->normalizeOutput($expected);
    }

    /**
     * Normalize trailing newlines/spaces and CRLF so echo "x" matches expected "x".
     */
    private function normalizeOutput(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);

        return rtrim($value, " \t\n\0\x0B");
    }
}
