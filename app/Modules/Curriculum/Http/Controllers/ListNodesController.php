<?php

namespace App\Modules\Curriculum\Http\Controllers;

use App\Modules\Curriculum\Contracts\CurriculumRouteReaderInterface;
use App\Modules\Curriculum\Enums\Track;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListNodesController extends ApiController
{
    public function __invoke(Request $request, CurriculumRouteReaderInterface $curriculum): JsonResponse
    {
        $track = $request->query('track');
        $trackEnum = is_string($track) && $track !== '' ? Track::from($track) : null;

        return $this->respond($curriculum->listNodes($trackEnum));
    }
}
