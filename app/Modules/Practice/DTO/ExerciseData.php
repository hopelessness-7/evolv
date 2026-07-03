<?php

namespace App\Modules\Practice\DTO;

use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class ExerciseData implements RespondsAsArray
{
    /**
     * @param  list<ExerciseTestData>  $tests
     */
    public function __construct(
        public int $atomId,
        public int $nodeId,
        public string $nodeSlug,
        public string $language,
        public string $starterCode,
        public array $tests,
        public ?string $title = null,
    ) {}

    public function languageId(): int
    {
        $languageIds = config('judge0.language_ids', []);

        if (! isset($languageIds[$this->language])) {
            throw new \InvalidArgumentException("Unsupported exercise language [{$this->language}].");
        }

        return (int) $languageIds[$this->language];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->toPublicArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function toPublicArray(): array
    {
        return [
            'atom_id' => $this->atomId,
            'node_id' => $this->nodeId,
            'node_slug' => $this->nodeSlug,
            'title' => $this->title,
            'language' => $this->language,
            'starter_code' => $this->starterCode,
            'tests' => array_map(
                fn (ExerciseTestData $test) => $test->toArray(includeExpectedOutput: false),
                $this->tests,
            ),
        ];
    }
}
