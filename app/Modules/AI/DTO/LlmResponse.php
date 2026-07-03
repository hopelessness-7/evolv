<?php

namespace App\Modules\AI\DTO;

final readonly class LlmResponse
{
    public function __construct(
        public string $content,
        public string $model,
        public ?int $promptTokens = null,
        public ?int $completionTokens = null,
        public ?int $totalDurationNs = null,
    ) {}
}
