<?php

namespace Tests\Feature\Content;

use App\Models\User;
use Database\Seeders\ContentSeeder;
use Database\Seeders\CurriculumGraphSeeder;
use Database\Seeders\OnboardingQuestionnaireSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(OnboardingQuestionnaireSeeder::class);
        $this->seed(CurriculumGraphSeeder::class);
        $this->seed(ContentSeeder::class);
    }

    public function test_returns_active_content_for_published_node(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->getJson('/api/v1/content/nodes/php.intro', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->assertJsonPath('node.slug', 'php.intro')
            ->assertJsonPath('status', 'active');
    }

    public function test_seeds_content_for_all_php_track_nodes(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        foreach ([
            'php.intro', 'php.variables', 'php.strings', 'php.operators', 'php.arrays',
            'php.control-flow', 'php.functions', 'php.scope', 'php.forms',
            'php.include', 'php.errors', 'php.http-basics',
        ] as $slug) {
            $this->getJson("/api/v1/content/nodes/{$slug}", $headers)->assertOk();
        }
    }

    public function test_quiz_check_validates_answer(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $atoms = $this->getJson('/api/v1/content/nodes/php.intro', $headers)
            ->json('atoms');

        $quiz = collect($atoms)->firstWhere('kind', 'quiz');

        $this->postJson('/api/v1/content/nodes/php.intro/quiz-check', [
            'atom_id' => $quiz['id'],
            'answer' => 'b',
        ], $headers)
            ->assertOk()
            ->assertJsonPath('correct', true);
    }
}
