<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TailLogRequest;
use App\Models\Server;
use App\Services\LogTailer;
use Illuminate\Http\JsonResponse;

class ServerLogController extends Controller
{
    public function index(TailLogRequest $request, Server $server, LogTailer $tailer): JsonResponse
    {
        $validated = $request->validated();

        $result = $tailer->tail($server, $validated['path'], $validated['lines'] ?? 200);

        return response()->json([
            'successful' => $result->successful,
            'output' => $result->successful ? $result->output : $result->errorOutput,
        ]);
    }
}
