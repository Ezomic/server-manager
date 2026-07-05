<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Metric;
use App\Models\Server;
use App\Models\User;
use App\Notifications\ServerRecovered;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Notification;

class RecordMetric
{
    /** @param array<string, int|float> $data */
    public function handle(Server $server, array $data): Metric
    {
        $metric = $server->metrics()->create([
            ...$data,
            'recorded_at' => Date::now(),
        ]);

        $wasOffline = $server->status === 'offline';

        $server->forceFill([
            'status' => 'online',
            'last_seen_at' => Date::now(),
        ])->save();

        if ($wasOffline) {
            Notification::send(User::all(), new ServerRecovered($server));
        }

        return $metric;
    }
}
