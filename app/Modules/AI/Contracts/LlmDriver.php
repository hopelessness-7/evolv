<?php

namespace App\Modules\AI\Contracts;

use App\Modules\AI\DTO\LlmOptions;
use App\Modules\AI\DTO\LlmResponse;
use App\Modules\AI\Enums\LlmTask;

interface LlmDriver
{
    /**
     * @return list<float>
     */
    public function embed(string $text, ?LlmTask $task = null): array;

    /**
     * @param  list<array{role: string, content: string}>  $messages
     */
    public function chat(array $messages, LlmOptions $options): LlmResponse;

    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @return iterable<string>
     */
    public function chatStream(array $messages, LlmOptions $options): iterable;

    public function supports(LlmTask $task): bool;
}
