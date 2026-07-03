<?php

namespace App\Modules\Content\Http\Controllers;

use App\Modules\Content\Services\ContentService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class ShowNodeContentController extends ApiController
{
    public function __invoke(string $slug, ContentService $content): JsonResponse
    {
        return $this->respond($content->getNodeContent($slug));
    }
}
