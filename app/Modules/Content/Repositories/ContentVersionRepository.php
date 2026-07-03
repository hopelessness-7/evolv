<?php

namespace App\Modules\Content\Repositories;

use App\Modules\Content\Contracts\ContentVersionRepositoryInterface;
use App\Modules\Content\Enums\AtomKind;
use App\Modules\Content\Enums\VersionStatus;
use App\Modules\Content\Models\ContentAtom;
use App\Modules\Content\Models\ContentVersion;
use App\Modules\Curriculum\Models\KnowledgeNode;

class ContentVersionRepository implements ContentVersionRepositoryInterface
{
    public function findNodeBySlug(string $slug): ?KnowledgeNode
    {
        return KnowledgeNode::query()->where('slug', $slug)->first();
    }

    public function findActiveByNodeId(int $nodeId): ?ContentVersion
    {
        return ContentVersion::query()
            ->with(['atoms', 'node'])
            ->where('node_id', $nodeId)
            ->where('status', VersionStatus::Active)
            ->orderByDesc('version_no')
            ->first();
    }

    public function findAtomByIdInVersion(ContentVersion $version, int $atomId): ?ContentAtom
    {
        return ContentAtom::query()
            ->where('version_id', $version->id)
            ->whereKey($atomId)
            ->first();
    }

    public function findFirstAtomByKindInVersion(ContentVersion $version, AtomKind $kind): ?ContentAtom
    {
        return ContentAtom::query()
            ->where('version_id', $version->id)
            ->where('kind', $kind)
            ->orderBy('order_in_version')
            ->first();
    }
}
