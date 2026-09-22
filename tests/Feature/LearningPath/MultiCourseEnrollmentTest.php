<?php

namespace Tests\Feature\LearningPath;

use App\Models\User;
use Database\Seeders\ContentSeeder;
use Database\Seeders\CurriculumGraphSeeder;
use Database\Seeders\OnboardingQuestionnaireSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiCourseEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(OnboardingQuestionnaireSeeder::class);
        $this->seed(CurriculumGraphSeeder::class);
        $this->seed(ContentSeeder::class);
    }

    public function test_can_enroll_php_and_laravel_in_parallel(): void
    {
        $headers = $this->authHeaders();

        $this->postJson('/api/v1/learning-path/enroll', ['track' => 'php'], $headers)
            ->assertCreated()
            ->assertJsonPath('track', 'php');

        $this->postJson('/api/v1/learning-path/enroll', ['track' => 'laravel'], $headers)
            ->assertCreated()
            ->assertJsonPath('track', 'laravel');

        $plans = $this->getJson('/api/v1/learning-path/plans', $headers)
            ->assertOk()
            ->json('plans');

        $tracks = collect($plans)->pluck('track')->all();
        $this->assertContains('php', $tracks);
        $this->assertContains('laravel', $tracks);
    }

    public function test_plan_by_track_query_and_primary_switch(): void
    {
        $headers = $this->authHeaders();

        $this->postJson('/api/v1/learning-path/enroll', ['track' => 'php'], $headers)->assertCreated();
        $this->postJson('/api/v1/learning-path/enroll', ['track' => 'laravel'], $headers)->assertCreated();

        $this->putJson('/api/v1/learning-path/primary', ['track' => 'laravel'], $headers)
            ->assertOk()
            ->assertJsonPath('primary_track', 'laravel');

        $this->getJson('/api/v1/learning-path?track=php', $headers)
            ->assertOk()
            ->assertJsonPath('track', 'php');

        $this->getJson('/api/v1/learning-path', $headers)
            ->assertOk()
            ->assertJsonPath('track', 'laravel');
    }

    public function test_complete_step_on_non_primary_track(): void
    {
        $headers = $this->authHeaders();

        $this->postJson('/api/v1/learning-path/enroll', ['track' => 'php'], $headers)->assertCreated();
        $laravel = $this->postJson('/api/v1/learning-path/enroll', ['track' => 'laravel'], $headers)
            ->assertCreated()
            ->json();

        $this->putJson('/api/v1/learning-path/primary', ['track' => 'php'], $headers)->assertOk();

        $stepId = $laravel['steps'][0]['id'];

        $this->postJson("/api/v1/learning-path/steps/{$stepId}/complete", [], $headers)
            ->assertOk()
            ->assertJsonPath('track', 'laravel')
            ->assertJsonPath('steps.0.status', 'completed');
    }

    /**
     * @return array<string, string>
     */
    private function authHeaders(): array
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        return ['Authorization' => 'Bearer '.$token];
    }
}
