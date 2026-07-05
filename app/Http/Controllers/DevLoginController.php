<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DevLoginController extends Controller
{
    public function store(): RedirectResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        $user = User::firstOrCreate(
            ['email' => 'dev@server-manager.test'],
            ['name' => 'Dev', 'password' => bcrypt(str()->random(32)), 'email_verified_at' => now()],
        );

        Auth::login($user);

        return to_route('dashboard');
    }
}
