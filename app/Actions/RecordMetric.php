<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Metric;
use App\Models\Server;
use Illuminate\Support\Facades\Date;

class RecordMetric
{
    /** @param array<string, int|float> $data */
    public function handle(Server $server, array $data): Metric
    {
        $metric = $server->metrics()->create([
            ...$data,
            'recorded_at' => Date::now(),
        ]);

        $server->forceFill([
            'status' => 'online',
            'last_seen_at' => Date::now(),
        ])->save();

        return $metric;
    }
}
