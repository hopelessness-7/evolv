<?php

namespace App\Modules\Journal\Http\Controllers;

use App\Modules\Journal\Services\JournalService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListJournalEntriesController extends ApiController
{
    public function __invoke(Request $request, JournalService $journal): JsonResponse
    {
        $nodeSlug = $request->query('node_slug');
        $planDate = $request->query('plan_date');
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 10);

        return $this->respond($journal->list(
            $request->user(),
            is_string($nodeSlug) ? $nodeSlug : null,
            is_string($planDate) ? $planDate : null,
            $page,
            $perPage,
        ));
    }
}
