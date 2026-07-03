<?php

namespace Tests\Feature\Learn;

use App\Models\User;
use Database\Seeders\ContentSeeder;
use Database\Seeders\CurriculumGraphSeeder;
use Database\Seeders\OnboardingQuestionnaireSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LearnTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        $this->seed(OnboardingQuestionnaireSeeder::class);
        $this->seed(CurriculumGraphSeeder::class);
        $this->seed(ContentSeeder::class);
    }

    public function test_today_requires_authentication(): void
    {
        $this->getJson('/api/v1/learn/today')->assertUnauthorized();
    }

    public function test_today_returns_bff_payload(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->getJson('/api/v1/learn/today', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->assertJsonStructure([
                'onboarding',
                'daily_plan' => ['date', 'mode', 'steps'],
                'progress',
            ]);
    }

    public function test_current_lesson_returns_step_and_content(): void
    {
        $headers = $this->personalizedHeaders();

        $this->getJson('/api/v1/learn/current-lesson', $headers)
            ->assertOk()
            ->assertJsonStructure([
                'lesson' => [
                    'step' => ['id', 'node' => ['slug']],
                    'content' => ['node', 'atoms'],
                ],
            ]);
    }

    /**
     * @return array<string, string>
     */
    private function personalizedHeaders(): array
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $coreId = $this->postJson('/api/v1/onboarding/sessions', ['questionnaire_code' => 'core'], $headers)->json('id');
        $this->patchJson("/api/v1/onboarding/sessions/{$coreId}", [
            'answers' => [
                'display_name' => 'Alex',
                'timezone' => 'UTC',
                'interface_language' => 'en',
                'daily_minutes' => 30,
                'weekly_days' => '4_5',
                'best_time_of_day' => 'morning',
                'enabled_pillars' => ['craft'],
                'primary_motivation' => 'skill_up',
                'coach_tone' => 'direct',
            ],
        ], $headers);
        $this->postJson("/api/v1/onboarding/sessions/{$coreId}/complete", [], $headers)->assertOk();

        $craftId = $this->postJson('/api/v1/onboarding/sessions', ['questionnaire_code' => 'craft_lite'], $headers)->json('id');
        $this->patchJson("/api/v1/onboarding/sessions/{$craftId}", [
            'answers' => [
                'experience_level' => 'beginner',
                'years_coding' => 'none',
                'current_stack' => ['none'],
                'target_languages' => ['php'],
                'target_topics' => ['fundamentals'],
                'learning_goal' => 'curiosity',
                'goal_deadline' => 'none',
                'learning_style' => 'practice_first',
                'session_length' => 'standard_30',
                'code_comfort' => 'never',
                'biggest_blocker' => 'none',
                'prefers_challenges' => 'depends',
            ],
        ], $headers);
        $this->postJson("/api/v1/onboarding/sessions/{$craftId}/complete", [], $headers)->assertOk();

        return $headers;
    }
}
