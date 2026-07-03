<?php

namespace Tests\Unit\Curriculum;

use App\Modules\Curriculum\Contracts\GraphRepositoryInterface;
use App\Modules\Curriculum\Models\KnowledgeNode;
use Database\Seeders\CurriculumGraphSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GraphRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurriculumGraphSeeder::class);
    }

    public function test_prerequisites_walks_requires_chain(): void
    {
        $graph = app(GraphRepositoryInterface::class);
        $arrays = KnowledgeNode::query()
            ->where('slug', 'php.arrays')
            ->firstOrFail();

        $slugs = array_map(
            fn (KnowledgeNode $node) => $node->slug,
            $graph->prerequisites($arrays->id),
        );

        $this->assertContains('php.intro', $slugs);
        $this->assertContains('php.variables', $slugs);
        $this->assertContains('php.operators', $slugs);
    }

    public function test_requires_edge_does_not_create_cycle(): void
    {
        $graph = app(GraphRepositoryInterface::class);
        $intro = KnowledgeNode::query()->where('slug', 'php.intro')->firstOrFail();
        $variables = KnowledgeNode::query()->where('slug', 'php.variables')->firstOrFail();

        $this->assertFalse($graph->hasRequiresCycle($intro->id, $variables->id));
    }

    public function test_reachable_from_entry_includes_downstream_nodes(): void
    {
        $graph = app(GraphRepositoryInterface::class);
        $intro = KnowledgeNode::query()
            ->where('slug', 'php.intro')
            ->firstOrFail();

        $slugs = array_map(
            fn (KnowledgeNode $node) => $node->slug,
            $graph->reachableFromMany([$intro->id]),
        );

        $this->assertContains('php.intro', $slugs);
        $this->assertContains('php.functions', $slugs);
    }
}
