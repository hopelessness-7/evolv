<?php

namespace Database\Seeders;

use App\Modules\Content\Enums\AtomKind;
use App\Modules\Content\Enums\VersionStatus;
use App\Modules\Content\Models\ContentAtom;
use App\Modules\Content\Models\ContentVersion;
use App\Modules\Curriculum\Models\KnowledgeNode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $directory = database_path('seeders/data/content');

        foreach (File::glob($directory.'/*.json') as $path) {
            $payload = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

            foreach ($payload['entries'] as $entry) {
                $this->seedEntry($entry);
            }
        }

        foreach ($this->fallbackEntries() as $entry) {
            $node = KnowledgeNode::query()->where('slug', $entry['node_slug'])->first();

            if ($node === null) {
                continue;
            }

            $exists = ContentVersion::query()
                ->where('node_id', $node->id)
                ->where('status', VersionStatus::Active)
                ->exists();

            if ($exists) {
                continue;
            }

            $this->seedEntry($entry);
        }
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private function seedEntry(array $entry): void
    {
        $node = KnowledgeNode::query()->where('slug', $entry['node_slug'])->first();

        if ($node === null) {
            return;
        }

        $version = ContentVersion::query()->updateOrCreate(
            [
                'node_id' => $node->id,
                'version_no' => $entry['version_no'] ?? 1,
            ],
            [
                'status' => VersionStatus::Active,
            ],
        );

        ContentAtom::query()->where('version_id', $version->id)->delete();

        foreach ($entry['atoms'] as $atomPayload) {
            ContentAtom::query()->create([
                'version_id' => $version->id,
                'kind' => AtomKind::from($atomPayload['kind']),
                'body_md' => $atomPayload['body_md'],
                'meta' => $atomPayload['meta'] ?? null,
                'order_in_version' => $atomPayload['order'] ?? 0,
            ]);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fallbackEntries(): array
    {
        return [];
    }
}
