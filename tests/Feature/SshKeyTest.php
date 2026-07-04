<?php

declare(strict_types=1);

use App\Models\Server;
use App\Models\SshKey;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('lists keys with server counts', function () {
    $key = SshKey::factory()->create();
    Server::factory()->count(2)->create(['ssh_key_id' => $key->id]);

    $this->get(route('ssh-keys.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('ssh-keys/Index')
            ->where('sshKeys.0.servers_count', 2));
});

it('stores a key encrypted and never exposes the private key', function () {
    $this->post(route('ssh-keys.store'), [
        'name' => 'deploy key',
        'public_key' => 'ssh-ed25519 AAAAtest',
        'private_key' => 'secret-material',
    ])->assertRedirect(route('ssh-keys.index'));

    $key = SshKey::sole();

    expect($key->private_key)->toBe('secret-material')
        ->and($key->getRawOriginal('private_key'))->not->toBe('secret-material')
        ->and(Crypt::decryptString($key->getRawOriginal('private_key')))->toBe('secret-material')
        ->and($key->toArray())->not->toHaveKey('private_key');
});

it('deletes a key and detaches servers', function () {
    $key = SshKey::factory()->create();
    $server = Server::factory()->create(['ssh_key_id' => $key->id]);

    $this->delete(route('ssh-keys.destroy', $key))
        ->assertRedirect(route('ssh-keys.index'));

    expect(SshKey::count())->toBe(0)
        ->and($server->refresh()->ssh_key_id)->toBeNull();
});
