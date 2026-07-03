<?php

namespace App\Modules\Curriculum\Exceptions;

use App\Modules\Shared\Exceptions\ApiException;

class CurriculumException extends ApiException
{
    public static function nodeNotFound(string $slug): self
    {
        return new self("Knowledge node [{$slug}] not found.", 404, 'node_not_found');
    }
}
