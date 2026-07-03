<?php

namespace Tests\Feature\Practice;

use App\Models\User;
use App\Modules\AI\Services\LlmRouter;
use App\Modules\Practice\Contracts\CodeExecutionDriverInterface;
use App\Modules\Practice\DTO\ExecutionResultData;
use App\Modules\Practice\Enums\AttemptVerdict;
use Database\Seeders\ContentSeeder;
use Database\Seeders\CurriculumGraphSeeder;
use Database\Seeders\OnboardingQuestionnaireSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PracticeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(OnboardingQuestionnaireSeeder::class);
        $this->seed(CurriculumGraphSeeder::class);
        $this->seed(ContentSeeder::class);
    }

    public function test_exercise_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/v1/practice/nodes/php.intro/exercise')
            ->assertUnauthorized();
    }

    public function test_returns_exercise_for_node_with_exercise_atom(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->getJson('/api/v1/practice/nodes/php.intro/exercise', $headers)
            ->assertOk()
            ->assertJsonPath('node_slug', 'php.intro')
            ->assertJsonPath('language', 'php')
            ->assertJsonStructure([
                'atom_id',
                'starter_code',
                'tests' => [['label', 'stdin']],
            ]);
    }

    public function test_submit_accepted_attempt_updates_mastery(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $exercise = $this->getJson('/api/v1/practice/nodes/php.intro/exercise', $headers)
            ->assertOk()
            ->json();

        $driver = \Mockery::mock(CodeExecutionDriverInterface::class);
        $driver->shouldReceive('execute')
            ->once()
            ->andReturn(new ExecutionResultData(
                verdict: AttemptVerdict::Accepted,
                stdout: "Hello, Evolv!\n",
                durationMs: 42,
                judge0Response: ['status' => ['id' => 3]],
            ));

        $this->app->instance(CodeExecutionDriverInterface::class, $driver);
        $this->app->forgetInstance(\App\Modules\Practice\Services\ExerciseRunner::class);
        $this->app->forgetInstance(\App\Modules\Practice\Services\PracticeService::class);

        $this->mock(LlmRouter::class);

        $this->postJson('/api/v1/practice/nodes/php.intro/attempts', [
            'atom_id' => $exercise['atom_id'],
            'code' => "<?php\necho 'Hello, Evolv!';",
        ], $headers)
            ->assertOk()
            ->assertJsonPath('verdict', 'accepted')
            ->assertJsonPath('passed_tests', 1)
            ->assertJsonPath('total_tests', 1);

        $this->assertDatabaseHas('attempts', [
            'user_id' => $user->id,
            'verdict' => 'accepted',
        ]);

        $this->assertDatabaseHas('user_skills', [
            'user_id' => $user->id,
            'mastery' => 15,
        ]);
    }

    public function test_submit_failed_attempt_does_not_update_mastery(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $exercise = $this->getJson('/api/v1/practice/nodes/php.intro/exercise', $headers)->json();

        $driver = \Mockery::mock(CodeExecutionDriverInterface::class);
        $driver->shouldReceive('execute')
            ->once()
            ->andReturn(new ExecutionResultData(
                verdict: AttemptVerdict::WrongAnswer,
                stdout: "wrong\n",
                durationMs: 10,
            ));

        $this->app->instance(CodeExecutionDriverInterface::class, $driver);
        $this->app->forgetInstance(\App\Modules\Practice\Services\ExerciseRunner::class);
        $this->app->forgetInstance(\App\Modules\Practice\Services\PracticeService::class);

        $this->mock(LlmRouter::class, function ($mock): void {
            $mock->shouldReceive('chat')->andReturn(
                new \App\Modules\AI\DTO\LlmResponse(content: '[]', model: 'test'),
            );
        });

        $this->postJson('/api/v1/practice/nodes/php.intro/attempts', [
            'atom_id' => $exercise['atom_id'],
            'code' => "<?php\necho 'wrong';",
        ], $headers)
            ->assertOk()
            ->assertJsonPath('verdict', 'wrong_answer');

        $this->assertDatabaseMissing('user_skills', [
            'user_id' => $user->id,
        ]);
    }
}
