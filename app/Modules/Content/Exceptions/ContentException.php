<?php

namespace App\Modules\Content\Exceptions;

use App\Modules\Shared\Exceptions\ApiException;

class ContentException extends ApiException
{
    public static function notFoundForNode(string $slug): self
    {
        return new self("Active content for node [{$slug}] not found.", 404, 'content_not_found');
    }

    public static function atomNotFound(int $atomId): self
    {
        return new self("Content atom [{$atomId}] not found.", 404, 'content_atom_not_found');
    }

    public static function notAQuizAtom(int $atomId): self
    {
        return new self("Content atom [{$atomId}] is not a quiz.", 422, 'content_atom_not_quiz');
    }

    public static function quizAnswerMissing(int $atomId): self
    {
        return new self("Quiz atom [{$atomId}] has no configured answer.", 422, 'quiz_answer_missing');
    }
}
