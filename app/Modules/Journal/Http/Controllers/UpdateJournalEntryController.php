<?php

namespace App\Modules\Journal\Http\Controllers;

use App\Modules\Journal\Http\Requests\UpdateJournalEntryRequest;
use App\Modules\Journal\Services\JournalService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class UpdateJournalEntryController extends ApiController
{
    public function __invoke(int $entryId, UpdateJournalEntryRequest $request, JournalService $journal): JsonResponse
    {
        $data = $request->validated();

        return $this->respond($journal->update(
            $request->user(),
            $entryId,
            $request->kind(),
            array_key_exists('body', $data) ? (string) $data['body'] : null,
            array_key_exists('node_slug', $data) && $data['node_slug'] !== null
                ? (string) $data['node_slug']
                : null,
            array_key_exists('plan_date', $data) && $data['plan_date'] !== null
                ? (string) $data['plan_date']
                : null,
            clearNodeSlug: array_key_exists('node_slug', $data) && $data['node_slug'] === null,
            clearPlanDate: array_key_exists('plan_date', $data) && $data['plan_date'] === null,
        ));
    }
}
