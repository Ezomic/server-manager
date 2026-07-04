<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Server> */
class ServerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->domainWord(),
            'hostname' => fake()->ipv4(),
            'port' => 22,
            'ssh_user' => 'root',
            'type' => 'web',
            'provider' => fake()->randomElement(['Hetzner', 'DigitalOcean', 'AWS']),
            'tags' => [fake()->word()],
            'notes' => null,
        ];
    }
}
