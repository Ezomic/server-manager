<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ServiceActionRequest;
use App\Models\Server;
use App\Services\ServiceManager;
use Illuminate\Http\JsonResponse;

class ServerServiceController extends Controller
{
    public function index(Server $server, ServiceManager $services): JsonResponse
    {
        return response()->json(['services' => $services->list($server)]);
    }

    public function store(ServiceActionRequest $request, Server $server, ServiceManager $services): JsonResponse
    {
        $validated = $request->validated();

        $result = $services->perform($server, $validated['type'], $validated['name'], $validated['action']);

        return response()->json([
            'successful' => $result->successful,
            'output' => $result->successful ? $result->output : $result->errorOutput,
        ], $result->successful ? 200 : 422);
    }
}
