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

        return $this->mapAtomToExercise($atom, $node);
    }

    private function mapAtomToExercise(
        ContentAtom $atom,
        \App\Modules\Curriculum\Models\KnowledgeNode $node,
    ): ExerciseData {
        $meta = is_array($atom->meta) ? $atom->meta : [];
        $nodeMeta = is_array($node->meta) ? $node->meta : [];

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
            nodeId: $node->id,
            nodeSlug: $node->slug,
            language: $language,
            starterCode: $starterCode,
            tests: $tests,
            title: $node->title,
            prompt: trim((string) $atom->body_md),
            hints: $this->extractHints($meta),
            summary: $node->summary,
            criterion: isset($nodeMeta['criterion']) ? (string) $nodeMeta['criterion'] : null,
        );
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return list<string>
     */
    private function extractHints(array $meta): array
    {
        $hints = [];

        if (isset($meta['hints']) && is_array($meta['hints'])) {
            foreach ($meta['hints'] as $hint) {
                if (is_string($hint) && trim($hint) !== '') {
                    $hints[] = trim($hint);
                }
            }
        }

        if (isset($meta['hint']) && is_string($meta['hint']) && trim($meta['hint']) !== '') {
            $hints[] = trim($meta['hint']);
        }

        if (isset($meta['trace_hint']) && is_string($meta['trace_hint']) && trim($meta['trace_hint']) !== '') {
            $hints[] = trim($meta['trace_hint']);
        }

        return array_values(array_unique($hints));
    }
}
