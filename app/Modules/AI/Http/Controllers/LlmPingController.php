<?php

namespace App\Modules\AI\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AI\Enums\LlmTask;
use App\Modules\AI\Services\LlmRouter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LlmPingController extends Controller
{
    public function __invoke(Request $request, LlmRouter $llm): JsonResponse
    {
        $response = $llm->chat([
            ['role' => 'user', 'content' => 'Reply with exactly: pong'],
        ], LlmTask::Chat);

        return response()->json([
            'model' => $response->model,
            'reply' => trim($response->content),
        ]);
    }
}
