<?php

namespace App\Modules\Practice\Contracts;

use App\Modules\Practice\DTO\ExerciseData;

interface PracticeExerciseReaderInterface
{
    public function getExercise(string $nodeSlug, ?int $atomId = null): ExerciseData;
}
