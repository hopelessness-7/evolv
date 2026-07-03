<?php

namespace App\Modules\AI\DTO\Output;

use App\Modules\AI\Enums\GenerationJobKind;
use App\Modules\AI\Enums\GenerationJobStatus;
use App\Modules\AI\Models\AiGenerationJob;
use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class GenerationJobData implements RespondsAsArray
{
    public function __construct(
        public int $id,
        public string $kind,
        public string $status,
        public ?int $resultVersionId,
        public ?string $error,
    ) {}

    public static function fromModel(AiGenerationJob $job): self
    {
        return new self(
            id: $job->id,
            kind: $job->kind instanceof GenerationJobKind ? $job->kind->value : (string) $job->kind,
            status: $job->status instanceof GenerationJobStatus ? $job->status->value : (string) $job->status,
            resultVersionId: $job->result_version_id,
            error: $job->error,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'kind' => $this->kind,
            'status' => $this->status,
            'result_version_id' => $this->resultVersionId,
            'error' => $this->error,
        ];
    }
}
