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
        $result = $tailer->tail($server, $request->string('path')->value(), $request->integer('lines', 200));

        return response()->json([
            'successful' => $result->successful,
            'output' => $result->successful ? $result->output : $result->errorOutput,
        ]);
    }
}
