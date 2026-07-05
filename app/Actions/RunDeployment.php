<?php

declare(strict_types=1);

namespace App\Actions;

use App\Jobs\RunDeploymentJob;
use App\Models\Deployment;
use App\Models\DeploymentScript;
use App\Models\User;
use Illuminate\Support\Facades\Date;

class RunDeployment
{
    public function handle(DeploymentScript $script, User $user): Deployment
    {
        $deployment = Deployment::create([
            'server_id' => $script->server_id,
            'deployment_script_id' => $script->id,
            'user_id' => $user->id,
            'status' => 'running',
            'started_at' => Date::now(),
        ]);

        RunDeploymentJob::dispatch($deployment);

        return $deployment;
    }
}
