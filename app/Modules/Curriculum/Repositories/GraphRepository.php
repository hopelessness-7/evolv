<?php

namespace App\Modules\Curriculum\Repositories;

use App\Modules\Curriculum\Contracts\GraphRepositoryInterface;
use App\Modules\Curriculum\Enums\EdgeKind;
use App\Modules\Curriculum\Models\KnowledgeNode;
use Illuminate\Support\Facades\DB;

class GraphRepository implements GraphRepositoryInterface
{
    public function prerequisites(int $nodeId, bool $transitive = true): array
    {
        if (! $transitive) {
            return $this->loadNodes(
                DB::table('knowledge_edges')
                    ->where('to_node_id', $nodeId)
                    ->where('kind', EdgeKind::Requires->value)
                    ->pluck('from_node_id')
                    ->all(),
            );
        }

        return $this->loadNodes(
            $this->recursiveNodeIds(
                <<<'SQL'
                WITH RECURSIVE walk AS (
                    SELECT from_node_id AS node_id
                    FROM knowledge_edges
                    WHERE to_node_id = ? AND kind = ?
                    UNION
                    SELECT e.from_node_id
                    FROM knowledge_edges e
                    INNER JOIN walk w ON e.to_node_id = w.node_id
                    WHERE e.kind = ?
                )
                SELECT node_id FROM walk
                SQL,
                [$nodeId, EdgeKind::Requires->value, EdgeKind::Requires->value],
            ),
        );
    }

    public function dependents(int $nodeId, bool $transitive = true): array
    {
        if (! $transitive) {
            return $this->loadNodes(
                DB::table('knowledge_edges')
                    ->where('from_node_id', $nodeId)
                    ->where('kind', EdgeKind::Requires->value)
                    ->pluck('to_node_id')
                    ->all(),
            );
        }

        return $this->loadNodes(
            $this->recursiveNodeIds(
                <<<'SQL'
                WITH RECURSIVE walk AS (
                    SELECT to_node_id AS node_id
                    FROM knowledge_edges
                    WHERE from_node_id = ? AND kind = ?
                    UNION
                    SELECT e.to_node_id
                    FROM knowledge_edges e
                    INNER JOIN walk w ON e.from_node_id = w.node_id
                    WHERE e.kind = ?
                )
                SELECT node_id FROM walk
                SQL,
                [$nodeId, EdgeKind::Requires->value, EdgeKind::Requires->value],
            ),
        );
    }

    public function related(int $nodeId): array
    {
        $outgoing = DB::table('knowledge_edges')
            ->where('from_node_id', $nodeId)
            ->where('kind', EdgeKind::RelatedTo->value)
            ->pluck('to_node_id')
            ->all();

        $incoming = DB::table('knowledge_edges')
            ->where('to_node_id', $nodeId)
            ->where('kind', EdgeKind::RelatedTo->value)
            ->pluck('from_node_id')
            ->all();

        return $this->loadNodes(array_values(array_unique([...$outgoing, ...$incoming])));
    }

    public function reachableFromMany(array $entryNodeIds): array
    {
        $ids = [];

        foreach (array_values(array_unique(array_map(intval(...), $entryNodeIds))) as $entryId) {
            $ids[$entryId] = true;

            foreach ($this->dependents($entryId, true) as $node) {
                $ids[$node->id] = true;
            }
        }

        return $this->loadNodes(array_keys($ids));
    }

    public function hasRequiresCycle(int $fromNodeId, int $toNodeId): bool
    {
        if ($fromNodeId === $toNodeId) {
            return true;
        }

        $reachable = $this->recursiveNodeIds(
            <<<'SQL'
            WITH RECURSIVE walk AS (
                SELECT to_node_id AS node_id
                FROM knowledge_edges
                WHERE from_node_id = ? AND kind = ?
                UNION
                SELECT e.to_node_id
                FROM knowledge_edges e
                INNER JOIN walk w ON e.from_node_id = w.node_id
                WHERE e.kind = ?
            )
            SELECT node_id FROM walk WHERE node_id = ?
            SQL,
            [$toNodeId, EdgeKind::Requires->value, EdgeKind::Requires->value, $fromNodeId],
        );

        return $reachable !== [];
    }

    /**
     * @param  list<int|string>  $bindings
     * @return list<int>
     */
    private function recursiveNodeIds(string $sql, array $bindings): array
    {
        $rows = DB::select($sql, $bindings);

        return array_map(intval(...), array_column($rows, 'node_id'));
    }

    /**
     * @param  list<int>  $ids
     * @return list<KnowledgeNode>
     */
    private function loadNodes(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $nodes = KnowledgeNode::query()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $ordered = [];

        foreach ($ids as $id) {
            if ($nodes->has($id)) {
                $ordered[] = $nodes->get($id);
            }
        }

        return $ordered;
    }
}
