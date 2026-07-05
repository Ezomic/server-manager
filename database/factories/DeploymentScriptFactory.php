<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DeploymentScript;
use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DeploymentScript> */
class DeploymentScriptFactory extends Factory
{
    public function definition(): array
    {
        return [
            'server_id' => Server::factory(),
            'name' => 'deploy',
            'script' => 'cd /var/www/app && git pull && php artisan migrate --force',
        ];
    }
}
