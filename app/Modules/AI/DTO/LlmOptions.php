<?php

namespace App\Modules\AI\DTO;

use App\Modules\AI\Enums\LlmTask;

final readonly class LlmOptions
{
    public function __construct(
        public LlmTask $task = LlmTask::Chat,
        public ?string $model = null,
        public float $temperature = 0.7,
        public ?int $maxTokens = null,
        public bool $jsonMode = false,
    ) {}

    /**
     * @param  array<string, mixed>  $overrides
     */
    public function with(array $overrides): self
    {
        return new self(
            task: $overrides['task'] ?? $this->task,
            model: $overrides['model'] ?? $this->model,
            temperature: $overrides['temperature'] ?? $this->temperature,
            maxTokens: $overrides['maxTokens'] ?? $this->maxTokens,
            jsonMode: $overrides['jsonMode'] ?? $this->jsonMode,
        );
    }
}
