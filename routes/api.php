<?php

use App\Http\Controllers\Api\V1\IncidentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/incidents', [IncidentController::class, 'index']);
    Route::post('/incidents', [IncidentController::class, 'store']);
    Route::get('/incidents/{incident}', [IncidentController::class, 'show']);
    Route::patch('/incidents/{incident}/status', [IncidentController::class, 'transition']);
});
