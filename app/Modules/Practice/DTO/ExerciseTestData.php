<?php

namespace App\Modules\Practice\DTO;

final readonly class ExerciseTestData
{
    public function __construct(
        public string $label,
        public string $stdin = '',
        public ?string $expectedOutput = null,
    ) {}

    /**
     * @param  array<string, mixed>  $test
     */
    public static function fromMeta(array $test, bool $includeExpectedOutput = true): self
    {
        return new self(
            label: (string) ($test['label'] ?? 'Test'),
            stdin: (string) ($test['stdin'] ?? ''),
            expectedOutput: $includeExpectedOutput
                ? (isset($test['expected_output']) ? (string) $test['expected_output'] : null)
                : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(bool $includeExpectedOutput = true): array
    {
        $payload = [
            'label' => $this->label,
            'stdin' => $this->stdin,
        ];

        if ($includeExpectedOutput && $this->expectedOutput !== null) {
            $payload['expected_output'] = $this->expectedOutput;
        }

        return $payload;
    }
}
