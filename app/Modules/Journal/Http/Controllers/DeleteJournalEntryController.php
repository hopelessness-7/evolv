<?php

namespace App\Modules\Journal\Http\Controllers;

use App\Modules\Journal\Services\JournalService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeleteJournalEntryController extends ApiController
{
    public function __invoke(int $entryId, Request $request, JournalService $journal): Response
    {
        $journal->delete($request->user(), $entryId);

        return response()->noContent();
    }
}
