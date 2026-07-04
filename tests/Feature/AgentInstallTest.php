<?php

declare(strict_types=1);

use App\Models\Metric;
use App\Models\Server;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('generates an install script with a fresh token', function () {
    $server = Server::factory()->create();

    $response = $this->post(route('servers.agent-script', $server))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/x-shellscript; charset=UTF-8');

    $server->refresh();

    expect($server->agent_token)->not->toBeNull()
        ->and($response->getContent())
        ->toContain($server->agent_token)
        ->toContain('servermanager-agent.timer');
});

it('revokes the previous token when regenerating', function () {
    $server = Server::factory()->create();

    $this->post(route('servers.agent-script', $server));
    $first = $server->refresh()->agent_token;

    $this->post(route('servers.agent-script', $server));

    expect($server->refresh()->agent_token)->not->toBe($first);
});

it('shows server detail with metrics', function () {
    $server = Server::factory()->create();
    Metric::factory()->count(3)->create(['server_id' => $server->id]);

    $this->get(route('servers.show', $server))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('servers/Show')
            ->has('metrics', 3)
            ->where('latestMetric.server_id', $server->id));
});

it('never exposes the agent token on the show page', function () {
    $server = Server::factory()->create();
    $this->post(route('servers.agent-script', $server));

    $this->get(route('servers.show', $server->refresh()))
        ->assertOk()
        ->assertDontSee($server->agent_token);
});
