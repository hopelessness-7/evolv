<?php

namespace App\Modules\Curriculum\Http\Controllers;

use App\Modules\Curriculum\Contracts\CurriculumRouteReaderInterface;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EntryNodesController extends ApiController
{
    public function __invoke(Request $request, CurriculumRouteReaderInterface $curriculum): JsonResponse
    {
        return $this->success([
            'entry_nodes' => array_map(
                fn ($node) => $node->toArray(),
                $curriculum->entryNodes($request->user()),
            ),
        ]);
    }
}
