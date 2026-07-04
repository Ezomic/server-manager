<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreServerRequest;
use App\Models\Server;
use App\Models\SshKey;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ServerController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('servers/Index', [
            'servers' => Server::with('sshKey:id,name')->latest()->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('servers/Create', [
            'sshKeys' => SshKey::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreServerRequest $request): RedirectResponse
    {
        Server::create($request->validated());

        return to_route('servers.index');
    }

    public function show(Server $server): Response
    {
        return Inertia::render('servers/Show', [
            'server' => $server->load('sshKey:id,name'),
            'latestMetric' => $server->metrics()->latest('recorded_at')->first(),
            'metrics' => $server->metrics()
                ->where('recorded_at', '>=', now()->subHours(24))
                ->oldest('recorded_at')
                ->get(),
            'hasAgentToken' => $server->agent_token !== null,
        ]);
    }

    public function edit(Server $server): Response
    {
        return Inertia::render('servers/Edit', [
            'server' => $server,
            'sshKeys' => SshKey::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function update(StoreServerRequest $request, Server $server): RedirectResponse
    {
        $server->update($request->validated());

        return to_route('servers.index');
    }

    public function destroy(Server $server): RedirectResponse
    {
        $server->delete();

        return to_route('servers.index');
    }
}
