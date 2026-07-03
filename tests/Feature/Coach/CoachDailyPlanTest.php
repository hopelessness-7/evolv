<?php

namespace Tests\Feature\Coach;

use App\Models\User;
use App\Modules\AI\DTO\LlmResponse;
use App\Modules\AI\Exceptions\LlmException;
use App\Modules\AI\Services\LlmRouter;
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

    public function test_new_user_gets_simplified_fallback_plan(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->mock(LlmRouter::class, function ($mock): void {
            $mock->shouldReceive('chat')
                ->once()
                ->andThrow(new LlmException('offline'));
        });

        $this->getJson('/api/v1/coach/daily-plan', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->assertJsonPath('mode', 'simplified')
            ->assertJsonPath('source', 'fallback')
            ->assertJsonPath('cached', false)
            ->assertJsonStructure([
                'date',
                'total_minutes',
                'greeting',
                'steps' => [['type', 'title', 'description', 'minutes']],
                'reminders',
            ]);

        $this->assertDatabaseHas('coach_daily_plans', [
            'user_id' => $user->id,
            'mode' => 'simplified',
            'source' => 'fallback',
        ]);
    }

    public function test_daily_plan_is_cached_for_the_same_day(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->mock(LlmRouter::class, function ($mock): void {
            $mock->shouldReceive('chat')
                ->once()
                ->andThrow(new LlmException('offline'));
        });

        $this->getJson('/api/v1/coach/daily-plan', $headers)
            ->assertOk()
            ->assertJsonPath('cached', false);

        $this->getJson('/api/v1/coach/daily-plan', $headers)
            ->assertOk()
            ->assertJsonPath('cached', true);
    }

    public function test_refresh_regenerates_plan(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->mock(LlmRouter::class, function ($mock): void {
            $mock->shouldReceive('chat')
                ->once()
                ->andThrow(new LlmException('offline'));
        });

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

        $this->getJson('/api/v1/coach/daily-plan?refresh=1', $headers)
            ->assertOk()
            ->assertJsonPath('source', 'llm')
            ->assertJsonPath('greeting', 'Refreshed plan')
            ->assertJsonPath('cached', false);
    }

    public function test_personalized_fallback_includes_node_slug(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->seed(\Database\Seeders\CurriculumGraphSeeder::class);

        $this->completeCore($headers);
        $this->completeCraftLite($headers, ['fundamentals']);

        $this->mock(LlmRouter::class, function ($mock): void {
            $mock->shouldReceive('chat')
                ->once()
                ->andThrow(new LlmException('offline'));
        });

        $this->getJson('/api/v1/coach/daily-plan?refresh=1', $headers)
            ->assertOk()
            ->assertJsonPath('mode', 'personalized')
            ->assertJsonPath('steps.0.node_slug', 'php.intro')
            ->assertJsonPath('steps.0.type', 'lesson');
    }

    public function test_personalized_plan_after_craft_lite_complete(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->completeCore($headers);
        $this->completeCraftLite($headers);

        $this->mock(LlmRouter::class, function ($mock): void {
            $mock->shouldReceive('chat')
                ->once()
                ->andReturn(new LlmResponse(
                    content: json_encode([
                        'date' => now()->toDateString(),
                        'mode' => 'personalized',
                        'total_minutes' => 30,
                        'greeting' => 'Personalized day',
                        'steps' => [
                            [
                                'type' => 'lesson',
                                'title' => 'PHP basics',
                                'description' => 'Read lesson',
                                'minutes' => 15,
                                'pillar' => 'craft',
                            ],
                            [
                                'type' => 'practice',
                                'title' => 'Exercise',
                                'description' => 'Solve task',
                                'minutes' => 15,
                                'pillar' => 'craft',
                            ],
                        ],
                        'reminders' => [],
                    ], JSON_THROW_ON_ERROR),
                    model: 'phi3:mini',
                ));
        });

        $this->getJson('/api/v1/coach/daily-plan?refresh=1', $headers)
            ->assertOk()
            ->assertJsonPath('mode', 'personalized')
            ->assertJsonPath('source', 'llm')
            ->assertJsonPath('greeting', 'Personalized day');
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

    public function test_llm_failure_falls_back_to_deterministic_plan(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->mock(LlmRouter::class, function ($mock): void {
            $mock->shouldReceive('chat')
                ->once()
                ->andThrow(new \App\Modules\AI\Exceptions\LlmException('offline'));
        });

        $this->getJson('/api/v1/coach/daily-plan?refresh=1', $headers)
            ->assertOk()
            ->assertJsonPath('source', 'fallback')
            ->assertJsonPath('mode', 'simplified');
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
