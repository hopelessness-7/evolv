<?php

namespace App\Modules\Curriculum\Http\Controllers;

use App\Modules\Curriculum\Contracts\CurriculumRouteReaderInterface;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class ListNodeRelatedController extends ApiController
{
    public function __invoke(string $slug, CurriculumRouteReaderInterface $curriculum): JsonResponse
    {
        return $this->success([
            'nodes' => array_map(
                fn ($node) => $node->toArray(),
                $curriculum->related($slug),
            ),
        ]);
    }
}
