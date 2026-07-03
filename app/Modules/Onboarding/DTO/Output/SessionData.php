<?php

namespace App\Modules\Onboarding\DTO\Output;

use App\Modules\Onboarding\Enums\SessionStatus;
use App\Modules\Onboarding\Models\OnboardingSession;
use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class SessionData implements RespondsAsArray
{
    public function __construct(
        public int $id,
        public string $questionnaireCode,
        public string $questionnaireVersion,
        public SessionStatus $status,
        public array $answers,
        public ?array $interpreted,
        public ?array $composedPrompts,
        public ?string $completedAt,
    ) {}

    public static function fromModel(OnboardingSession $session): self
    {
        return new self(
            id: $session->id,
            questionnaireCode: $session->questionnaire_code,
            questionnaireVersion: $session->questionnaire_version,
            status: $session->status,
            answers: $session->answers ?? [],
            interpreted: $session->interpreted,
            composedPrompts: $session->composed_prompts,
            completedAt: $session->completed_at?->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'questionnaire_code' => $this->questionnaireCode,
            'questionnaire_version' => $this->questionnaireVersion,
            'status' => $this->status->value,
            'answers' => $this->answers,
            'interpreted' => $this->interpreted,
            'composed_prompts' => $this->composedPrompts,
            'completed_at' => $this->completedAt,
        ];
    }
}
