<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Server;
use App\Models\User;
use App\Notifications\ServerWentOffline;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Notification;

class MarkOfflineServers extends Command
{
    protected $signature = 'servers:mark-offline';

    protected $description = 'Mark servers offline when their agent has not reported for 2 minutes';

    public function handle(): int
    {
        $servers = Server::where('status', 'online')
            ->where('last_seen_at', '<', Date::now()->subMinutes(2))
            ->get();

        foreach ($servers as $server) {
            $server->forceFill(['status' => 'offline'])->save();
            Notification::send(User::all(), new ServerWentOffline($server));
        }

        $this->info("Marked {$servers->count()} server(s) offline.");

        return self::SUCCESS;
    }
}
