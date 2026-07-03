<?php

namespace App\Modules\Practice\Exceptions;

use App\Modules\Shared\Exceptions\ApiException;

class PracticeException extends ApiException
{
    public static function nodeNotFound(string $slug): self
    {
        return new self("Curriculum node [{$slug}] not found.", 404, 'node_not_found');
    }

    public static function noActiveContent(string $slug): self
    {
        return new self("No active content for node [{$slug}].", 404, 'no_active_content');
    }

    public static function exerciseNotFound(string $slug, ?int $atomId): self
    {
        $suffix = $atomId !== null ? " atom [{$atomId}]" : '';

        return new self("Exercise not found for node [{$slug}]{$suffix}.", 404, 'exercise_not_found');
    }

    public static function invalidExerciseAtom(int $atomId): self
    {
        return new self("Atom [{$atomId}] is not an exercise.", 422, 'invalid_exercise_atom');
    }

    public static function invalidExerciseMeta(int $atomId): self
    {
        return new self("Exercise atom [{$atomId}] has invalid meta.", 422, 'invalid_exercise_meta');
    }
}
