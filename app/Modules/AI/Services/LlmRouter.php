<?php

namespace App\Modules\AI\Services;

use App\Modules\AI\Contracts\LlmDriver;
use App\Modules\AI\DTO\LlmOptions;
use App\Modules\AI\DTO\LlmResponse;
use App\Modules\AI\Enums\LlmTask;
use App\Modules\AI\Exceptions\LlmException;

class LlmRouter
{
    public function __construct(
        private readonly LlmDriver $driver,
    ) {}

    /**
     * @return list<float>
     */
    public function embed(string $text): array
    {
        return $this->driver->embed($text, LlmTask::Embed);
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     */
    public function chat(array $messages, LlmTask $task, ?LlmOptions $options = null): LlmResponse
    {
        $options = ($options ?? new LlmOptions)->with(['task' => $task]);

        if (! $this->driver->supports($task)) {
            throw new LlmException("LLM driver does not support task [{$task->value}].");
        }

        return $this->driver->chat($messages, $options);
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @return iterable<string>
     */
    public function chatStream(array $messages, LlmTask $task, ?LlmOptions $options = null): iterable
    {
        $options = ($options ?? new LlmOptions)->with(['task' => $task]);

        if (! $this->driver->supports($task)) {
            throw new LlmException("LLM driver does not support task [{$task->value}].");
        }

        return $this->driver->chatStream($messages, $options);
    }
}
