<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeploymentScriptRequest;
use App\Models\DeploymentScript;
use App\Models\Server;
use Illuminate\Http\RedirectResponse;

class DeploymentScriptController extends Controller
{
    public function store(StoreDeploymentScriptRequest $request, Server $server): RedirectResponse
    {
        $server->deploymentScripts()->create($request->validated());

        return back();
    }

    public function destroy(Server $server, DeploymentScript $deploymentScript): RedirectResponse
    {
        abort_unless($deploymentScript->server_id === $server->id, 404);

        $deploymentScript->delete();

        return back();
    }
}
