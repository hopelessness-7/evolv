<?php

namespace Tests\Unit\Curriculum;

use App\Modules\Curriculum\Enums\EdgeKind;
use App\Modules\Curriculum\Enums\NodeStatus;
use App\Modules\Curriculum\Enums\Track;
use App\Modules\Curriculum\Models\KnowledgeEdge;
use App\Modules\Curriculum\Models\KnowledgeNode;
use App\Modules\Curriculum\Services\RouteExpander;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteExpanderTest extends TestCase
{
    use RefreshDatabase;

    public function test_orders_nodes_topologically(): void
    {
        $intro = KnowledgeNode::query()->create([
            'slug' => 'php.intro',
            'track' => Track::Php,
            'title' => 'Intro',
            'status' => NodeStatus::Published,
        ]);
        $vars = KnowledgeNode::query()->create([
            'slug' => 'php.variables',
            'track' => Track::Php,
            'title' => 'Variables',
            'status' => NodeStatus::Published,
        ]);
        $arrays = KnowledgeNode::query()->create([
            'slug' => 'php.arrays',
            'track' => Track::Php,
            'title' => 'Arrays',
            'status' => NodeStatus::Published,
        ]);

        foreach ([[$intro, $vars], [$vars, $arrays]] as [$from, $to]) {
            KnowledgeEdge::query()->create([
                'from_node_id' => $from->id,
                'to_node_id' => $to->id,
                'kind' => EdgeKind::Requires,
            ]);
        }

        $ordered = app(RouteExpander::class)->order([$arrays, $intro, $vars]);

        $this->assertSame(
            ['php.intro', 'php.variables', 'php.arrays'],
            array_map(fn (KnowledgeNode $node) => $node->slug, $ordered),
        );
    }
}
