<?php

namespace App\Modules\Shared\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;

class InfraHealthController extends ApiController
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'app' => $this->ok(),
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'ollama' => $this->checkHttpService(config('llm.drivers.ollama.host').'/api/tags'),
            'qdrant' => $this->checkHttpService(rtrim((string) env('QDRANT_HOST', 'http://qdrant:6333'), '/').'/healthz'),
            'meilisearch' => $this->checkHttpService(rtrim((string) env('MEILISEARCH_HOST', 'http://meilisearch:7700'), '/').'/health'),
            'judge0' => $this->checkHttpService(config('judge0.host').'/about'),
        ];

        $healthy = collect($checks)->every(
            fn (array $check) => ($check['status'] ?? 'error') === 'ok'
        );

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }

    /**
     * @return array{status: string, message?: string}
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return $this->ok();
        } catch (\Throwable $exception) {
            return $this->fail($exception->getMessage());
        }
    }

    /**
     * @return array{status: string, message?: string}
     */
    private function checkRedis(): array
    {
        try {
            Redis::ping();

            return $this->ok();
        } catch (\Throwable $exception) {
            return $this->fail($exception->getMessage());
        }
    }

    /**
     * @return array{status: string, message?: string}
     */
    private function checkHttpService(string $url): array
    {
        try {
            $response = Http::timeout(5)->get($url);

            if ($response->successful()) {
                return $this->ok();
            }

            return $this->fail('HTTP '.$response->status());
        } catch (\Throwable $exception) {
            return $this->fail($exception->getMessage());
        }
    }

    /**
     * @return array{status: string}
     */
    private function ok(): array
    {
        return ['status' => 'ok'];
    }

    /**
     * @return array{status: string, message: string}
     */
    private function fail(string $message): array
    {
        return [
            'status' => 'error',
            'message' => $message,
        ];
    }
}
