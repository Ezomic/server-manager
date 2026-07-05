<?php

declare(strict_types=1);

use App\Actions\GenerateAgentToken;
use App\Models\Server;
use App\Models\User;
use App\Notifications\ServerRecovered;
use App\Notifications\ServerWentOffline;
use Illuminate\Support\Facades\Notification;

it('notifies all users when a server goes offline', function () {
    Notification::fake();

    User::factory()->count(2)->create();
    $server = Server::factory()->create([
        'status' => 'online',
        'last_seen_at' => now()->subMinutes(5),
    ]);

    $this->artisan('servers:mark-offline');

    Notification::assertSentTo(User::all(), ServerWentOffline::class);
});

it('does not re-notify servers that are already offline', function () {
    Notification::fake();

    User::factory()->create();
    Server::factory()->create([
        'status' => 'offline',
        'last_seen_at' => now()->subMinutes(10),
    ]);

    $this->artisan('servers:mark-offline');

    Notification::assertNothingSent();
});

it('notifies all users when an offline server recovers', function () {
    Notification::fake();

    $user = User::factory()->create();
    $server = Server::factory()->create(['status' => 'offline']);
    $token = app(GenerateAgentToken::class)->handle($server);

    $this->postJson(route('agent.metrics.store'), [
        'cpu_percent' => 10,
        'memory_used_mb' => 100,
        'memory_total_mb' => 1000,
        'disk_used_mb' => 100,
        'disk_total_mb' => 1000,
        'load_avg' => 0.1,
    ], ['Authorization' => "Bearer {$token}"]);

    Notification::assertSentTo($user, ServerRecovered::class);
});

it('does not notify recovery for a server that was already online', function () {
    Notification::fake();

    User::factory()->create();
    $server = Server::factory()->create(['status' => 'online']);
    $token = app(GenerateAgentToken::class)->handle($server);

    $this->postJson(route('agent.metrics.store'), [
        'cpu_percent' => 10,
        'memory_used_mb' => 100,
        'memory_total_mb' => 1000,
        'disk_used_mb' => 100,
        'disk_total_mb' => 1000,
        'load_avg' => 0.1,
    ], ['Authorization' => "Bearer {$token}"]);

    Notification::assertNothingSent();
});
