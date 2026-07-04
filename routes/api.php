<?php

use App\Http\Controllers\Api\AgentMetricController;
use Illuminate\Support\Facades\Route;

Route::post('agent/metrics', [AgentMetricController::class, 'store'])
    ->middleware('throttle:120,1')
    ->name('agent.metrics.store');
