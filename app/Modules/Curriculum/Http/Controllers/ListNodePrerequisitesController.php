<?php

namespace App\Modules\Curriculum\Http\Controllers;

use App\Modules\Curriculum\Contracts\CurriculumRouteReaderInterface;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListNodePrerequisitesController extends ApiController
{
    public function __invoke(
        string $slug,
        Request $request,
        CurriculumRouteReaderInterface $curriculum,
    ): JsonResponse {
        $transitive = ! $request->boolean('direct_only');

        return $this->success([
            'nodes' => array_map(
                fn ($node) => $node->toArray(),
                $curriculum->prerequisites($slug, $transitive),
            ),
        ]);
    }
}
