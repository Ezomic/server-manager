<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Exceptions\MissingSshKeyException;
use App\Models\Deployment;
use App\Services\Ssh\SshClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Date;

class RunDeploymentJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public function __construct(private readonly Deployment $deployment) {}

    public function handle(SshClient $ssh): void
    {
        $script = $this->deployment->deploymentScript;

        try {
            $result = $ssh->run($script->server, $script->script);

            $this->deployment->forceFill([
                'status' => $result->successful ? 'succeeded' : 'failed',
                'output' => $result->successful ? $result->output : $result->output."\n".$result->errorOutput,
                'finished_at' => Date::now(),
            ])->save();
        } catch (MissingSshKeyException $e) {
            $this->deployment->forceFill([
                'status' => 'failed',
                'output' => $e->getMessage(),
                'finished_at' => Date::now(),
            ])->save();
        }
    }
}
