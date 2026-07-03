<?php

namespace App\Modules\Coach\Services;

use App\Models\User;
use App\Modules\AI\DTO\LlmOptions;
use App\Modules\AI\Enums\LlmTask;
use App\Modules\AI\Exceptions\LlmException;
use App\Modules\AI\Services\LlmRouter;
use App\Modules\Coach\DTO\Output\DailyPlanData;
use App\Modules\Coach\Enums\PlanMode;
use App\Modules\Coach\Enums\PlanSource;
use App\Modules\Onboarding\DTO\Output\OnboardingCoachContextData;

class DailyPlanGenerator
{
    public function __construct(
        private readonly LlmRouter $llm,
        private readonly FallbackDailyPlanBuilder $fallbackBuilder,
    ) {}

    public function generate(string $date, User $user, OnboardingCoachContextData $context): DailyPlanData
    {
        try {
            $response = $this->llm->chat(
                $this->buildMessages($date, $context),
                LlmTask::DailyPlan,
                new LlmOptions(
                    task: LlmTask::DailyPlan,
                    temperature: 0.3,
                    jsonMode: true,
                ),
            );

            $parsed = $this->parseResponse($response->content, $date, $context);

            if ($parsed !== null) {
                return $parsed;
            }
        } catch (LlmException) {
            // Fall through to deterministic plan.
        }

        return $this->fallbackBuilder->build($date, $user, $context);
    }

    /**
     * @return list<array{role: string, content: string}>
     */
    private function buildMessages(string $date, OnboardingCoachContextData $context): array
    {
        $mode = $context->personalizedPlanEligible ? PlanMode::Personalized->value : PlanMode::Simplified->value;
        $profileJson = json_encode($context->profileSummary ?? [], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $pendingJson = json_encode($context->pendingQuestionnaires, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $system = $context->coachSystemPrompt
            ?? 'You are Evolv Coach. Create concise, actionable daily learning plans.';

        $user = <<<PROMPT
Generate a daily learning plan for date {$date} in mode "{$mode}".

User profile (JSON):
{$profileJson}

Pending onboarding questionnaires (JSON):
{$pendingJson}

Return JSON only with this shape:
{
  "date": "{$date}",
  "mode": "{$mode}",
  "total_minutes": <int>,
  "greeting": "<string>",
  "steps": [
    {
      "type": "onboarding|lesson|practice|quiz_review|mind|reflection|explore",
      "title": "<string>",
      "description": "<string>",
      "minutes": <int>,
      "pillar": "craft|mind|presence|null",
      "questionnaire_code": "<optional string>",
      "node_id": <optional int>,
      "node_slug": "<optional string>"
    }
  ],
  "reminders": [
    {
      "type": "onboarding_incomplete",
      "questionnaire_code": "<string>",
      "required": <bool>,
      "message": "<string>"
    }
  ]
}

Rules:
- Sum of step minutes must not exceed total_minutes.
- In simplified mode prioritize onboarding completion.
- In personalized mode balance enabled pillars; prefer practice when a coding exercise exists for the current node, otherwise quiz_review.
- Keep steps practical for a self-paced learning app.
PROMPT;

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ];
    }

    private function parseResponse(string $content, string $date, OnboardingCoachContextData $context): ?DailyPlanData
    {
        $payload = json_decode($content, true);

        if (! is_array($payload)) {
            return null;
        }

        $steps = $payload['steps'] ?? null;

        if (! is_array($steps) || $steps === []) {
            return null;
        }

        $modeValue = $payload['mode'] ?? ($context->personalizedPlanEligible ? PlanMode::Personalized->value : PlanMode::Simplified->value);
        $mode = PlanMode::tryFrom((string) $modeValue) ?? PlanMode::Simplified;

        return DailyPlanData::fresh(
            date: (string) ($payload['date'] ?? $date),
            mode: $mode,
            source: PlanSource::Llm,
            totalMinutes: (int) ($payload['total_minutes'] ?? ($context->profileSummary['daily_minutes'] ?? 30)),
            greeting: (string) ($payload['greeting'] ?? ''),
            steps: $steps,
            reminders: is_array($payload['reminders'] ?? null) ? $payload['reminders'] : [],
        );
    }
}
