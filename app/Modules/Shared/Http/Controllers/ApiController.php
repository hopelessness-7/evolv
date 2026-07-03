<?php

namespace App\Modules\Shared\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Contracts\RespondsAsArray;
use Illuminate\Http\JsonResponse;

abstract class ApiController extends Controller
{
    /**
     * @param  array<string, mixed>|RespondsAsArray  $payload
     */
    protected function respond(array|RespondsAsArray $payload, int $status = 200): JsonResponse
    {
        $data = $payload instanceof RespondsAsArray ? $payload->toArray() : $payload;

        return response()->json($data, $status);
    }

    /**
     * @param  array<string, mixed>|RespondsAsArray  $payload
     */
    protected function created(array|RespondsAsArray $payload): JsonResponse
    {
        return $this->respond($payload, 201);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function success(array $payload = [], int $status = 200): JsonResponse
    {
        return response()->json($payload, $status);
    }
}
