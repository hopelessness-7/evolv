<?php

namespace Tests\Unit\Onboarding;

use App\Models\User;
use App\Modules\Onboarding\DTO\Output\InterpretedAnswersData;
use App\Modules\Onboarding\Enums\SessionStatus;
use App\Modules\Onboarding\Models\OnboardingSession;
use App\Modules\Onboarding\Models\Questionnaire;
use App\Modules\Onboarding\Models\UserProfile;
use App\Modules\Onboarding\Services\Answers\CoreAnswerInterpreter;
use App\Modules\Onboarding\Services\Answers\CraftLiteAnswerInterpreter;
use App\Modules\Onboarding\Services\Answers\MindLiteAnswerInterpreter;
use App\Modules\Onboarding\Services\PromptComposer;
use App\Modules\Onboarding\Services\ProgressEvaluator;
use App\Modules\Onboarding\Services\QuestionnaireSelector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_interpreter_builds_facets_and_tags(): void
    {
        $result = (new CoreAnswerInterpreter)->interpret([
            'display_name' => 'Alex',
            'daily_minutes' => 45,
            'weekly_days' => '4_5',
            'enabled_pillars' => ['craft', 'mind'],
            'coach_tone' => 'direct',
        ]);

        $this->assertSame('Alex', $result->facets['display_name']);
        $this->assertSame(203, $result->facets['weekly_minutes_estimate']);
        $this->assertContains('pillar:craft', $result->tags);
    }

    public function test_craft_lite_interpreter_computes_difficulty(): void
    {
        $result = (new CraftLiteAnswerInterpreter)->interpret([
            'experience_level' => 'junior',
            'learning_goal' => 'interview',
            'target_languages' => ['php'],
        ]);

        $this->assertSame('intermediate', $result->facets['difficulty_band']);
        $this->assertSame('interview', $result->facets['path_priority']);
    }

    public function test_mind_lite_suggests_extended_packs(): void
    {
        $result = (new MindLiteAnswerInterpreter)->interpret([
            'improvement_areas' => ['focus', 'stress'],
            'self_rating_focus' => 2,
            'self_rating_stress' => 5,
            'wellbeing_disclaimer' => true,
        ]);

        $this->assertContains('mind_focus', $result->facets['suggested_extended_packs']);
        $this->assertContains('pack:mind_focus', $result->tags);
    }

    public function test_prompt_composer_merges_profile_facets(): void
    {
        $profile = new UserProfile([
            'facets' => [
                'core' => [
                    'coach_tone' => 'supportive',
                    'display_name' => 'Alex',
                ],
            ],
        ]);

        $questionnaire = new Questionnaire([
            'prompt_templates' => [
                'encouragement' => 'Hello {{display_name}}, tone {{coach_tone}}, langs {{target_languages}}.',
            ],
        ]);

        $result = (new PromptComposer)->compose(
            new OnboardingSession,
            $questionnaire,
            new InterpretedAnswersData(facets: ['target_languages' => ['php', 'go']]),
            $profile,
        );

        $this->assertSame(
            'Hello Alex, tone supportive, langs php, go.',
            $result->prompts['encouragement'],
        );
    }

    public function test_mind_only_user_completes_without_craft_lite(): void
    {
        $user = User::factory()->create();
        $profile = UserProfile::query()->create([
            'user_id' => $user->id,
            'enabled_pillars' => ['mind'],
            'facets' => ['core' => ['enabled_pillars' => ['mind']]],
            'core_completed_at' => now(),
        ]);

        $sessions = collect([
            tap(new OnboardingSession, fn ($s) => $s->forceFill([
                'questionnaire_code' => 'core',
                'status' => SessionStatus::Completed,
            ])),
        ]);

        $status = app(ProgressEvaluator::class)->evaluate($user, $profile, $sessions);

        $this->assertTrue($status->isComplete);
        $this->assertSame('extended', $status->phase);
        $this->assertSame('mind_lite', $status->available[0]->code);
    }

    public function test_selector_offers_mind_extended_packs(): void
    {
        $profile = new UserProfile([
            'enabled_pillars' => ['mind'],
            'facets' => [
                'mind_lite' => ['suggested_extended_packs' => ['mind_focus', 'mind_wellbeing']],
            ],
        ]);

        $completed = collect([
            tap(new OnboardingSession, fn ($s) => $s->forceFill([
                'questionnaire_code' => 'core',
                'status' => SessionStatus::Completed,
            ])),
            tap(new OnboardingSession, fn ($s) => $s->forceFill([
                'questionnaire_code' => 'mind_lite',
                'status' => SessionStatus::Completed,
            ])),
        ]);

        $codes = array_map(
            fn ($q) => $q->code,
            (new QuestionnaireSelector)->availableFor(User::factory()->make(), $profile, $completed),
        );

        $this->assertContains('mind_focus', $codes);
        $this->assertContains('mind_wellbeing', $codes);
    }

    public function test_mind_extended_interpreter_preserves_pack_code(): void
    {
        $result = (new \App\Modules\Onboarding\Services\Answers\MindExtendedAnswerInterpreter('mind_focus'))
            ->interpret([
                'distraction_sources' => ['phone'],
                'deep_work_experience' => 'sometimes',
                'focus_disclaimer' => true,
            ]);

        $this->assertSame('mind_focus', $result->facets['pack_code']);
        $this->assertContains('pack:mind_focus', $result->tags);
    }
}
