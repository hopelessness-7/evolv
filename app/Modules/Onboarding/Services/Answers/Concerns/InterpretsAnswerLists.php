<?php

namespace App\Modules\Onboarding\Services\Answers\Concerns;

trait InterpretsAnswerLists
{
    /**
     * @return list<string>
     */
    protected function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(
            array_map(fn ($item) => is_string($item) && $item !== '' ? $item : null, $value),
        ));
    }

    protected function boolAck(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    protected function intRating(mixed $value, int $default = 3, int $min = 1, int $max = 5): int
    {
        $rating = (int) $value;

        return max($min, min($max, $rating ?: $default));
    }

    /**
     * @return list<string>
     */
    protected function tagList(string $prefix, array $values): array
    {
        $tags = [];

        foreach ($values as $value) {
            if (is_string($value) && $value !== '') {
                $tags[] = $prefix.$value;
            }
        }

        return $tags;
    }
}
