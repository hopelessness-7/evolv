<?php

namespace App\Modules\Content\Services;

use App\Modules\Content\Contracts\ContentVersionRepositoryInterface;
use App\Modules\Content\DTO\Output\NodeContentData;
use App\Modules\Content\DTO\Output\QuizCheckResultData;
use App\Modules\Content\Enums\AtomKind;
use App\Modules\Content\Exceptions\ContentException;
use App\Modules\Curriculum\Services\CurriculumService;

class ContentService
{
    public function __construct(
        private readonly CurriculumService $curriculum,
        private readonly ContentVersionRepositoryInterface $versions,
    ) {}

    public function getNodeContent(string $slug): NodeContentData
    {
        $node = $this->curriculum->getNode($slug);
        $version = $this->versions->findActiveByNodeId($node->id)
            ?? throw ContentException::notFoundForNode($slug);

        $version->loadMissing('node');

        return NodeContentData::fromVersion($version);
    }

    public function checkQuiz(string $slug, int $atomId, string $answer): QuizCheckResultData
    {
        $node = $this->curriculum->getNode($slug);
        $version = $this->versions->findActiveByNodeId($node->id)
            ?? throw ContentException::notFoundForNode($slug);

        $atom = $version->atoms->firstWhere('id', $atomId)
            ?? throw ContentException::atomNotFound($atomId);

        if ($atom->kind !== AtomKind::Quiz) {
            throw ContentException::notAQuizAtom($atomId);
        }

        $expected = $atom->meta['answer'] ?? null;

        if (! is_string($expected) || $expected === '') {
            throw ContentException::quizAnswerMissing($atomId);
        }

        $correct = strcasecmp(trim($answer), trim($expected)) === 0;

        return new QuizCheckResultData(
            atomId: $atomId,
            correct: $correct,
        );
    }
}
