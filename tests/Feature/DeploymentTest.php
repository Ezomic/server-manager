<?php

declare(strict_types=1);

use App\Models\Deployment;
use App\Models\DeploymentScript;
use App\Models\Server;
use App\Models\User;
use App\Services\Ssh\SshClient;
use App\Services\Ssh\SshResult;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
    $this->server = Server::factory()->create();
});

it('creates a deployment script', function () {
    $this->post(route('servers.deployment-scripts.store', $this->server), [
        'name' => 'deploy',
        'script' => 'git pull',
    ])->assertRedirect();

    expect(DeploymentScript::sole())
        ->name->toBe('deploy')
        ->server_id->toBe($this->server->id);
});

it('runs a deployment script and captures output', function () {
    app()->instance(SshClient::class, new class extends SshClient
    {
        public function run(Server $server, string $command): SshResult
        {
            return new SshResult(true, 'Already up to date.');
        }
    });

    $script = DeploymentScript::factory()->create(['server_id' => $this->server->id]);

    $this->postJson(route('servers.deployments.store', [$this->server, $script]))
        ->assertCreated();

    $deployment = Deployment::sole();

    expect($deployment->status)->toBe('succeeded')
        ->and($deployment->output)->toBe('Already up to date.')
        ->and($deployment->finished_at)->not->toBeNull();
});

it('records a failed deployment', function () {
    app()->instance(SshClient::class, new class extends SshClient
    {
        public function run(Server $server, string $command): SshResult
        {
            return new SshResult(false, '', 'fatal: could not read from remote repository');
        }
    });

    $script = DeploymentScript::factory()->create(['server_id' => $this->server->id]);

    $this->postJson(route('servers.deployments.store', [$this->server, $script]));

    expect(Deployment::sole())
        ->status->toBe('failed')
        ->output->toContain('fatal: could not read from remote repository');
});

it('lists deployment history for a server', function () {
    Deployment::factory()->count(2)->create(['server_id' => $this->server->id]);
    Deployment::factory()->create();

    $this->getJson(route('servers.deployments.index', $this->server))
        ->assertOk()
        ->assertJsonCount(2, 'deployments');
});

it('rejects running a script that belongs to another server', function () {
    $otherServer = Server::factory()->create();
    $script = DeploymentScript::factory()->create(['server_id' => $otherServer->id]);

    $this->postJson(route('servers.deployments.store', [$this->server, $script]))
        ->assertNotFound();
});
