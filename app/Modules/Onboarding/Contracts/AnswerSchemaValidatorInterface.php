<?php

namespace App\Modules\Onboarding\Contracts;

interface AnswerSchemaValidatorInterface
{
    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $answers
     * @param  list<string>  $patchKeys
     */
    public function validatePatch(array $schema, array $answers, array $patchKeys): void;

    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $answers
     */
    public function validateComplete(array $schema, array $answers): void;
}
