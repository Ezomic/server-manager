<?php

declare(strict_types=1);

use App\Models\Server;
use App\Models\SshKey;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('lists servers', function () {
    Server::factory()->count(3)->create();

    $this->get(route('servers.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('servers/Index')
            ->has('servers', 3));
});

it('creates a server', function () {
    $key = SshKey::factory()->create();

    $this->post(route('servers.store'), [
        'name' => 'web-1',
        'hostname' => '10.0.0.1',
        'port' => 22,
        'ssh_user' => 'deploy',
        'ssh_key_id' => $key->id,
        'type' => 'web',
        'provider' => 'Hetzner',
        'tags' => ['production'],
        'notes' => null,
    ])->assertRedirect(route('servers.index'));

    expect(Server::sole())
        ->name->toBe('web-1')
        ->hostname->toBe('10.0.0.1')
        ->ssh_key_id->toBe($key->id)
        ->tags->toBe(['production']);
});

it('rejects an invalid server type', function () {
    $this->post(route('servers.store'), [
        'name' => 'web-1',
        'hostname' => '10.0.0.1',
        'port' => 22,
        'ssh_user' => 'root',
        'type' => 'mainframe',
    ])->assertSessionHasErrors('type');
});

it('updates a server', function () {
    $server = Server::factory()->create();

    $this->put(route('servers.update', $server), [
        'name' => 'renamed',
        'hostname' => $server->hostname,
        'port' => 2222,
        'ssh_user' => $server->ssh_user,
        'type' => 'game',
    ])->assertRedirect(route('servers.index'));

    expect($server->refresh())
        ->name->toBe('renamed')
        ->port->toBe(2222)
        ->type->toBe('game');
});

it('deletes a server', function () {
    $server = Server::factory()->create();

    $this->delete(route('servers.destroy', $server))
        ->assertRedirect(route('servers.index'));

    expect(Server::count())->toBe(0);
});

it('requires authentication', function () {
    auth()->logout();

    $this->get(route('servers.index'))->assertRedirect(route('login'));
});
