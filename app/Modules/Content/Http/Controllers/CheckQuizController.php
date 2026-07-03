<?php

namespace App\Modules\Content\Http\Controllers;

use App\Modules\Content\Http\Requests\CheckQuizRequest;
use App\Modules\Content\Services\ContentService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class CheckQuizController extends ApiController
{
    public function __invoke(string $slug, CheckQuizRequest $request, ContentService $content): JsonResponse
    {
        $dto = $request->getDto();

        return $this->respond($content->checkQuiz($slug, $dto->atomId, $dto->answer));
    }
}
