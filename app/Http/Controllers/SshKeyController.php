<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreSshKeyRequest;
use App\Models\SshKey;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SshKeyController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('ssh-keys/Index', [
            'sshKeys' => SshKey::withCount('servers')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreSshKeyRequest $request): RedirectResponse
    {
        SshKey::create($request->validated());

        return to_route('ssh-keys.index');
    }

    public function destroy(SshKey $sshKey): RedirectResponse
    {
        $sshKey->delete();

        return to_route('ssh-keys.index');
    }
}
