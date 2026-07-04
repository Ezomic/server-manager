<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Server;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

class MarkOfflineServers extends Command
{
    protected $signature = 'servers:mark-offline';

    protected $description = 'Mark servers offline when their agent has not reported for 2 minutes';

    public function handle(): int
    {
        $count = Server::where('status', 'online')
            ->where('last_seen_at', '<', Date::now()->subMinutes(2))
            ->update(['status' => 'offline']);

        $this->info("Marked {$count} server(s) offline.");

        return self::SUCCESS;
    }
}
