<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Server;
use App\Services\Ssh\SshClient;
use App\Services\Ssh\SshResult;

class LogTailer
{
    public function __construct(private readonly SshClient $ssh) {}

    public function tail(Server $server, string $path, int $lines = 200): SshResult
    {
        $escapedPath = escapeshellarg($path);

        return $this->ssh->run(
            $server,
            "test -f {$escapedPath} && tail -n {$lines} {$escapedPath} || echo 'File not found: {$path}'",
        );
    }
}
