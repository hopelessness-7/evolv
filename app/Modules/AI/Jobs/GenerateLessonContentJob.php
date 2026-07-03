<?php

namespace App\Modules\AI\Jobs;

use App\Modules\AI\Enums\GenerationJobStatus;
use App\Modules\AI\Enums\LlmTask;
use App\Modules\AI\Models\AiGenerationJob;
use App\Modules\AI\Services\LlmRouter;
use App\Modules\Content\Enums\AtomKind;
use App\Modules\Content\Enums\VersionStatus;
use App\Modules\Content\Models\ContentAtom;
use App\Modules\Content\Models\ContentVersion;
use App\Modules\Curriculum\Models\KnowledgeNode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class GenerateLessonContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $jobId,
    ) {}

    public function handle(LlmRouter $llm): void
    {
        $job = AiGenerationJob::query()->find($this->jobId);

        if ($job === null) {
            return;
        }

        $job->update(['status' => GenerationJobStatus::Processing]);

        try {
            $nodeId = $job->nodeId()
                ?? throw new \RuntimeException('Generation job is missing node_id.');

            $node = KnowledgeNode::query()->findOrFail($nodeId);
            $response = $llm->chat([
                [
                    'role' => 'system',
                    'content' => 'You generate structured lesson content as JSON only. '
                        .'Return {"atoms":[{"kind":"theory|snippet|quiz|summary","body_md":"...","meta":{}}]}. '
                        .'Include at least one theory atom, one snippet, one quiz with meta.answer, and one summary.',
                ],
                [
                    'role' => 'user',
                    'content' => json_encode([
                        'slug' => $node->slug,
                        'title' => $node->title,
                        'summary' => $node->summary,
                        'track' => $node->track->value,
                    ], JSON_THROW_ON_ERROR),
                ],
            ], LlmTask::StructuredJson);

            $payload = $this->decodeAtomsPayload($response->content);
            $version = $this->persistVersion($node, $payload['atoms']);

            $job->update([
                'status' => GenerationJobStatus::Completed,
                'result_version_id' => $version->id,
                'error' => null,
            ]);
        } catch (Throwable $exception) {
            $job->update([
                'status' => GenerationJobStatus::Failed,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /**
     * @return array{atoms: list<array{kind: string, body_md: string, meta: array<string, mixed>}>}
     */
    private function decodeAtomsPayload(string $content): array
    {
        $decoded = json_decode(trim($content), true);

        if (! is_array($decoded) || ! isset($decoded['atoms']) || ! is_array($decoded['atoms'])) {
            throw new \RuntimeException('LLM response did not contain a valid atoms array.');
        }

        $atoms = [];

        foreach ($decoded['atoms'] as $atom) {
            if (! is_array($atom) || ! isset($atom['kind'], $atom['body_md'])) {
                continue;
            }

            $atoms[] = [
                'kind' => (string) $atom['kind'],
                'body_md' => (string) $atom['body_md'],
                'meta' => is_array($atom['meta'] ?? null) ? $atom['meta'] : [],
            ];
        }

        if ($atoms === []) {
            throw new \RuntimeException('LLM response contained no usable atoms.');
        }

        return ['atoms' => $atoms];
    }

    /**
     * @param  list<array{kind: string, body_md: string, meta: array<string, mixed>}>  $atoms
     */
    private function persistVersion(KnowledgeNode $node, array $atoms): ContentVersion
    {
        return DB::transaction(function () use ($node, $atoms) {
            $nextVersionNo = (int) ContentVersion::query()
                ->where('node_id', $node->id)
                ->max('version_no') + 1;

            ContentVersion::query()
                ->where('node_id', $node->id)
                ->where('status', VersionStatus::Active)
                ->update(['status' => VersionStatus::Archived]);

            $version = ContentVersion::query()->create([
                'node_id' => $node->id,
                'version_no' => max(1, $nextVersionNo),
                'status' => VersionStatus::Active,
            ]);

            foreach (array_values($atoms) as $index => $atom) {
                $kind = AtomKind::tryFrom($atom['kind']) ?? AtomKind::Theory;

                ContentAtom::query()->create([
                    'version_id' => $version->id,
                    'kind' => $kind,
                    'body_md' => $atom['body_md'],
                    'meta' => $atom['meta'],
                    'order_in_version' => $index + 1,
                ]);
            }

            return $version->load('atoms');
        });
    }
}
