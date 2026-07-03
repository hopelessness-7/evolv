<?php

namespace App\Modules\Onboarding\Services;

use App\Modules\Onboarding\Contracts\AnswerSchemaValidatorInterface;
use Illuminate\Validation\ValidationException;

class AnswerSchemaValidator implements AnswerSchemaValidatorInterface
{
    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $answers
     * @param  list<string>  $patchKeys
     */
    public function validatePatch(array $schema, array $answers, array $patchKeys): void
    {
        $errors = [];

        foreach ($patchKeys as $key) {
            $question = $this->findQuestion($schema, $key);

            if ($question === null) {
                $errors["answers.{$key}"] = ["Unknown question [{$key}]."];

                continue;
            }

            if (! array_key_exists($key, $answers)) {
                $errors["answers.{$key}"] = ['Answer is required when patching this question.'];

                continue;
            }

            $message = $this->validateAnswer($question, $answers[$key]);

            if ($message !== null) {
                $errors["answers.{$key}"] = [$message];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $answers
     */
    public function validateComplete(array $schema, array $answers): void
    {
        $errors = [];

        foreach ($this->questions($schema) as $question) {
            $id = (string) ($question['id'] ?? '');
            $required = (bool) ($question['required'] ?? false);
            $hasValue = array_key_exists($id, $answers);

            if ($required && ! $hasValue) {
                $errors["answers.{$id}"] = ['This question is required.'];

                continue;
            }

            if (! $hasValue) {
                continue;
            }

            $message = $this->validateAnswer($question, $answers[$id]);

            if ($message !== null) {
                $errors["answers.{$id}"] = [$message];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return list<array<string, mixed>>
     */
    private function questions(array $schema): array
    {
        $questions = $schema['questions'] ?? [];

        return is_array($questions) ? array_values(array_filter($questions, 'is_array')) : [];
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>|null
     */
    private function findQuestion(array $schema, string $id): ?array
    {
        foreach ($this->questions($schema) as $question) {
            if (($question['id'] ?? null) === $id) {
                return $question;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $question
     */
    private function validateAnswer(array $question, mixed $value): ?string
    {
        $type = (string) ($question['type'] ?? 'text');
        $required = (bool) ($question['required'] ?? false);

        if ($value === null || $value === '') {
            return $required ? 'This field is required.' : null;
        }

        return match ($type) {
            'text' => $this->validateText($question, $value),
            'timezone' => is_string($value) && $value !== '' ? null : 'Timezone must be a non-empty string.',
            'single_select' => $this->validateSingleSelect($question, $value),
            'multi_select' => $this->validateMultiSelect($question, $value),
            'number' => $this->validateNumber($question, $value),
            'scale_1_5' => $this->validateScale($question, $value),
            'boolean_ack' => $this->validateBooleanAck($value),
            default => is_scalar($value) || is_array($value) ? null : 'Invalid answer format.',
        };
    }

    /**
     * @param  array<string, mixed>  $question
     */
    private function validateText(array $question, mixed $value): ?string
    {
        if (! is_string($value)) {
            return 'Must be a string.';
        }

        $maxLength = (int) ($question['max_length'] ?? 255);

        if (mb_strlen($value) > $maxLength) {
            return "Must not exceed {$maxLength} characters.";
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $question
     */
    private function validateSingleSelect(array $question, mixed $value): ?string
    {
        if (! is_string($value) && ! is_int($value)) {
            return 'Must be a single option value.';
        }

        $allowed = $this->optionValues($question);

        if ($allowed !== [] && ! in_array((string) $value, $allowed, true)) {
            return 'Selected value is not allowed.';
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $question
     */
    private function validateMultiSelect(array $question, mixed $value): ?string
    {
        if (! is_array($value)) {
            return 'Must be an array of option values.';
        }

        $allowed = $this->optionValues($question);

        foreach ($value as $item) {
            if (! is_string($item) && ! is_int($item)) {
                return 'Each selected value must be a string.';
            }

            if ($allowed !== [] && ! in_array((string) $item, $allowed, true)) {
                return 'One or more selected values are not allowed.';
            }
        }

        if (($question['required'] ?? false) && $value === []) {
            return 'At least one option must be selected.';
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $question
     */
    private function validateNumber(array $question, mixed $value): ?string
    {
        if (! is_numeric($value)) {
            return 'Must be a number.';
        }

        $number = (float) $value;
        $min = $question['min'] ?? null;
        $max = $question['max'] ?? null;

        if ($min !== null && $number < (float) $min) {
            return "Must be at least {$min}.";
        }

        if ($max !== null && $number > (float) $max) {
            return "Must not exceed {$max}.";
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $question
     */
    private function validateScale(array $question, mixed $value): ?string
    {
        if (! is_numeric($value)) {
            return 'Must be a number.';
        }

        $min = (int) ($question['min'] ?? 1);
        $max = (int) ($question['max'] ?? 5);
        $intValue = (int) $value;

        if ($intValue < $min || $intValue > $max) {
            return "Must be between {$min} and {$max}.";
        }

        return null;
    }

    private function validateBooleanAck(mixed $value): ?string
    {
        if ($value === true || $value === 1 || $value === '1' || $value === 'true') {
            return null;
        }

        return 'Acknowledgement is required.';
    }

    /**
     * @param  array<string, mixed>  $question
     * @return list<string>
     */
    private function optionValues(array $question): array
    {
        $options = $question['options'] ?? [];

        if (! is_array($options)) {
            return [];
        }

        $values = [];

        foreach ($options as $option) {
            if (is_array($option) && isset($option['value'])) {
                $values[] = (string) $option['value'];
            }
        }

        return $values;
    }
}
