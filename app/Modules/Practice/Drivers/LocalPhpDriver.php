<?php

namespace App\Modules\Practice\Drivers;

use App\Modules\Practice\Contracts\CodeExecutionDriverInterface;
use App\Modules\Practice\DTO\ExecutionResultData;
use App\Modules\Practice\Enums\AttemptVerdict;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

/**
 * Runs PHP via the Sail/app CLI — used when Judge0 isolate sandbox is unavailable
 * (common on Docker Desktop / WSL). Only suitable for trusted local exercises.
 */
class LocalPhpDriver implements CodeExecutionDriverInterface
{
    public function execute(
        string $sourceCode,
        int $languageId,
        ?string $stdin = null,
        ?string $expectedOutput = null,
    ): ExecutionResultData {
        $phpLanguageId = (int) config('judge0.language_ids.php', 68);

        if ($languageId !== $phpLanguageId) {
            return new ExecutionResultData(
                verdict: AttemptVerdict::JudgeUnavailable,
                stderr: 'LocalPhpDriver supports PHP only.',
            );
        }

        $binary = (string) config('judge0.local_php_binary', 'php');
        $timeout = (float) config('judge0.timeout', 30);
        $tmp = tempnam(sys_get_temp_dir(), 'evolv_php_');

        if ($tmp === false) {
            return new ExecutionResultData(
                verdict: AttemptVerdict::JudgeUnavailable,
                stderr: 'Could not create temp file for local PHP run.',
            );
        }

        $script = $tmp.'.php';
        @unlink($tmp);

        try {
            if (file_put_contents($script, $sourceCode) === false) {
                return new ExecutionResultData(
                    verdict: AttemptVerdict::JudgeUnavailable,
                    stderr: 'Could not write temp PHP script.',
                );
            }

            $process = new Process([$binary, $script]);
            $process->setTimeout($timeout);

            if ($stdin !== null && $stdin !== '') {
                $process->setInput($stdin);
            }

            $started = hrtime(true);

            try {
                $process->run();
            } catch (ProcessTimedOutException) {
                return new ExecutionResultData(
                    verdict: AttemptVerdict::Timeout,
                    stderr: 'Local PHP execution timed out.',
                    durationMs: (int) round((hrtime(true) - $started) / 1_000_000),
                );
            }

            $durationMs = (int) round((hrtime(true) - $started) / 1_000_000);

            $stdout = $process->getOutput();
            $stderr = $process->getErrorOutput();

            if ($process->getExitCode() !== 0) {
                $looksLikeCompile = str_contains($stderr, 'Parse error')
                    || str_contains($stderr, 'Fatal error');

                return new ExecutionResultData(
                    verdict: $looksLikeCompile ? AttemptVerdict::CompileError : AttemptVerdict::RuntimeError,
                    stdout: $stdout !== '' ? $stdout : null,
                    stderr: $stderr !== '' ? $stderr : null,
                    durationMs: $durationMs,
                );
            }

            // Output comparison is done in ExerciseRunner when expectedOutput is null here.
            if ($expectedOutput !== null && $expectedOutput !== '') {
                $ok = $this->normalize($stdout) === $this->normalize($expectedOutput);

                return new ExecutionResultData(
                    verdict: $ok ? AttemptVerdict::Accepted : AttemptVerdict::WrongAnswer,
                    stdout: $stdout !== '' ? $stdout : null,
                    stderr: $stderr !== '' ? $stderr : null,
                    durationMs: $durationMs,
                );
            }

            return new ExecutionResultData(
                verdict: AttemptVerdict::Accepted,
                stdout: $stdout !== '' ? $stdout : null,
                stderr: $stderr !== '' ? $stderr : null,
                durationMs: $durationMs,
            );
        } catch (\Throwable $e) {
            return new ExecutionResultData(
                verdict: AttemptVerdict::JudgeUnavailable,
                stderr: $e->getMessage(),
            );
        } finally {
            @unlink($script);
        }
    }

    private function normalize(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);

        return rtrim($value, " \t\n\0\x0B");
    }
}
