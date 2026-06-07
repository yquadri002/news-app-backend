<?php

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthController::class, 'index']);
Route::get('/health/metrics', [HealthController::class, 'metrics']);

Route::get('/', function () {
    if (config('filament.admin_domain')) {
        return redirect('/admin/login');
    }

    return view('welcome');
});
