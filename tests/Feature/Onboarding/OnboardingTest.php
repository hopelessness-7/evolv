<?php

namespace Tests\Feature\Onboarding;

use App\Models\User;
use Database\Seeders\OnboardingQuestionnaireSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(OnboardingQuestionnaireSeeder::class);
    }

    public function test_status_requires_authentication(): void
    {
        $this->getJson('/api/v1/onboarding/status')->assertUnauthorized();
    }

    public function test_new_user_sees_core_questionnaire_as_pending(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->getJson('/api/v1/onboarding/status', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->assertJsonPath('phase', 'core_pending')
            ->assertJsonPath('is_complete', false)
            ->assertJsonPath('available_questionnaires.0.code', 'core')
            ->assertJsonPath('available_questionnaires.0.required', true);
    }

    public function test_session_flow_does_not_overwrite_completed_sessions(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $start = $this->postJson('/api/v1/onboarding/sessions', [
            'questionnaire_code' => 'core',
        ], $headers)->assertCreated();

        $sessionId = $start->json('id');

        $this->postJson('/api/v1/onboarding/sessions', [
            'questionnaire_code' => 'core',
        ], $headers)
            ->assertOk()
            ->assertJsonPath('id', $sessionId)
            ->assertJsonPath('resumed', true);

        $this->patchJson("/api/v1/onboarding/sessions/{$sessionId}", [
            'answers' => $this->coreAnswers([
                'timezone' => 'Europe/Moscow',
                'daily_minutes' => 45,
                'enabled_pillars' => ['craft'],
            ]),
        ], $headers)->assertOk();

        $complete = $this->postJson("/api/v1/onboarding/sessions/{$sessionId}/complete", [], $headers)
            ->assertOk()
            ->assertJsonPath('status', 'completed');

        $this->assertDatabaseHas('onboarding_sessions', [
            'id' => $sessionId,
            'status' => 'completed',
            'questionnaire_code' => 'core',
        ]);

        $this->postJson("/api/v1/onboarding/sessions/{$sessionId}/complete", [], $headers)
            ->assertStatus(409)
            ->assertJson([
                'message' => 'Session is already completed.',
                'error' => 'session_already_completed',
            ]);

        $secondStart = $this->postJson('/api/v1/onboarding/sessions', [
            'questionnaire_code' => 'core',
            'force_new' => true,
        ], $headers)->assertCreated();

        $secondSessionId = $secondStart->json('id');
        $this->assertNotSame($sessionId, $secondSessionId);

        $this->assertDatabaseHas('onboarding_sessions', [
            'id' => $sessionId,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('onboarding_sessions', [
            'id' => $secondSessionId,
            'status' => 'in_progress',
        ]);

        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'timezone' => 'Europe/Moscow',
            'daily_minutes' => 45,
        ]);

        $this->getJson('/api/v1/onboarding/status', $headers)
            ->assertOk()
            ->assertJsonPath('phase', 'pillar_lite')
            ->assertJsonPath('available_questionnaires.0.code', 'craft_lite');

        $this->assertNotNull($complete->json('interpreted'));
        $this->assertNotNull($complete->json('composed_prompts'));
    }

    public function test_core_complete_renders_prompts_without_placeholders(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $sessionId = $this->postJson('/api/v1/onboarding/sessions', [
            'questionnaire_code' => 'core',
        ], $headers)->json('id');

        $this->patchJson("/api/v1/onboarding/sessions/{$sessionId}", [
            'answers' => $this->coreAnswers([
                'display_name' => 'Alex',
                'enabled_pillars' => ['craft'],
            ]),
        ], $headers);

        $response = $this->postJson("/api/v1/onboarding/sessions/{$sessionId}/complete", [], $headers)
            ->assertOk();

        $coachSystem = $response->json('composed_prompts.prompts.coach_system');
        $this->assertIsString($coachSystem);
        $this->assertStringNotContainsString('{{', $coachSystem);
        $this->assertStringContainsString('Alex', $coachSystem);
    }

    public function test_mind_only_user_is_complete_without_craft_lite(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $sessionId = $this->postJson('/api/v1/onboarding/sessions', [
            'questionnaire_code' => 'core',
        ], $headers)->json('id');

        $this->patchJson("/api/v1/onboarding/sessions/{$sessionId}", [
            'answers' => $this->coreAnswers([
                'display_name' => 'Mia',
                'daily_minutes' => 20,
                'weekly_days' => '2_3',
                'best_time_of_day' => 'evening',
                'enabled_pillars' => ['mind'],
                'primary_motivation' => 'wellbeing',
                'coach_tone' => 'supportive',
            ]),
        ], $headers);

        $this->postJson("/api/v1/onboarding/sessions/{$sessionId}/complete", [], $headers)
            ->assertOk();

        $this->getJson('/api/v1/onboarding/status', $headers)
            ->assertOk()
            ->assertJsonPath('is_complete', true)
            ->assertJsonPath('phase', 'extended')
            ->assertJsonPath('available_questionnaires.0.code', 'mind_lite');
    }

    public function test_lists_current_questionnaires(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->getJson('/api/v1/onboarding/questionnaires', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->assertJsonStructure([
                'questionnaires' => [
                    ['code', 'version', 'title', 'schema'],
                ],
            ]);
    }

    public function test_patch_rejects_invalid_answer_values(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $sessionId = $this->postJson('/api/v1/onboarding/sessions', [
            'questionnaire_code' => 'core',
        ], $headers)->json('id');

        $this->patchJson("/api/v1/onboarding/sessions/{$sessionId}", [
            'answers' => [
                'coach_tone' => 'not_a_real_tone',
            ],
        ], $headers)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['answers.coach_tone']);
    }

    public function test_complete_rejects_missing_required_answers(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $sessionId = $this->postJson('/api/v1/onboarding/sessions', [
            'questionnaire_code' => 'core',
        ], $headers)->json('id');

        $this->patchJson("/api/v1/onboarding/sessions/{$sessionId}", [
            'answers' => [
                'display_name' => 'Alex',
            ],
        ], $headers)->assertOk();

        $this->postJson("/api/v1/onboarding/sessions/{$sessionId}/complete", [], $headers)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['answers.daily_minutes']);
    }

    public function test_core_one_shot_completes_onboarding(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->postJson('/api/v1/onboarding/core', [
            'answers' => $this->coreAnswers([
                'display_name' => 'Sam',
                'enabled_pillars' => ['craft'],
            ]),
        ], $headers)
            ->assertOk()
            ->assertJsonPath('status', 'completed')
            ->assertJsonPath('questionnaire_code', 'core');

        $this->getJson('/api/v1/onboarding/status', $headers)
            ->assertOk()
            ->assertJsonPath('phase', 'pillar_lite')
            ->assertJsonPath('completed_questionnaires.0', 'core');
    }

    public function test_mind_extended_questionnaire_is_available(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->getJson('/api/v1/onboarding/questionnaires/mind_focus', $headers)
            ->assertOk()
            ->assertJsonPath('code', 'mind_focus')
            ->assertJsonPath('tier', 'extended');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function coreAnswers(array $overrides = []): array
    {
        return array_merge([
            'display_name' => 'Alex',
            'timezone' => 'Europe/Moscow',
            'interface_language' => 'ru',
            'daily_minutes' => 30,
            'weekly_days' => '4_5',
            'best_time_of_day' => 'morning',
            'enabled_pillars' => ['craft'],
            'primary_motivation' => 'skill_up',
            'coach_tone' => 'direct',
        ], $overrides);
    }
}
