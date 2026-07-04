<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GenerateAgentToken;
use App\Models\Server;
use App\Services\AgentScriptGenerator;
use Illuminate\Http\Response;

class AgentInstallController extends Controller
{
    public function store(
        Server $server,
        GenerateAgentToken $generateAgentToken,
        AgentScriptGenerator $scriptGenerator,
    ): Response {
        $token = $generateAgentToken->handle($server);

        return response($scriptGenerator->generate($server, $token), 200, [
            'Content-Type' => 'text/x-shellscript',
            'Content-Disposition' => 'attachment; filename="install-agent.sh"',
        ]);
    }
}
