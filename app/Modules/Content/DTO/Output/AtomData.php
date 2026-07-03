<?php

namespace App\Modules\Content\DTO\Output;

use App\Modules\Content\Models\ContentAtom;
use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class AtomData implements RespondsAsArray
{
    public function __construct(
        public int $id,
        public string $kind,
        public string $bodyMd,
        public ?array $meta,
        public int $order,
    ) {}

    public static function fromModel(ContentAtom $atom): self
    {
        return new self(
            id: $atom->id,
            kind: $atom->kind->value,
            bodyMd: $atom->body_md,
            meta: $atom->meta,
            order: $atom->order_in_version,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'kind' => $this->kind,
            'body_md' => $this->bodyMd,
            'meta' => $this->meta,
            'order' => $this->order,
        ];
    }
}
