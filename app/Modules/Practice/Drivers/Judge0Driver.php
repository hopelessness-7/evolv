<?php

namespace App\Modules\Practice\Drivers;

use App\Modules\Practice\Contracts\CodeExecutionDriverInterface;
use App\Modules\Practice\DTO\ExecutionResultData;
use App\Modules\Practice\Enums\AttemptVerdict;
use Illuminate\Support\Facades\Http;

class Judge0Driver implements CodeExecutionDriverInterface
{
    public function execute(
        string $sourceCode,
        int $languageId,
        ?string $stdin = null,
        ?string $expectedOutput = null,
    ): ExecutionResultData {
        $host = (string) config('judge0.host');
        $timeout = (int) config('judge0.timeout', 30);

        $payload = [
            'source_code' => $sourceCode,
            'language_id' => $languageId,
        ];

        if ($stdin !== null && $stdin !== '') {
            $payload['stdin'] = $stdin;
        }

        if ($expectedOutput !== null) {
            $payload['expected_output'] = $expectedOutput;
        }

        try {
            $request = Http::timeout($timeout)->acceptJson();

            $authToken = config('judge0.auth_token');

            if (is_string($authToken) && $authToken !== '') {
                $request = $request->withToken($authToken);
            }

            $response = $request->post("{$host}/submissions?wait=true", $payload);
        } catch (\Throwable) {
            return new ExecutionResultData(
                verdict: AttemptVerdict::JudgeUnavailable,
            );
        }

        if (! $response->successful()) {
            return new ExecutionResultData(
                verdict: AttemptVerdict::JudgeUnavailable,
            );
        }

        /** @var array<string, mixed> $body */
        $body = $response->json() ?? [];

        $statusId = (int) data_get($body, 'status.id', 0);
        $stdout = $this->nullableString(data_get($body, 'stdout'));
        $stderr = $this->nullableString(data_get($body, 'stderr'))
            ?? $this->nullableString(data_get($body, 'compile_output'));
        $durationMs = $this->durationMs($body);

        return new ExecutionResultData(
            verdict: $this->mapStatusToVerdict($statusId),
            stdout: $stdout,
            stderr: $stderr,
            durationMs: $durationMs,
            judge0Response: $body,
        );
    }

    private function mapStatusToVerdict(int $statusId): AttemptVerdict
    {
        return match ($statusId) {
            3 => AttemptVerdict::Accepted,
            4 => AttemptVerdict::WrongAnswer,
            5 => AttemptVerdict::Timeout,
            6 => AttemptVerdict::CompileError,
            7, 8, 9, 10, 11, 12, 14 => AttemptVerdict::RuntimeError,
            default => AttemptVerdict::JudgeUnavailable,
        };
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function durationMs(array $body): ?int
    {
        $time = data_get($body, 'time');

        if (! is_numeric($time)) {
            return null;
        }

        return (int) round(((float) $time) * 1000);
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = (string) $value;

        return $string === '' ? null : $string;
    }
}
