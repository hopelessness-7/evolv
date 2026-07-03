<?php

namespace App\Modules\Onboarding\Services;

use App\Modules\Onboarding\Contracts\OnboardingPromptComposerInterface;
use App\Modules\Onboarding\DTO\Output\ComposedPromptsData;
use App\Modules\Onboarding\DTO\Output\InterpretedAnswersData;
use App\Modules\Onboarding\Models\OnboardingSession;
use App\Modules\Onboarding\Models\Questionnaire;
use App\Modules\Onboarding\Models\UserProfile;

class PromptComposer implements OnboardingPromptComposerInterface
{
    public function compose(OnboardingSession $session, Questionnaire $questionnaire, InterpretedAnswersData $interpreted, ?UserProfile $profile = null): ComposedPromptsData
    {
        $templates = $questionnaire->prompt_templates ?? [];
        $variables = $this->buildVariables($profile, $interpreted);
        $prompts = [];

        foreach ($templates as $key => $template) {
            if (is_string($template)) {
                $prompts[$key] = $this->render($template, $variables);
            }
        }

        return new ComposedPromptsData(prompts: $prompts);
    }

    /** @return array<string, mixed> */
    private function buildVariables(?UserProfile $profile, InterpretedAnswersData $interpreted): array
    {
        $context = [];

        foreach ($profile?->facets ?? [] as $block) {
            if (is_array($block)) {
                $context = array_merge($context, $block);
            }
        }

        return array_merge($context, $interpreted->facets);
    }

    /** @param  array<string, mixed>  $variables */
    private function render(string $template, array $variables): string
    {
        return (string) preg_replace_callback(
            '/\{\{(\w+)\}\}/',
            fn (array $matches): string => $this->stringifyValue($variables[$matches[1]] ?? null),
            $template,
        );
    }

    private function stringifyValue(mixed $value): string
    {
        if (is_array($value)) {
            $items = array_values(array_filter(
                $value,
                fn ($item) => is_string($item) && $item !== '',
            ));

            return implode(', ', $items);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return '';
        }

        return (string) $value;
    }
}
