<?php

namespace App\Modules\Content\Contracts;

use App\Modules\Content\Enums\AtomKind;
use App\Modules\Content\Models\ContentAtom;
use App\Modules\Content\Models\ContentVersion;
use App\Modules\Curriculum\Models\KnowledgeNode;

interface ContentVersionRepositoryInterface
{
    public function findNodeBySlug(string $slug): ?KnowledgeNode;

    public function findActiveByNodeId(int $nodeId): ?ContentVersion;

    public function findAtomByIdInVersion(ContentVersion $version, int $atomId): ?ContentAtom;

    public function findFirstAtomByKindInVersion(ContentVersion $version, AtomKind $kind): ?ContentAtom;
}
