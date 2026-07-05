<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Server;
use App\Services\Ssh\SshClient;
use App\Services\Ssh\SshResult;
use Illuminate\Support\Str;

class ServiceManager
{
    private const NAME_PATTERN = '/^[A-Za-z0-9@_.:-]+$/';

    public function __construct(private readonly SshClient $ssh) {}

    /** @return array<int, array{name: string, type: string, status: string, description: string}> */
    public function list(Server $server): array
    {
        $services = $this->listSystemd($server);

        return [...$services, ...$this->listDocker($server)];
    }

    public function perform(Server $server, string $type, string $name, string $action): SshResult
    {
        if (preg_match(self::NAME_PATTERN, $name) !== 1) {
            throw new \InvalidArgumentException('Invalid service name.');
        }

        if (! in_array($action, ['start', 'stop', 'restart'], true)) {
            throw new \InvalidArgumentException('Invalid action.');
        }

        $command = $type === 'docker'
            ? "docker {$action} {$name}"
            : "sudo systemctl {$action} {$name}";

        return $this->ssh->run($server, $command);
    }

    /** @return array<int, array{name: string, type: string, status: string, description: string}> */
    private function listSystemd(Server $server): array
    {
        $result = $this->ssh->run(
            $server,
            'systemctl list-units --type=service --no-pager --no-legend --plain --all',
        );

        if (! $result->successful) {
            return [];
        }

        return collect(explode("\n", $result->output))
            ->filter(fn (string $line) => trim($line) !== '')
            ->map(function (string $line) {
                $parts = preg_split('/\s+/', trim($line), 5) ?: [];

                return [
                    'name' => Str::beforeLast($parts[0] ?? '', '.service'),
                    'type' => 'systemd',
                    'status' => $parts[3] ?? 'unknown',
                    'description' => $parts[4] ?? '',
                ];
            })
            ->filter(fn (array $service) => $service['name'] !== '')
            ->values()
            ->all();
    }

    /** @return array<int, array{name: string, type: string, status: string, description: string}> */
    private function listDocker(Server $server): array
    {
        $result = $this->ssh->run(
            $server,
            'command -v docker >/dev/null && docker ps -a --format "{{.Names}}\t{{.State}}\t{{.Image}}" || true',
        );

        if (! $result->successful || $result->output === '') {
            return [];
        }

        return collect(explode("\n", $result->output))
            ->filter(fn (string $line) => str_contains($line, "\t"))
            ->map(function (string $line) {
                [$name, $state, $image] = array_pad(explode("\t", $line, 3), 3, '');

                return [
                    'name' => $name,
                    'type' => 'docker',
                    'status' => $state === 'running' ? 'running' : $state,
                    'description' => $image,
                ];
            })
            ->values()
            ->all();
    }
}
