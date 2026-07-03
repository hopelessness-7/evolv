<?php

namespace Database\Seeders;

use App\Modules\Curriculum\Enums\EdgeKind;
use App\Modules\Curriculum\Enums\NodeStatus;
use App\Modules\Curriculum\Models\KnowledgeEdge;
use App\Modules\Curriculum\Models\KnowledgeNode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CurriculumGraphSeeder extends Seeder
{
    public function run(): void
    {
        $directory = database_path('seeders/data/curriculum');

        foreach (File::glob($directory.'/*.json') as $path) {
            $payload = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
            $slugToId = [];

            foreach ($payload['nodes'] as $nodePayload) {
                $node = KnowledgeNode::query()->updateOrCreate(
                    ['slug' => $nodePayload['slug']],
                    [
                        'track' => $payload['track'],
                        'title' => $nodePayload['title'],
                        'summary' => $nodePayload['description'] ?? $nodePayload['summary'] ?? null,
                        'status' => NodeStatus::from($nodePayload['status'] ?? 'published'),
                        'meta' => $nodePayload['meta'] ?? null,
                    ],
                );

                $slugToId[$node->slug] = $node->id;
            }

            foreach ($payload['edges'] as $edgePayload) {
                $fromId = $slugToId[$edgePayload['from']] ?? null;
                $toId = $slugToId[$edgePayload['to']] ?? null;

                if ($fromId === null || $toId === null) {
                    continue;
                }

                KnowledgeEdge::query()->updateOrCreate(
                    [
                        'from_node_id' => $fromId,
                        'to_node_id' => $toId,
                        'kind' => EdgeKind::from($edgePayload['kind']),
                    ],
                    [],
                );
            }
        }
    }
}
