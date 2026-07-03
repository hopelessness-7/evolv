<?php

namespace App\Modules\Curriculum\DTO\Output;

use App\Modules\Curriculum\Enums\NodeStatus;
use App\Modules\Curriculum\Enums\Track;
use App\Modules\Curriculum\Models\KnowledgeNode;
use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class NodeData implements RespondsAsArray
{
    public function __construct(
        public int $id,
        public string $slug,
        public Track $track,
        public string $title,
        public string $summary,
        public NodeStatus $status,
    ) {}

    public static function fromModel(KnowledgeNode $node): self
    {
        return new self(
            id: $node->id,
            slug: $node->slug,
            track: $node->track,
            title: $node->title,
            summary: (string) ($node->summary ?? ''),
            status: $node->status,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'track' => $this->track->value,
            'title' => $this->title,
            'summary' => $this->summary,
            'status' => $this->status->value,
        ];
    }
}
