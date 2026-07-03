<?php

namespace App\Modules\AI\Http\Controllers;

use App\Modules\AI\Services\ContentGenerationService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class GenerateLessonContentController extends ApiController
{
    public function __invoke(string $slug, ContentGenerationService $generation): JsonResponse
    {
        return $this->created($generation->generateLessonForSlug($slug));
    }
}
