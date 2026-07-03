<?php

namespace Tests\Unit\Onboarding;

use App\Modules\Onboarding\Services\AnswerSchemaValidator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AnswerSchemaValidatorTest extends TestCase
{
    private AnswerSchemaValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new AnswerSchemaValidator;
    }

    public function test_validate_patch_rejects_unknown_question(): void
    {
        $this->expectException(ValidationException::class);

        $this->validator->validatePatch(
            ['questions' => [['id' => 'known', 'type' => 'text', 'required' => true]]],
            ['known' => 'ok', 'unknown' => 'x'],
            ['unknown'],
        );
    }

    public function test_validate_patch_rejects_invalid_single_select(): void
    {
        $this->expectException(ValidationException::class);

        $this->validator->validatePatch(
            [
                'questions' => [[
                    'id' => 'coach_tone',
                    'type' => 'single_select',
                    'required' => true,
                    'options' => [
                        ['value' => 'direct', 'label' => 'Direct'],
                    ],
                ]],
            ],
            ['coach_tone' => 'invalid'],
            ['coach_tone'],
        );
    }

    public function test_validate_complete_requires_all_required_questions(): void
    {
        $this->expectException(ValidationException::class);

        $this->validator->validateComplete(
            [
                'questions' => [
                    ['id' => 'display_name', 'type' => 'text', 'required' => true],
                    ['id' => 'daily_minutes', 'type' => 'number', 'required' => true, 'min' => 5, 'max' => 480],
                ],
            ],
            ['display_name' => 'Alex'],
        );
    }

    public function test_validate_complete_accepts_valid_core_subset(): void
    {
        $this->validator->validateComplete(
            [
                'questions' => [
                    ['id' => 'display_name', 'type' => 'text', 'required' => true, 'max_length' => 64],
                    ['id' => 'daily_minutes', 'type' => 'number', 'required' => true, 'min' => 5, 'max' => 480],
                    ['id' => 'wellbeing_disclaimer', 'type' => 'boolean_ack', 'required' => true],
                ],
            ],
            [
                'display_name' => 'Alex',
                'daily_minutes' => 30,
                'wellbeing_disclaimer' => true,
            ],
        );

        $this->assertTrue(true);
    }
}
