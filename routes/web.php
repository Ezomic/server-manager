<?php

use App\Http\Controllers\AgentInstallController;
use App\Http\Controllers\DeploymentController;
use App\Http\Controllers\DeploymentScriptController;
use App\Http\Controllers\DevLoginController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\ServerLogController;
use App\Http\Controllers\ServerServiceController;
use App\Http\Controllers\SshKeyController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

if (app()->environment(['local', 'testing'])) {
    Route::post('dev-login', [DevLoginController::class, 'store'])->name('dev-login');
}

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

    Route::post('servers/{server}/deployment-scripts', [DeploymentScriptController::class, 'store'])
        ->name('servers.deployment-scripts.store');
    Route::delete('servers/{server}/deployment-scripts/{deploymentScript}', [DeploymentScriptController::class, 'destroy'])
        ->name('servers.deployment-scripts.destroy');
    Route::get('servers/{server}/deployments', [DeploymentController::class, 'index'])
        ->name('servers.deployments.index');
    Route::post('servers/{server}/deployment-scripts/{deploymentScript}/run', [DeploymentController::class, 'store'])
        ->name('servers.deployments.store');
    Route::get('servers/{server}/deployments/{deployment}', [DeploymentController::class, 'show'])
        ->name('servers.deployments.show');

    Route::get('servers/{server}/logs', [ServerLogController::class, 'index'])
        ->name('servers.logs.index');
});

require __DIR__.'/settings.php';
