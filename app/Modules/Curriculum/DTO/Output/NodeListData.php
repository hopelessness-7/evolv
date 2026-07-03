<?php

namespace App\Modules\Curriculum\DTO\Output;

use App\Modules\Curriculum\Models\KnowledgeNode;
use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class NodeListData implements RespondsAsArray
{
    /**
     * @param  list<NodeData>  $nodes
     */
    public function __construct(
        public array $nodes,
    ) {}

    /**
     * @param  list<KnowledgeNode>  $models
     */
    public static function fromModels(array $models): self
    {
        return new self(
            nodes: array_map(
                fn (KnowledgeNode $node) => NodeData::fromModel($node),
                $models,
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'nodes' => array_map(
                fn (NodeData $node) => $node->toArray(),
                $this->nodes,
            ),
        ];
    }
}
