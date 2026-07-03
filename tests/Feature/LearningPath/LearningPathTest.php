<?php

namespace Tests\Feature\LearningPath;

use App\Models\User;
use Database\Seeders\ContentSeeder;
use Database\Seeders\CurriculumGraphSeeder;
use Database\Seeders\OnboardingQuestionnaireSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningPathTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(OnboardingQuestionnaireSeeder::class);
        $this->seed(CurriculumGraphSeeder::class);
        $this->seed(ContentSeeder::class);
    }

    public function test_creates_plan_from_curriculum_route(): void
    {
        $headers = $this->authenticatedHeaders();

        $response = $this->getJson('/api/v1/learning-path', $headers)
            ->assertOk()
            ->assertJsonPath('track', 'php')
            ->assertJsonPath('status', 'active');

        $steps = $response->json('steps');
        $this->assertGreaterThan(5, count($steps));
        $this->assertSame('available', $steps[0]['status']);
        $this->assertSame('php.intro', $steps[0]['node']['slug']);
    }

    public function test_current_step_includes_content(): void
    {
        $headers = $this->authenticatedHeaders();

        $this->getJson('/api/v1/learning-path/current-step', $headers)
            ->assertOk()
            ->assertJsonPath('step.node.slug', 'php.intro')
            ->assertJsonPath('content.node.slug', 'php.intro');
    }

    public function test_completing_step_unlocks_next(): void
    {
        $headers = $this->authenticatedHeaders();

        $stepId = $this->getJson('/api/v1/learning-path', $headers)->json('steps.0.id');

        $this->postJson("/api/v1/learning-path/steps/{$stepId}/complete", [], $headers)
            ->assertOk()
            ->assertJsonPath('steps.0.status', 'completed')
            ->assertJsonPath('steps.1.status', 'available');
    }

    public function test_progress_endpoint_returns_percent(): void
    {
        $headers = $this->authenticatedHeaders();

        $this->getJson('/api/v1/learning-path/progress', $headers)
            ->assertOk()
            ->assertJsonStructure(['plan_id', 'total_steps', 'completed_steps', 'percent']);
    }

    /**
     * @return array<string, string>
     */
    private function authenticatedHeaders(): array
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->completeCore($headers);
        $this->completeCraftLite($headers);

        return $headers;
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
     */
    private function completeCraftLite(array $headers): void
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

        $this->postJson("/api/v1/onboarding/sessions/{$sessionId}/complete", [], $headers)->assertOk();
    }
}
