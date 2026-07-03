<?php

namespace App\Modules\Curriculum\Contracts;

use App\Modules\Curriculum\Models\KnowledgeNode;

interface GraphRepositoryInterface
{
    /**
     * Nodes that must be completed before the given node (requires edges).
     *
     * @return list<KnowledgeNode>
     */
    public function prerequisites(int $nodeId, bool $transitive = true): array;

    /**
     * Nodes that depend on the given node (requires edges).
     *
     * @return list<KnowledgeNode>
     */
    public function dependents(int $nodeId, bool $transitive = true): array;

    /**
     * @return list<KnowledgeNode>
     */
    public function related(int $nodeId): array;

    /**
     * Entry nodes plus all nodes reachable forward along requires edges.
     *
     * @param  list<int>  $entryNodeIds
     * @return list<KnowledgeNode>
     */
    public function reachableFromMany(array $entryNodeIds): array;

    public function hasRequiresCycle(int $fromNodeId, int $toNodeId): bool;
}
