<?php

use App\Http\Controllers\AgentInstallController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\ServerServiceController;
use App\Http\Controllers\SshKeyController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::resource('servers', ServerController::class);
    Route::post('servers/{server}/agent-script', [AgentInstallController::class, 'store'])
        ->name('servers.agent-script');
    Route::get('servers/{server}/services', [ServerServiceController::class, 'index'])
        ->name('servers.services.index');
    Route::post('servers/{server}/services', [ServerServiceController::class, 'store'])
        ->name('servers.services.store');
    Route::resource('ssh-keys', SshKeyController::class)->only(['index', 'store', 'destroy']);
});

require __DIR__.'/settings.php';
