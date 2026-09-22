<?php

namespace Tests\Feature\Journal;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalTest extends TestCase
{
    use RefreshDatabase;

    public function test_crud_journal_entries(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $created = $this->postJson('/api/v1/journal/entries', [
            'kind' => 'reflection',
            'body' => 'Понял N+1 и with()',
            'node_slug' => 'php.intro',
            'plan_date' => now()->toDateString(),
        ], $headers)
            ->assertCreated()
            ->assertJsonPath('kind', 'reflection')
            ->assertJsonPath('node_slug', 'php.intro')
            ->json();

        $this->getJson('/api/v1/journal/entries?node_slug=php.intro', $headers)
            ->assertOk()
            ->assertJsonPath('entries.0.id', $created['id'])
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonStructure(['entries', 'meta' => ['current_page', 'per_page', 'total', 'last_page']]);

        $this->patchJson('/api/v1/journal/entries/'.$created['id'], [
            'body' => 'Обновлённая мысль',
        ], $headers)
            ->assertOk()
            ->assertJsonPath('body', 'Обновлённая мысль');

        $this->deleteJson('/api/v1/journal/entries/'.$created['id'], [], $headers)
            ->assertNoContent();

        $this->getJson('/api/v1/journal/entries', $headers)
            ->assertOk()
            ->assertJsonPath('entries', []);
    }

    public function test_journal_requires_auth(): void
    {
        $this->getJson('/api/v1/journal/entries')->assertUnauthorized();
    }
}
