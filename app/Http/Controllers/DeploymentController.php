<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RunDeployment;
use App\Models\Deployment;
use App\Models\DeploymentScript;
use App\Models\Server;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeploymentController extends Controller
{
    public function index(Request $request, Server $server): JsonResponse
    {
        return response()->json([
            'deployments' => $server->deployments()
                ->with(['deploymentScript:id,name', 'user:id,name'])
                ->latest('started_at')
                ->limit(20)
                ->get(),
        ]);
    }

    public function store(Request $request, Server $server, DeploymentScript $deploymentScript, RunDeployment $runDeployment): JsonResponse
    {
        abort_unless($deploymentScript->server_id === $server->id, 404);

        $deployment = $runDeployment->handle($deploymentScript, $request->user());

        return response()->json(['deployment' => $deployment], 201);
    }

    public function show(Server $server, Deployment $deployment): JsonResponse
    {
        abort_unless($deployment->server_id === $server->id, 404);

        return response()->json(['deployment' => $deployment]);
    }
}
