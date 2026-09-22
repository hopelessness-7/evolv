<?php

namespace App\Modules\Curriculum\Enums;

enum Track: string
{
    case Php = 'php';
    case Laravel = 'laravel';
    case Sql = 'sql';
    case Javascript = 'javascript';
    case Python = 'python';
    case Go = 'go';
    case Algorithms = 'algorithms';

    /**
     * Entry knowledge-node slug for LearningPath / Curriculum.
     */
    public function entrySlug(string $difficultyBand = 'beginner'): string
    {
        if ($this === self::Laravel) {
            return 'laravel.n1';
        }

        $suffix = $difficultyBand === 'advanced' ? 'overview' : 'intro';

        return $this->value.'.'.$suffix;
    }
}
