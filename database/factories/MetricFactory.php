<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Metric;
use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Metric> */
class MetricFactory extends Factory
{
    public function definition(): array
    {
        return [
            'server_id' => Server::factory(),
            'cpu_percent' => fake()->randomFloat(2, 0, 100),
            'memory_used_mb' => fake()->numberBetween(500, 3500),
            'memory_total_mb' => 4096,
            'disk_used_mb' => fake()->numberBetween(5000, 70000),
            'disk_total_mb' => 81920,
            'load_avg' => fake()->randomFloat(2, 0, 4),
            'recorded_at' => now(),
        ];
    }
}
