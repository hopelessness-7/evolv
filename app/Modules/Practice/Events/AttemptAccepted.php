<?php

namespace App\Modules\Practice\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttemptAccepted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly int $nodeId,
        public readonly string $nodeSlug,
    ) {}
}
