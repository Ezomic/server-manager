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

it('tails a log file', function () {
    app()->instance(SshClient::class, new class extends SshClient
    {
        public function run(Server $server, string $command): SshResult
        {
            return new SshResult(true, "line one\nline two");
        }
    });

    $this->getJson(route('servers.logs.index', $this->server).'?path=/var/log/nginx/error.log')
        ->assertOk()
        ->assertJsonPath('successful', true)
        ->assertJsonPath('output', "line one\nline two");
});

it('rejects paths that are not absolute', function () {
    $this->getJson(route('servers.logs.index', $this->server).'?path=relative/path.log')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['path']);
});

it('rejects paths with shell metacharacters', function () {
    $this->getJson(route('servers.logs.index', $this->server).'?'.http_build_query([
        'path' => '/var/log/app.log; rm -rf /',
    ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['path']);
});

it('caps the requested line count', function () {
    $this->getJson(route('servers.logs.index', $this->server).'?path=/var/log/app.log&lines=5000')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['lines']);
});

it('surfaces a missing file message', function () {
    app()->instance(SshClient::class, new class extends SshClient
    {
        public function run(Server $server, string $command): SshResult
        {
            return new SshResult(true, 'File not found: /var/log/missing.log');
        }
    });

    $this->getJson(route('servers.logs.index', $this->server).'?path=/var/log/missing.log')
        ->assertOk()
        ->assertJsonPath('output', 'File not found: /var/log/missing.log');
});
