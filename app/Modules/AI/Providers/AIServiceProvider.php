<?php

namespace App\Modules\AI\Providers;

use App\Modules\AI\Contracts\LlmDriver;
use App\Modules\AI\Services\ContentGenerationService;
use App\Modules\AI\Services\LlmRouter;
use App\Modules\AI\Services\OllamaDriver;
use Illuminate\Support\ServiceProvider;

class AIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OllamaDriver::class, function () {
            $config = config('llm.drivers.ollama');

            return new OllamaDriver(
                host: rtrim((string) $config['host'], '/'),
                models: (array) $config['models'],
                timeout: (int) $config['timeout'],
            );
        });

        $this->app->singleton(LlmDriver::class, function ($app) {
            $driver = config('llm.default');

            return match ($driver) {
                'ollama' => $app->make(OllamaDriver::class),
                default => throw new \InvalidArgumentException("Unsupported LLM driver [{$driver}]."),
            };
        });

        $this->app->singleton(LlmRouter::class);
        $this->app->singleton(ContentGenerationService::class);
    }
}
