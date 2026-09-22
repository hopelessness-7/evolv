<?php

namespace Tests\Feature\Coach;

use App\Models\User;
use Database\Seeders\OnboardingQuestionnaireSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DailyCheckInTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(OnboardingQuestionnaireSeeder::class);
        Queue::fake();
    }

    public function test_check_in_requires_auth(): void
    {
        $this->postJson('/api/v1/coach/check-ins', [
            'energy' => 3,
            'focus' => 3,
            'practice_ready' => 3,
        ])->assertUnauthorized();
    }

    public function test_store_check_in_adapts_low_load_and_drops_practice(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->seed(\Database\Seeders\CurriculumGraphSeeder::class);
        $this->completeCore($headers);
        $this->completeCraftLite($headers, ['fundamentals']);

        $this->getJson('/api/v1/coach/daily-plan', $headers)
            ->assertOk()
            ->assertJsonPath('check_in', null)
            ->assertJsonPath('steps.0.type', 'check_in');

        $response = $this->postJson('/api/v1/coach/check-ins', [
            'energy' => 1,
            'focus' => 2,
            'practice_ready' => 1,
            'note' => 'tired',
        ], $headers)
            ->assertOk()
            ->assertJsonPath('check_in.load_band', 'low')
            ->assertJsonPath('check_in.energy', 1)
            ->assertJsonPath('daily_plan.check_in.load_band', 'low');

        $steps = collect($response->json('daily_plan.steps'));
        $this->assertNull($steps->firstWhere('type', 'check_in'));
        $this->assertNull($steps->firstWhere('type', 'practice'));
        $this->assertNotNull($steps->firstWhere('type', 'lesson'));

        $this->getJson('/api/v1/coach/daily-plan', $headers)
            ->assertOk()
            ->assertJsonPath('check_in.load_band', 'low')
            ->assertJsonPath('steps.0.type', 'lesson');

        $this->getJson('/api/v1/coach/check-ins', $headers)
            ->assertOk()
            ->assertJsonPath('check_in.focus', 2);
    }

    public function test_updating_check_in_recomputes_from_plan_base(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->seed(\Database\Seeders\CurriculumGraphSeeder::class);
        $this->completeCore($headers);
        $this->completeCraftLite($headers, ['fundamentals']);
        $this->getJson('/api/v1/coach/daily-plan', $headers)->assertOk();

        $this->postJson('/api/v1/coach/check-ins', [
            'energy' => 1,
            'focus' => 1,
            'practice_ready' => 1,
        ], $headers)->assertOk();

        $high = $this->postJson('/api/v1/coach/check-ins', [
            'energy' => 5,
            'focus' => 5,
            'practice_ready' => 5,
        ], $headers)
            ->assertOk()
            ->assertJsonPath('check_in.load_band', 'high');

        $types = collect($high->json('daily_plan.steps'))->pluck('type')->all();
        $this->assertTrue(
            in_array('practice', $types, true) || in_array('quiz_review', $types, true),
            'Expected practice or quiz_review after high check-in, got: '.implode(',', $types),
        );
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

        $this->postJson("/api/v1/onboarding/sessions/{$sessionId}/complete", [], $headers)->assertOk();
    }

    /**
     * @param  array<string, string>  $headers
     * @param  list<string>  $targetTopics
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

        $this->postJson("/api/v1/onboarding/sessions/{$sessionId}/complete", [], $headers)->assertOk();
    }
}
