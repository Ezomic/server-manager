<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Server;
use Illuminate\Support\Str;

class GenerateAgentToken
{
    public function handle(Server $server): string
    {
        $token = Str::random(48);

        $server->forceFill(['agent_token' => $token])->save();

        return $token;
    }
}
