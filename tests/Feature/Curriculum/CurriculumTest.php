<?php

namespace Tests\Feature\Curriculum;

use App\Models\User;
use Database\Seeders\CurriculumGraphSeeder;
use Database\Seeders\OnboardingQuestionnaireSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurriculumTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(OnboardingQuestionnaireSeeder::class);
        $this->seed(CurriculumGraphSeeder::class);
    }

    public function test_list_nodes_requires_authentication(): void
    {
        $this->getJson('/api/v1/curriculum/nodes')->assertUnauthorized();
    }

    public function test_lists_published_php_nodes(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->getJson('/api/v1/curriculum/nodes?track=php', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->assertJsonStructure(['nodes' => [['id', 'slug', 'track', 'title', 'summary', 'status']]]);
    }

    public function test_shows_single_node(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->getJson('/api/v1/curriculum/nodes/php.functions', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->assertJsonPath('slug', 'php.functions');
    }

    public function test_lists_prerequisites_for_node(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $response = $this->getJson('/api/v1/curriculum/nodes/php.arrays/prerequisites', [
            'Authorization' => 'Bearer '.$token,
        ])->assertOk();

        $slugs = collect($response->json('nodes'))->pluck('slug')->all();

        $this->assertContains('php.operators', $slugs);
    }
}
