<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Deployment;
use App\Models\DeploymentScript;
use App\Models\Server;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Deployment> */
class DeploymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'server_id' => Server::factory(),
            'deployment_script_id' => DeploymentScript::factory(),
            'user_id' => User::factory(),
            'status' => 'succeeded',
            'output' => 'Already up to date.',
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
        ];
    }
}
