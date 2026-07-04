<?php

declare(strict_types=1);

use App\Actions\GenerateAgentToken;
use App\Models\Metric;
use App\Models\Server;

function metricPayload(): array
{
    return [
        'cpu_percent' => 42.5,
        'memory_used_mb' => 2048,
        'memory_total_mb' => 4096,
        'disk_used_mb' => 30000,
        'disk_total_mb' => 81920,
        'load_avg' => 1.25,
    ];
}

it('rejects requests without a token', function () {
    $this->postJson(route('agent.metrics.store'), metricPayload())
        ->assertUnauthorized();
});

it('rejects requests with an unknown token', function () {
    Server::factory()->create();

    $this->postJson(route('agent.metrics.store'), metricPayload(), [
        'Authorization' => 'Bearer wrong-token',
    ])->assertUnauthorized();
});

it('records a metric and marks the server online', function () {
    $server = Server::factory()->create(['status' => 'offline']);
    $token = app(GenerateAgentToken::class)->handle($server);

    $this->postJson(route('agent.metrics.store'), metricPayload(), [
        'Authorization' => "Bearer {$token}",
    ])->assertCreated();

    expect(Metric::sole())
        ->server_id->toBe($server->id)
        ->cpu_percent->toBe(42.5)
        ->and($server->refresh())
        ->status->toBe('online')
        ->last_seen_at->not->toBeNull();
});

it('validates the payload', function () {
    $server = Server::factory()->create();
    $token = app(GenerateAgentToken::class)->handle($server);

    $this->postJson(route('agent.metrics.store'), [
        'cpu_percent' => 250,
    ], [
        'Authorization' => "Bearer {$token}",
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['cpu_percent', 'memory_used_mb', 'disk_total_mb', 'load_avg']);
});

it('marks stale servers offline', function () {
    $stale = Server::factory()->create([
        'status' => 'online',
        'last_seen_at' => now()->subMinutes(5),
    ]);
    $fresh = Server::factory()->create([
        'status' => 'online',
        'last_seen_at' => now()->subSeconds(30),
    ]);

    $this->artisan('servers:mark-offline')->assertSuccessful();

    expect($stale->refresh()->status)->toBe('offline')
        ->and($fresh->refresh()->status)->toBe('online');
});
