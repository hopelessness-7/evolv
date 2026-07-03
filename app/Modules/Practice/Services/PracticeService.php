<?php

namespace App\Modules\Practice\Services;

use App\Models\User;
use App\Modules\AI\DTO\LlmOptions;
use App\Modules\AI\Enums\LlmTask;
use App\Modules\AI\Services\LlmRouter;
use App\Modules\Practice\Contracts\AttemptRepositoryInterface;
use App\Modules\Practice\Contracts\PracticeExerciseReaderInterface;
use App\Modules\Practice\DTO\ExerciseData;
use App\Modules\Practice\DTO\Input\SubmitAttemptData;
use App\Modules\Practice\DTO\Output\AttemptResultData;
use App\Modules\Practice\Enums\AttemptKind;
use App\Modules\Practice\Enums\AttemptVerdict;
use App\Modules\Practice\Models\Attempt;

class PracticeService
{
    public function __construct(
        private readonly PracticeExerciseReaderInterface $exercises,
        private readonly ExerciseRunner $runner,
        private readonly AttemptRepositoryInterface $attempts,
        private readonly UserSkillUpdater $skillUpdater,
        private readonly LlmRouter $llm,
    ) {}

    public function getExercise(string $slug): ExerciseData
    {
        return $this->exercises->getExercise($slug);
    }

    public function submitAttempt(User $user, string $slug, SubmitAttemptData $data): AttemptResultData
    {
        $exercise = $this->exercises->getExercise($slug, $data->atomId);

        $run = $this->runner->run($exercise, $data->code);

        $errorTags = $run['verdict'] === AttemptVerdict::Accepted
            ? []
            : $this->deriveErrorTags($data->code, $run['stderr'], $run['verdict']);

        $lastJudge0Response = $run['results'] !== []
            ? end($run['results'])->judge0Response
            : null;

        $attempt = new Attempt([
            'user_id' => $user->id,
            'node_id' => $exercise->nodeId,
            'kind' => AttemptKind::CodeExercise,
            'payload' => [
                'atom_id' => $data->atomId,
                'code' => $data->code,
                'passed_tests' => $run['passed_tests'],
                'total_tests' => $run['total_tests'],
            ],
            'verdict' => $run['verdict'],
            'error_tags' => $errorTags,
            'duration_ms' => $run['duration_ms'],
            'judge0_response' => $lastJudge0Response,
        ]);

        $this->attempts->save($attempt);

        if ($run['verdict'] === AttemptVerdict::Accepted) {
            $this->skillUpdater->recordAcceptedPractice($user, $exercise->nodeId);
        }

        return new AttemptResultData(
            verdict: $run['verdict'],
            passedTests: $run['passed_tests'],
            totalTests: $run['total_tests'],
            stdout: $run['stdout'],
            stderr: $run['stderr'],
            attemptId: $attempt->id,
        );
    }

    /**
     * @return list<string>
     */
    private function deriveErrorTags(string $code, ?string $stderr, AttemptVerdict $verdict): array
    {
        try {
            $response = $this->llm->chat(
                [
                    [
                        'role' => 'user',
                        'content' => implode("\n", [
                            'Analyze this failed code exercise submission.',
                            'Return a JSON array of short error tag strings (e.g. ["off_by_one", "syntax_error"]).',
                            'Verdict: '.$verdict->value,
                            'Code:',
                            $code,
                            'Stderr:',
                            $stderr ?? '(none)',
                        ]),
                    ],
                ],
                LlmTask::CodeReview,
                new LlmOptions(task: LlmTask::CodeReview, jsonMode: true, temperature: 0.2),
            );

            $decoded = json_decode($response->content, true);

            if (! is_array($decoded)) {
                return [];
            }

            return array_values(array_filter(
                array_map(fn ($tag) => is_string($tag) ? $tag : null, $decoded),
                fn (?string $tag) => $tag !== null && $tag !== '',
            ));
        } catch (\Throwable) {
            return [];
        }
    }
}
