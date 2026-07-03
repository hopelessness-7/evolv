<?php

namespace App\Modules\AI\Exceptions;

use App\Modules\Shared\Exceptions\ApiException;

class AiException extends ApiException
{
    public static function contentAlreadyExists(string $slug): self
    {
        return new self("Active content already exists for node [{$slug}].", 409, 'content_already_exists');
    }

    public static function generationInProgress(string $slug): self
    {
        return new self("Content generation is already in progress for node [{$slug}].", 409, 'generation_in_progress');
    }

    public static function generationFailed(int $jobId, string $reason): self
    {
        return new self("AI generation job [{$jobId}] failed: {$reason}", 500, 'generation_failed');
    }
}
