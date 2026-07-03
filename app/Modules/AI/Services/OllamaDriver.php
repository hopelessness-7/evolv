<?php

namespace App\Modules\AI\Services;

use App\Modules\AI\Contracts\LlmDriver;
use App\Modules\AI\DTO\LlmOptions;
use App\Modules\AI\DTO\LlmResponse;
use App\Modules\AI\Enums\LlmTask;
use App\Modules\AI\Exceptions\LlmException;
use Generator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class OllamaDriver implements LlmDriver
{
    public function __construct(
        private readonly string $host,
        private readonly array $models,
        private readonly int $timeout,
    ) {}

    public function embed(string $text, ?LlmTask $task = null): array
    {
        $model = $this->resolveModel($task ?? LlmTask::Embed);

        try {
            $response = Http::baseUrl($this->host)
                ->timeout($this->timeout)
                ->post('/api/embeddings', [
                    'model' => $model,
                    'prompt' => $text,
                ])
                ->throw();
        } catch (ConnectionException|RequestException $exception) {
            throw new LlmException('Ollama embedding request failed: '.$exception->getMessage(), 0, $exception);
        }

        $embedding = $response->json('embedding');

        if (! is_array($embedding)) {
            throw new LlmException('Ollama returned an invalid embedding payload.');
        }

        return array_map('floatval', $embedding);
    }

    public function chat(array $messages, LlmOptions $options): LlmResponse
    {
        $model = $this->resolveModel($options->task, $options->model);
        $payload = $this->buildChatPayload($messages, $options, $model);

        try {
            $response = Http::baseUrl($this->host)
                ->timeout($this->timeout)
                ->post('/api/chat', $payload)
                ->throw();
        } catch (ConnectionException|RequestException $exception) {
            throw new LlmException('Ollama chat request failed: '.$exception->getMessage(), 0, $exception);
        }

        $content = $response->json('message.content');

        if (! is_string($content) || $content === '') {
            throw new LlmException('Ollama returned an empty chat response.');
        }

        return new LlmResponse(
            content: $content,
            model: $model,
            promptTokens: $response->json('prompt_eval_count'),
            completionTokens: $response->json('eval_count'),
            totalDurationNs: $response->json('total_duration'),
        );
    }

    public function chatStream(array $messages, LlmOptions $options): iterable
    {
        $model = $this->resolveModel($options->task, $options->model);
        $payload = $this->buildChatPayload($messages, $options, $model);
        $payload['stream'] = true;

        try {
            $response = Http::baseUrl($this->host)
                ->timeout($this->timeout)
                ->withOptions(['stream' => true])
                ->post('/api/chat', $payload);
        } catch (ConnectionException $exception) {
            throw new LlmException('Ollama stream request failed: '.$exception->getMessage(), 0, $exception);
        }

        if ($response->failed()) {
            throw new LlmException('Ollama stream request failed with HTTP '.$response->status());
        }

        $body = $response->toPsrResponse()->getBody();

        while (! $body->eof()) {
            $line = $this->readLine($body);

            if ($line === '') {
                continue;
            }

            $chunk = json_decode($line, true);

            if (! is_array($chunk)) {
                continue;
            }

            $delta = $chunk['message']['content'] ?? null;

            if (is_string($delta) && $delta !== '') {
                yield $delta;
            }
        }
    }

    public function supports(LlmTask $task): bool
    {
        return array_key_exists($task->value, $this->models);
    }

    private function resolveModel(LlmTask $task, ?string $override = null): string
    {
        if ($override !== null && $override !== '') {
            return $override;
        }

        $model = $this->models[$task->value] ?? $this->models['default'] ?? null;

        if ($model === null) {
            throw new LlmException("No Ollama model configured for task [{$task->value}].");
        }

        return $model;
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @return array<string, mixed>
     */
    private function buildChatPayload(array $messages, LlmOptions $options, string $model): array
    {
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'stream' => false,
            'options' => [
                'temperature' => $options->temperature,
            ],
        ];

        if ($options->maxTokens !== null) {
            $payload['options']['num_predict'] = $options->maxTokens;
        }

        if ($options->jsonMode) {
            $payload['format'] = 'json';
        }

        return $payload;
    }

    /**
     * @param  resource|\Psr\Http\Message\StreamInterface  $stream
     */
    private function readLine($stream): string
    {
        $buffer = '';

        while (! $stream->eof()) {
            $char = $stream->read(1);

            if ($char === '' || $char === false) {
                break;
            }

            if ($char === "\n") {
                break;
            }

            $buffer .= $char;
        }

        return trim($buffer);
    }
}
