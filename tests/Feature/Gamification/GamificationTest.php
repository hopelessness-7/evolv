<?php

namespace Tests\Feature\Gamification;

use App\Models\User;
use Database\Seeders\ContentSeeder;
use Database\Seeders\CurriculumGraphSeeder;
use Database\Seeders\OnboardingQuestionnaireSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GamificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(OnboardingQuestionnaireSeeder::class);
        $this->seed(CurriculumGraphSeeder::class);
        $this->seed(ContentSeeder::class);
    }

    public function test_me_returns_zeros_for_new_user(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->getJson('/api/v1/gamification/me', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->assertJsonPath('xp', 0)
            ->assertJsonPath('current_streak', 0)
            ->assertJsonPath('longest_streak', 0)
            ->assertJsonPath('last_active_on', null)
            ->assertJsonPath('unlocked_pieces', []);
    }

    public function test_completing_step_awards_xp_and_updates_streak(): void
    {
        $headers = $this->personalizedHeaders();

        $this->getJson('/api/v1/gamification/me', $headers)
            ->assertOk()
            ->assertJsonPath('xp', 0);

        $stepId = $this->getJson('/api/v1/learning-path', $headers)->json('steps.0.id');

        $this->postJson("/api/v1/learning-path/steps/{$stepId}/complete", [], $headers)
            ->assertOk()
            ->assertJsonPath('steps.0.status', 'completed');

        $this->getJson('/api/v1/gamification/me', $headers)
            ->assertOk()
            ->assertJsonPath('xp', 10)
            ->assertJsonPath('current_streak', 1)
            ->assertJsonPath('longest_streak', 1)
            ->assertJsonPath('last_active_on', now()->toDateString());
    }

    public function test_correct_quiz_awards_xp(): void
    {
        $headers = $this->personalizedHeaders();

        $atoms = $this->getJson('/api/v1/content/nodes/php.intro', $headers)
            ->json('atoms');

        $quiz = collect($atoms)->firstWhere('kind', 'quiz');

        $this->postJson('/api/v1/content/nodes/php.intro/quiz-check', [
            'atom_id' => $quiz['id'],
            'answer' => 'b',
        ], $headers)
            ->assertOk()
            ->assertJsonPath('correct', true);

        $this->getJson('/api/v1/gamification/me', $headers)
            ->assertOk()
            ->assertJsonPath('xp', 5)
            ->assertJsonPath('current_streak', 1);
    }

    /**
     * @return array<string, string>
     */
    private function personalizedHeaders(): array
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
