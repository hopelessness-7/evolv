<?php

namespace Tests\Feature\Curriculum;

use App\Models\User;
use App\Modules\Curriculum\Enums\Track;
use Database\Seeders\ContentSeeder;
use Database\Seeders\CurriculumGraphSeeder;
use Database\Seeders\OnboardingQuestionnaireSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaravelMiddleTrackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(OnboardingQuestionnaireSeeder::class);
        $this->seed(CurriculumGraphSeeder::class);
        $this->seed(ContentSeeder::class);
    }

    public function test_lists_laravel_middle_nodes(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $response = $this->getJson('/api/v1/curriculum/nodes?track=laravel', [
            'Authorization' => 'Bearer '.$token,
        ])->assertOk();

        $slugs = collect($response->json('nodes'))->pluck('slug')->all();

        foreach ([
            'laravel.n1',
            'laravel.sql-explain',
            'laravel.container',
            'laravel.lifecycle',
            'laravel.php8-speech',
            'laravel.validation',
            'laravel.queues',
            'laravel.events',
            'laravel.sanctum',
            'laravel.webhooks',
            'laravel.ci',
            'laravel.estimate',
        ] as $slug) {
            $this->assertContains($slug, $slugs);
        }
    }

    public function test_all_laravel_nodes_have_content(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        foreach ([
            'laravel.n1',
            'laravel.sql-explain',
            'laravel.container',
            'laravel.lifecycle',
            'laravel.php8-speech',
            'laravel.validation',
            'laravel.queues',
            'laravel.events',
            'laravel.sanctum',
            'laravel.webhooks',
            'laravel.ci',
            'laravel.estimate',
        ] as $slug) {
            $atoms = $this->getJson("/api/v1/content/nodes/{$slug}", $headers)
                ->assertOk()
                ->json('atoms');

            $this->assertNotEmpty($atoms, "expected content for {$slug}");
            $this->assertContains('theory', collect($atoms)->pluck('kind')->all());
        }
    }

    public function test_n1_content_has_theory_quiz_and_exercise(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $atoms = $this->getJson('/api/v1/content/nodes/laravel.n1', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->assertJsonPath('node.slug', 'laravel.n1')
            ->json('atoms');

        $kinds = collect($atoms)->pluck('kind')->all();

        $this->assertContains('theory', $kinds);
        $this->assertContains('quiz', $kinds);
        $this->assertContains('exercise', $kinds);
        $this->assertContains('summary', $kinds);
    }

    public function test_sql_explain_content_and_quiz(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $atoms = $this->getJson('/api/v1/content/nodes/laravel.sql-explain', $headers)
            ->assertOk()
            ->json('atoms');

        $quiz = collect($atoms)->first(
            fn (array $atom) => $atom['kind'] === 'quiz'
                && str_contains($atom['body_md'], 'HAVING'),
        );

        $this->assertNotNull($quiz);

        $this->postJson('/api/v1/content/nodes/laravel.sql-explain/quiz-check', [
            'atom_id' => $quiz['id'],
            'answer' => 'B',
        ], $headers)
            ->assertOk()
            ->assertJsonPath('correct', true);
    }

    public function test_practice_exercise_exists_for_n1(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->getJson('/api/v1/practice/nodes/laravel.n1/exercise', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->assertJsonPath('node_slug', 'laravel.n1')
            ->assertJsonPath('language', 'php');
    }

    public function test_laravel_track_entry_slug_is_n1(): void
    {
        $this->assertSame('laravel.n1', Track::Laravel->entrySlug());
        $this->assertSame('php.intro', Track::Php->entrySlug());
    }

    public function test_tracks_list_marks_laravel_as_having_content(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $tracks = $this->getJson('/api/v1/learning-path/tracks', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->json('tracks');

        $laravel = collect($tracks)->firstWhere('track', 'laravel');

        $this->assertNotNull($laravel);
        $this->assertTrue($laravel['has_content']);
        $this->assertSame('Laravel Middle', $laravel['label']);
    }
}
