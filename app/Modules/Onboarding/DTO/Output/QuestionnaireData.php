<?php

namespace App\Modules\Onboarding\DTO\Output;

use App\Modules\Onboarding\Enums\Pillar;
use App\Modules\Onboarding\Enums\Tier;
use App\Modules\Onboarding\Models\Questionnaire;
use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class QuestionnaireData implements RespondsAsArray
{
    public function __construct(
        public string $code,
        public string $version,
        public ?Pillar $pillar,
        public Tier $tier,
        public string $title,
        public ?string $description,
        public array $schema,
        public ?array $promptTemplates,
    ) {}

    public static function fromModel(Questionnaire $questionnaire): self
    {
        return new self(
            code: $questionnaire->code,
            version: $questionnaire->version,
            pillar: $questionnaire->pillar,
            tier: $questionnaire->tier,
            title: $questionnaire->title,
            description: $questionnaire->description,
            schema: $questionnaire->schema ?? [],
            promptTemplates: $questionnaire->prompt_templates,
        );
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'version' => $this->version,
            'pillar' => $this->pillar?->value,
            'tier' => $this->tier->value,
            'title' => $this->title,
            'description' => $this->description,
            'schema' => $this->schema,
            'prompt_templates' => $this->promptTemplates,
        ];
    }
}
