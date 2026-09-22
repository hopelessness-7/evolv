<?php

namespace Tests\Unit\Practice;

use App\Modules\Practice\Contracts\CodeExecutionDriverInterface;
use App\Modules\Practice\DTO\ExecutionResultData;
use App\Modules\Practice\DTO\ExerciseData;
use App\Modules\Practice\DTO\ExerciseTestData;
use App\Modules\Practice\Enums\AttemptVerdict;
use App\Modules\Practice\Services\ExerciseRunner;
use Mockery;
use Tests\TestCase;

class ExerciseRunnerNormalizeTest extends TestCase
{
    public function test_accepts_stdout_with_trailing_newline(): void
    {
        $driver = Mockery::mock(CodeExecutionDriverInterface::class);
        $driver->shouldReceive('execute')
            ->once()
            ->withArgs(function (string $code, int $langId, ?string $stdin, ?string $expected) {
                return $expected === null;
            })
            ->andReturn(new ExecutionResultData(
                verdict: AttemptVerdict::Accepted,
                stdout: "Hello, Evolv!\n",
                durationMs: 10,
            ));

        $runner = new ExerciseRunner($driver);
        $exercise = new ExerciseData(
            atomId: 1,
            nodeId: 1,
            nodeSlug: 'php.intro',
            language: 'php',
            starterCode: '<?php',
            tests: [
                new ExerciseTestData(label: 'greeting', stdin: '', expectedOutput: 'Hello, Evolv!'),
            ],
        );

        $result = $runner->run($exercise, "<?php echo 'Hello, Evolv!';");

        $this->assertSame(AttemptVerdict::Accepted, $result['verdict']);
        $this->assertSame(1, $result['passed_tests']);
    }

    public function test_wrong_answer_when_output_differs(): void
    {
        $driver = Mockery::mock(CodeExecutionDriverInterface::class);
        $driver->shouldReceive('execute')
            ->once()
            ->andReturn(new ExecutionResultData(
                verdict: AttemptVerdict::Accepted,
                stdout: "nope\n",
                durationMs: 10,
            ));

        $runner = new ExerciseRunner($driver);
        $exercise = new ExerciseData(
            atomId: 1,
            nodeId: 1,
            nodeSlug: 'php.intro',
            language: 'php',
            starterCode: '<?php',
            tests: [
                new ExerciseTestData(label: 'greeting', stdin: '', expectedOutput: 'Hello, Evolv!'),
            ],
        );

        $result = $runner->run($exercise, '<?php echo "nope";');

        $this->assertSame(AttemptVerdict::WrongAnswer, $result['verdict']);
        $this->assertSame(0, $result['passed_tests']);
    }
}
