<?php

namespace Tests\Feature;

use Tests\TestCase;

class InfraHealthTest extends TestCase
{
    public function test_infra_health_endpoint_returns_service_checks(): void
    {
        $response = $this->getJson('/api/v1/health/infra');

        $response->assertJsonStructure([
            'status',
            'checks' => [
                'app',
                'database',
                'redis',
                'ollama',
                'qdrant',
                'meilisearch',
                'judge0',
            ],
        ]);
    }
}
