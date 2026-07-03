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
        $lessons = [
            'php.strings' => 'Strings in PHP: concatenation with `.`, double-quoted interpolation, and `strlen()`.',
            'php.operators' => 'Operators: arithmetic `+ - * /`, comparison `== ===`, logical `&& ||`, and operator precedence.',
            'php.control-flow' => 'Control flow: `if/else`, `switch`, `for`, `while`, and `foreach` loops.',
            'php.functions' => 'Functions: declare with `function name()`, parameters, `return`, and basic type hints.',
            'php.scope' => 'Variable scope: local vs global, the `global` keyword, and `static` variables inside functions.',
            'php.forms' => 'HTML forms: read `$_GET` and `$_POST`, sanitize input, and validate required fields.',
            'php.include' => 'File includes: `include`, `require`, and organizing code across multiple PHP files.',
            'php.errors' => 'Errors and debugging: notice/warning/fatal, `try/catch`, and writing to an error log.',
            'php.http-basics' => 'HTTP basics: request method, status codes (200, 404, 500), headers, and response body.',
        ];

        $entries = [];

        foreach ($lessons as $slug => $summary) {
            $title = str_replace('php.', '', $slug);
            $entries[] = [
                'node_slug' => $slug,
                'version_no' => 1,
                'atoms' => [
                    [
                        'kind' => 'theory',
                        'order' => 1,
                        'body_md' => "# {$title}\n\n{$summary}",
                    ],
                    [
                        'kind' => 'quiz',
                        'order' => 2,
                        'body_md' => "Quick check: can you explain **{$title}** in one sentence?",
                        'meta' => ['answer' => 'yes'],
                    ],
                    [
                        'kind' => 'summary',
                        'order' => 3,
                        'body_md' => "- Review the key ideas of **{$title}**\n- Move to the next step when ready",
                    ],
                ],
            ];
        }

        return $entries;
    }
}
