<?php

namespace App\Modules\Onboarding\DTO\Output;

use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class ComposedPromptsData implements RespondsAsArray
{
    /**
     * @param  array<string, string>  $prompts  ключ → текст промпта
     */
    public function __construct(
        public array $prompts,
    ) {}

    public function toArray(): array
    {
        return ['prompts' => $this->prompts];
    }
}
