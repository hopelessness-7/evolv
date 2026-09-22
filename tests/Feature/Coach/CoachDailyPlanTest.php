<?php

namespace Tests\Feature\Coach;

use App\Models\User;
use App\Modules\AI\DTO\LlmResponse;
use App\Modules\AI\Exceptions\LlmException;
use App\Modules\AI\Services\LlmRouter;
use App\Modules\Coach\Enums\DailyPlanStatus;
use App\Modules\Coach\Jobs\GenerateDailyPlanJob;
use Database\Seeders\OnboardingQuestionnaireSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CoachDailyPlanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(OnboardingQuestionnaireSeeder::class);
        Queue::fake();
    }

    public function test_daily_plan_requires_authentication(): void
    {
        $this->getJson('/api/v1/coach/daily-plan')->assertUnauthorized();
    }

    public function test_new_user_gets_fallback_immediately_and_queues_llm_job(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $response = $this->getJson('/api/v1/coach/daily-plan', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->assertJsonPath('mode', 'simplified')
            ->assertJsonPath('source', 'fallback')
            ->assertJsonPath('status', DailyPlanStatus::Generating->value)
            ->assertJsonPath('cached', false)
            ->assertJsonPath('check_in', null)
            ->assertJsonPath('steps.0.type', 'check_in')
            ->assertJsonPath('steps.1.title', 'Пройти онбординг')
            ->assertJsonStructure([
                'date',
                'total_minutes',
                'greeting',
                'status',
                'message',
                'check_in',
                'steps' => [['type', 'title', 'description', 'minutes']],
                'reminders',
            ]);

        $this->assertStringContainsString('Привет', (string) $response->json('greeting'));
        $this->assertStringContainsString('готовится', (string) $response->json('message'));

        Queue::assertPushed(GenerateDailyPlanJob::class, function (GenerateDailyPlanJob $job) use ($user) {
            return $job->userId === $user->id;
        });

        $this->assertDatabaseHas('coach_daily_plans', [
            'user_id' => $user->id,
            'mode' => 'simplified',
            'source' => 'fallback',
            'status' => DailyPlanStatus::Generating->value,
        ]);
    }

    public function test_daily_plan_is_cached_when_ready_without_refresh(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->getJson('/api/v1/coach/daily-plan', $headers)->assertOk();

        // Simulate job completing with fallback still in place
        \App\Modules\Coach\Models\CoachDailyPlan::query()
            ->where('user_id', $user->id)
            ->update(['status' => DailyPlanStatus::Ready->value]);

        Queue::fake();

        $this->getJson('/api/v1/coach/daily-plan', $headers)
            ->assertOk()
            ->assertJsonPath('cached', true)
            ->assertJsonPath('status', DailyPlanStatus::Ready->value);

        Queue::assertNothingPushed();
    }

    public function test_refresh_marks_generating_and_queues_job(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->getJson('/api/v1/coach/daily-plan', $headers)->assertOk();

        \App\Modules\Coach\Models\CoachDailyPlan::query()
            ->where('user_id', $user->id)
            ->update(['status' => DailyPlanStatus::Ready->value]);

        Queue::fake();

        $this->getJson('/api/v1/coach/daily-plan?refresh=1', $headers)
            ->assertOk()
            ->assertJsonPath('status', DailyPlanStatus::Generating->value)
            ->assertJsonPath('source', 'fallback');

        Queue::assertPushed(GenerateDailyPlanJob::class);
    }

    public function test_generate_job_writes_llm_plan_as_ready(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->getJson('/api/v1/coach/daily-plan', $headers)->assertOk();

        $this->mock(LlmRouter::class, function ($mock): void {
            $mock->shouldReceive('chat')
                ->once()
                ->andReturn(new LlmResponse(
                    content: json_encode([
                        'date' => now()->toDateString(),
                        'mode' => 'simplified',
                        'total_minutes' => 20,
                        'greeting' => 'Refreshed plan',
                        'steps' => [
                            [
                                'type' => 'explore',
                                'title' => 'Refreshed step',
                                'description' => 'Generated again',
                                'minutes' => 20,
                                'pillar' => null,
                            ],
                        ],
                        'reminders' => [],
                    ], JSON_THROW_ON_ERROR),
                    model: 'phi3:mini',
                ));
        });

        (new GenerateDailyPlanJob($user->id, now()->toDateString()))->handle(
            $this->app->make(\App\Modules\Onboarding\Contracts\OnboardingProfileReaderInterface::class),
            $this->app->make(\App\Modules\Coach\Services\DailyPlanGenerator::class),
            $this->app->make(\App\Modules\Coach\Contracts\DailyPlanRepositoryInterface::class),
            $this->app->make(\App\Modules\Coach\Services\CoachService::class),
        );

        $this->getJson('/api/v1/coach/daily-plan', $headers)
            ->assertOk()
            ->assertJsonPath('source', 'llm')
            ->assertJsonPath('greeting', 'Refreshed plan')
            ->assertJsonPath('status', DailyPlanStatus::Ready->value)
            ->assertJsonPath('cached', true);
    }

    public function test_personalized_fallback_includes_node_slug_and_tools(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->seed(\Database\Seeders\CurriculumGraphSeeder::class);

        $this->completeCore($headers);
        $this->completeCraftLite($headers, ['fundamentals']);

        $response = $this->getJson('/api/v1/coach/daily-plan?refresh=1', $headers)
            ->assertOk()
            ->assertJsonPath('mode', 'personalized')
            ->assertJsonPath('steps.0.type', 'check_in')
            ->assertJsonPath('steps.1.node_slug', 'php.intro')
            ->assertJsonPath('steps.1.type', 'lesson');

        $this->assertIsArray($response->json('steps.1.tools'));
        $this->assertNotEmpty($response->json('steps.1.prompts'));

        $reflection = collect($response->json('steps'))->firstWhere('type', 'reflection');
        $this->assertNotNull($reflection);
        $this->assertNotEmpty($reflection['tools']);
        $this->assertCount(3, $reflection['prompts']);
    }

    public function test_generate_job_falls_back_when_llm_fails(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->getJson('/api/v1/coach/daily-plan', $headers)->assertOk();

        $this->mock(LlmRouter::class, function ($mock): void {
            $mock->shouldReceive('chat')
                ->once()
                ->andThrow(new LlmException('offline'));
        });

        (new GenerateDailyPlanJob($user->id, now()->toDateString()))->handle(
            $this->app->make(\App\Modules\Onboarding\Contracts\OnboardingProfileReaderInterface::class),
            $this->app->make(\App\Modules\Coach\Services\DailyPlanGenerator::class),
            $this->app->make(\App\Modules\Coach\Contracts\DailyPlanRepositoryInterface::class),
            $this->app->make(\App\Modules\Coach\Services\CoachService::class),
        );

        $this->getJson('/api/v1/coach/daily-plan', $headers)
            ->assertOk()
            ->assertJsonPath('source', 'fallback')
            ->assertJsonPath('mode', 'simplified')
            ->assertJsonPath('status', DailyPlanStatus::Ready->value);
    }

    public function test_invalid_plan_date_returns_api_error(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->getJson('/api/v1/coach/daily-plan?date=not-a-date', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    /**
     * @param  array<string, string>  $headers
     */
    private function completeCore(array $headers): void
    {
        $sessionId = $this->postJson('/api/v1/onboarding/sessions', [
            'questionnaire_code' => 'core',
        ], $headers)->json('id');

        $this->patchJson("/api/v1/onboarding/sessions/{$sessionId}", [
            'answers' => [
                'display_name' => 'Alex',
                'timezone' => 'Europe/Moscow',
                'interface_language' => 'ru',
                'daily_minutes' => 30,
                'weekly_days' => '4_5',
                'best_time_of_day' => 'morning',
                'enabled_pillars' => ['craft'],
                'primary_motivation' => 'skill_up',
                'coach_tone' => 'direct',
            ],
        ], $headers);

        $this->postJson("/api/v1/onboarding/sessions/{$sessionId}/complete", [], $headers)
            ->assertOk();
    }

    /**
     * @param  array<string, string>  $headers
     */
    private function completeCraftLite(array $headers, array $targetTopics = ['web_backend']): void
    {
        $sessionId = $this->postJson('/api/v1/onboarding/sessions', [
            'questionnaire_code' => 'craft_lite',
        ], $headers)->json('id');

        $this->patchJson("/api/v1/onboarding/sessions/{$sessionId}", [
            'answers' => [
                'experience_level' => 'beginner',
                'years_coding' => 'none',
                'current_stack' => ['none'],
                'target_languages' => ['php'],
                'target_topics' => $targetTopics,
                'learning_goal' => 'curiosity',
                'goal_deadline' => 'none',
                'learning_style' => 'practice_first',
                'session_length' => 'standard_30',
                'code_comfort' => 'never',
                'biggest_blocker' => 'none',
                'prefers_challenges' => 'depends',
            ],
        ], $headers);

        $this->postJson("/api/v1/onboarding/sessions/{$sessionId}/complete", [], $headers)
            ->assertOk();
    }
}
