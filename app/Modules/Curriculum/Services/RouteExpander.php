<?php

namespace App\Modules\Curriculum\Services;

use App\Modules\Curriculum\Contracts\GraphRepositoryInterface;
use App\Modules\Curriculum\Models\KnowledgeNode;
use Illuminate\Support\Facades\DB;

class RouteExpander
{
    public function __construct(
        private readonly GraphRepositoryInterface $graph,
    ) {}

    /**
     * @param  list<KnowledgeNode>  $nodes
     * @return list<KnowledgeNode>
     */
    public function order(array $nodes): array
    {
        if ($nodes === []) {
            return [];
        }

        $byId = [];

        foreach ($nodes as $node) {
            $byId[$node->id] = $node;
        }

        $nodeIds = array_keys($byId);

        $edges = DB::table('knowledge_edges')
            ->where('kind', 'requires')
            ->whereIn('from_node_id', $nodeIds)
            ->whereIn('to_node_id', $nodeIds)
            ->get(['from_node_id', 'to_node_id']);

        $inDegree = array_fill_keys($nodeIds, 0);
        $adjacency = array_fill_keys($nodeIds, []);

        foreach ($edges as $edge) {
            $prerequisite = (int) $edge->from_node_id;
            $dependent = (int) $edge->to_node_id;
            $adjacency[$prerequisite][] = $dependent;
            $inDegree[$dependent]++;
        }

        $queue = array_values(array_filter($nodeIds, fn (int $id) => $inDegree[$id] === 0));
        usort($queue, fn (int $a, int $b) => strcmp($byId[$a]->slug, $byId[$b]->slug));

        $sorted = [];

        while ($queue !== []) {
            $current = array_shift($queue);
            $sorted[] = $current;

            $neighbors = $adjacency[$current];
            usort($neighbors, fn (int $a, int $b) => strcmp($byId[$a]->slug, $byId[$b]->slug));

            foreach ($neighbors as $neighbor) {
                $inDegree[$neighbor]--;

                if ($inDegree[$neighbor] === 0) {
                    $queue[] = $neighbor;
                }
            }

            usort($queue, fn (int $a, int $b) => strcmp($byId[$a]->slug, $byId[$b]->slug));
        }

        if (count($sorted) !== count($nodeIds)) {
            usort($nodes, fn (KnowledgeNode $a, KnowledgeNode $b) => strcmp($a->slug, $b->slug));

            return $nodes;
        }

        return array_map(fn (int $id) => $byId[$id], $sorted);
    }
}
