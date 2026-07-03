<?php

namespace App\Modules\Curriculum\Contracts;

use App\Modules\Curriculum\Enums\Track;
use App\Modules\Curriculum\Models\KnowledgeNode;

interface NodeRepositoryInterface
{
    /**
     * @return list<KnowledgeNode>
     */
    public function listPublished(?Track $track = null): array;

    public function findBySlug(string $slug): ?KnowledgeNode;
}
