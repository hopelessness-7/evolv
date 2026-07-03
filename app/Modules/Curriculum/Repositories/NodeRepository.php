<?php

namespace App\Modules\Curriculum\Repositories;

use App\Modules\Curriculum\Contracts\NodeRepositoryInterface;
use App\Modules\Curriculum\Enums\NodeStatus;
use App\Modules\Curriculum\Enums\Track;
use App\Modules\Curriculum\Models\KnowledgeNode;

class NodeRepository implements NodeRepositoryInterface
{
    public function listPublished(?Track $track = null): array
    {
        $query = KnowledgeNode::query()
            ->where('status', NodeStatus::Published);

        if ($track !== null) {
            $query->where('track', $track);
        }

        return $query
            ->orderBy('track')
            ->orderBy('slug')
            ->get()
            ->all();
    }

    public function findBySlug(string $slug): ?KnowledgeNode
    {
        return KnowledgeNode::query()
            ->where('slug', $slug)
            ->first();
    }
}
