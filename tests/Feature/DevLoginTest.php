<?php

declare(strict_types=1);

use App\Models\User;

it('logs in as the dev user in local/testing environments', function () {
    $this->post(route('dev-login'))
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();
    expect(User::where('email', 'dev@server-manager.test')->exists())->toBeTrue();
});

it('reuses the existing dev user on subsequent logins', function () {
    $this->post(route('dev-login'));
    $first = User::where('email', 'dev@server-manager.test')->sole();

    auth()->logout();
    $this->post(route('dev-login'));

    expect(User::where('email', 'dev@server-manager.test')->count())->toBe(1)
        ->and(auth()->id())->toBe($first->id);
});

it('is unavailable outside local and testing environments', function () {
    app()->instance('env', 'production');

    $this->post('/dev-login')->assertNotFound();
});
