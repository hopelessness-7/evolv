<?php

namespace App\Modules\Journal\Http\Controllers;

use App\Modules\Journal\Http\Requests\StoreJournalEntryRequest;
use App\Modules\Journal\Services\JournalService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class StoreJournalEntryController extends ApiController
{
    public function __invoke(StoreJournalEntryRequest $request, JournalService $journal): JsonResponse
    {
        $data = $request->validated();

        return $this->created($journal->create(
            $request->user(),
            $request->kind(),
            (string) $data['body'],
            isset($data['node_slug']) ? (string) $data['node_slug'] : null,
            isset($data['plan_date']) ? (string) $data['plan_date'] : null,
        ));
    }
}
