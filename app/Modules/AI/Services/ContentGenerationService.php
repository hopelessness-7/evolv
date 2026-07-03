<?php

namespace App\Modules\AI\Services;

use App\Modules\AI\DTO\Output\GenerationJobData;
use App\Modules\AI\Enums\GenerationJobKind;
use App\Modules\AI\Enums\GenerationJobStatus;
use App\Modules\AI\Exceptions\AiException;
use App\Modules\AI\Jobs\GenerateLessonContentJob;
use App\Modules\AI\Models\AiGenerationJob;
use App\Modules\Content\Contracts\ContentVersionRepositoryInterface;
use App\Modules\Curriculum\Contracts\NodeRepositoryInterface;
use App\Modules\Curriculum\Exceptions\CurriculumException;

class ContentGenerationService
{
    public function __construct(
        private readonly NodeRepositoryInterface $nodes,
        private readonly ContentVersionRepositoryInterface $versions,
    ) {}

    public function generateLessonForNode(int $nodeId): GenerationJobData
    {
        $node = \App\Modules\Curriculum\Models\KnowledgeNode::query()->find($nodeId)
            ?? throw CurriculumException::nodeNotFound((string) $nodeId);

        return $this->generateLessonForSlug($node->slug);
    }

    public function generateLessonForSlug(string $slug): GenerationJobData
    {
        $node = $this->nodes->findBySlug($slug)
            ?? throw CurriculumException::nodeNotFound($slug);

        if ($this->versions->findActiveByNodeId($node->id) !== null) {
            throw AiException::contentAlreadyExists($slug);
        }

        $pending = AiGenerationJob::query()
            ->where('kind', GenerationJobKind::LessonContent)
            ->whereIn('status', [
                GenerationJobStatus::Pending,
                GenerationJobStatus::Processing,
            ])
            ->where('input->node_id', $node->id)
            ->exists();

        if ($pending) {
            throw AiException::generationInProgress($slug);
        }

        $job = AiGenerationJob::query()->create([
            'kind' => GenerationJobKind::LessonContent,
            'input' => [
                'node_id' => $node->id,
                'slug' => $node->slug,
                'title' => $node->title,
                'summary' => $node->summary,
            ],
            'status' => GenerationJobStatus::Pending,
        ]);

        GenerateLessonContentJob::dispatch($job->id);

        return GenerationJobData::fromModel($job);
    }
}
