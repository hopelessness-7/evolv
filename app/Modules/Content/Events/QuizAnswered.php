<?php

namespace App\Modules\Content\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuizAnswered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly string $nodeSlug,
        public readonly int $atomId,
        public readonly bool $correct,
    ) {}
}
