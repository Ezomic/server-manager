<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\RecordMetric;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMetricRequest;
use App\Models\Server;
use Illuminate\Http\JsonResponse;

class AgentMetricController extends Controller
{
    public function store(StoreMetricRequest $request, RecordMetric $recordMetric): JsonResponse
    {
        $token = (string) $request->bearerToken();

        abort_if($token === '', 401);

        $server = Server::where('agent_token', $token)->first();

        abort_unless($server !== null, 401);

        $recordMetric->handle($server, $request->validated());

        return response()->json(['status' => 'ok'], 201);
    }
}
