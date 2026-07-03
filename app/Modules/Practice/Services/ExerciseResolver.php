<?php

namespace App\Modules\Practice\Services;

use App\Modules\Content\Contracts\ContentVersionRepositoryInterface;
use App\Modules\Content\Enums\AtomKind;
use App\Modules\Content\Models\ContentAtom;
use App\Modules\Practice\Contracts\PracticeExerciseReaderInterface;
use App\Modules\Practice\DTO\ExerciseData;
use App\Modules\Practice\DTO\ExerciseTestData;
use App\Modules\Practice\Exceptions\PracticeException;

class ExerciseResolver implements PracticeExerciseReaderInterface
{
    public function __construct(
        private readonly ContentVersionRepositoryInterface $contentVersions,
    ) {}

    public function getExercise(string $nodeSlug, ?int $atomId = null): ExerciseData
    {
        $node = $this->contentVersions->findNodeBySlug($nodeSlug);

        if ($node === null) {
            throw PracticeException::nodeNotFound($nodeSlug);
        }

        $version = $this->contentVersions->findActiveByNodeId($node->id);

        if ($version === null) {
            throw PracticeException::noActiveContent($nodeSlug);
        }

        $atom = $atomId !== null
            ? $this->contentVersions->findAtomByIdInVersion($version, $atomId)
            : $this->contentVersions->findFirstAtomByKindInVersion($version, AtomKind::Exercise);

        if ($atom === null) {
            throw PracticeException::exerciseNotFound($nodeSlug, $atomId);
        }

        if ($atom->kind !== AtomKind::Exercise) {
            throw PracticeException::invalidExerciseAtom($atom->id);
        }

        return $this->mapAtomToExercise($atom, $node->id, $nodeSlug, $node->title);
    }

    private function mapAtomToExercise(
        ContentAtom $atom,
        int $nodeId,
        string $nodeSlug,
        string $nodeTitle,
    ): ExerciseData {
        $meta = is_array($atom->meta) ? $atom->meta : [];

        $language = (string) ($meta['language'] ?? '');
        $starterCode = (string) ($meta['starter_code'] ?? '');
        $rawTests = is_array($meta['tests'] ?? null) ? $meta['tests'] : [];

        if ($language === '' || $starterCode === '' || $rawTests === []) {
            throw PracticeException::invalidExerciseMeta($atom->id);
        }

        $tests = [];

        foreach ($rawTests as $test) {
            if (! is_array($test)) {
                continue;
            }

            $tests[] = ExerciseTestData::fromMeta($test, includeExpectedOutput: true);
        }

        if ($tests === []) {
            throw PracticeException::invalidExerciseMeta($atom->id);
        }

        $languageIds = config('judge0.language_ids', []);

        if (! isset($languageIds[$language])) {
            throw PracticeException::invalidExerciseMeta($atom->id);
        }

        return new ExerciseData(
            atomId: $atom->id,
            nodeId: $nodeId,
            nodeSlug: $nodeSlug,
            language: $language,
            starterCode: $starterCode,
            tests: $tests,
            title: $nodeTitle,
        );
    }
}
