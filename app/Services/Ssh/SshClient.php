<?php

declare(strict_types=1);

namespace App\Services\Ssh;

use App\Models\Server;
use RuntimeException;
use Spatie\Ssh\Ssh;

class SshClient
{
    public function run(Server $server, string $command): SshResult
    {
        $key = $server->sshKey;

        if ($key === null) {
            throw new RuntimeException("Server {$server->name} has no SSH key configured.");
        }

        $keyPath = tempnam(sys_get_temp_dir(), 'sm_key_');

        if ($keyPath === false) {
            throw new RuntimeException('Could not create temporary key file.');
        }

        try {
            file_put_contents($keyPath, $key->private_key.PHP_EOL);
            chmod($keyPath, 0600);

            $process = Ssh::create($server->ssh_user, $server->hostname)
                ->usePort($server->port)
                ->usePrivateKey($keyPath)
                ->disableStrictHostKeyChecking()
                ->setTimeout(30)
                ->execute($command);

            return new SshResult(
                successful: $process->isSuccessful(),
                output: trim($process->getOutput()),
                errorOutput: trim($process->getErrorOutput()),
            );
        } finally {
            unlink($keyPath);
        }
    }
}
