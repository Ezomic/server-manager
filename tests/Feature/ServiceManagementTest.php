<?php

declare(strict_types=1);

use App\Models\Server;
use App\Models\User;
use App\Services\Ssh\SshClient;
use App\Services\Ssh\SshResult;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
    $this->server = Server::factory()->create();
});

function fakeSsh(array $responses): void
{
    $fake = new class($responses) extends SshClient
    {
        public array $commands = [];

        public function __construct(private array $responses) {}

        public function run(Server $server, string $command): SshResult
        {
            $this->commands[] = $command;

            foreach ($this->responses as $needle => $response) {
                if (str_contains($command, $needle)) {
                    return $response;
                }
            }

            return new SshResult(successful: true, output: '');
        }
    };

    app()->instance(SshClient::class, $fake);
}

it('lists systemd services and docker containers', function () {
    fakeSsh([
        'systemctl' => new SshResult(true, implode("\n", [
            'nginx.service loaded active running A high performance web server',
            'php8.4-fpm.service loaded active running The PHP FastCGI Process Manager',
            'redis.service loaded inactive dead Redis in-memory store',
        ])),
        'docker' => new SshResult(true, "app\trunning\tnginx:latest\nworker\texited\tredis:7"),
    ]);

    $this->getJson(route('servers.services.index', $this->server))
        ->assertOk()
        ->assertJsonCount(5, 'services')
        ->assertJsonPath('services.0.name', 'nginx')
        ->assertJsonPath('services.0.status', 'running')
        ->assertJsonPath('services.2.status', 'dead')
        ->assertJsonPath('services.3.type', 'docker')
        ->assertJsonPath('services.4.status', 'exited');
});

it('restarts a systemd service', function () {
    fakeSsh(['systemctl restart' => new SshResult(true, '')]);

    $this->postJson(route('servers.services.store', $this->server), [
        'type' => 'systemd',
        'name' => 'nginx',
        'action' => 'restart',
    ])->assertOk()->assertJsonPath('successful', true);

    expect(app(SshClient::class)->commands)->toContain('sudo systemctl restart nginx');
});

it('stops a docker container', function () {
    fakeSsh(['docker stop' => new SshResult(true, 'app')]);

    $this->postJson(route('servers.services.store', $this->server), [
        'type' => 'docker',
        'name' => 'app',
        'action' => 'stop',
    ])->assertOk();

    expect(app(SshClient::class)->commands)->toContain('docker stop app');
});

it('rejects service names with shell metacharacters', function () {
    fakeSsh([]);

    $this->postJson(route('servers.services.store', $this->server), [
        'type' => 'systemd',
        'name' => 'nginx; rm -rf /',
        'action' => 'restart',
    ])->assertUnprocessable()->assertJsonValidationErrors(['name']);

    expect(app(SshClient::class)->commands)->toBeEmpty();
});

it('rejects unknown actions', function () {
    fakeSsh([]);

    $this->postJson(route('servers.services.store', $this->server), [
        'type' => 'systemd',
        'name' => 'nginx',
        'action' => 'mask',
    ])->assertUnprocessable()->assertJsonValidationErrors(['action']);
});

it('surfaces command failures', function () {
    fakeSsh([
        'systemctl restart' => new SshResult(false, '', 'Failed to restart nginx.service: access denied'),
    ]);

    $this->postJson(route('servers.services.store', $this->server), [
        'type' => 'systemd',
        'name' => 'nginx',
        'action' => 'restart',
    ])->assertUnprocessable()
        ->assertJsonPath('successful', false)
        ->assertJsonPath('output', 'Failed to restart nginx.service: access denied');
});

it('returns a friendly error when the server has no ssh key', function () {
    $this->getJson(route('servers.services.index', $this->server))
        ->assertUnprocessable()
        ->assertJsonPath('message', "Server {$this->server->name} has no SSH key configured.");
});
