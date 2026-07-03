<?php

namespace App\Modules\Content\DTO\Output;

use App\Modules\Content\Models\ContentVersion;
use App\Modules\Curriculum\DTO\Output\NodeData;
use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class NodeContentData implements RespondsAsArray
{
    /**
     * @param  list<AtomData>  $atoms
     */
    public function __construct(
        public NodeData $node,
        public int $versionNo,
        public string $status,
        public array $atoms,
    ) {}

    public static function fromVersion(ContentVersion $version): self
    {
        return new self(
            node: NodeData::fromModel($version->node),
            versionNo: $version->version_no,
            status: $version->status->value,
            atoms: $version->atoms
                ->map(fn ($atom) => AtomData::fromModel($atom))
                ->values()
                ->all(),
        );
    }

    public function toArray(): array
    {
        return [
            'node' => $this->node->toArray(),
            'version_no' => $this->versionNo,
            'status' => $this->status,
            'atoms' => array_map(fn (AtomData $atom) => $atom->toArray(), $this->atoms),
        ];
    }
}
