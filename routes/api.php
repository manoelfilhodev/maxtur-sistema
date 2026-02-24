<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ChecklistController;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum');

    Route::get('/checklists', [ChecklistController::class, 'index']);
    Route::post('/checklists', [ChecklistController::class, 'store'])
        ->middleware(['auth:sanctum', 'throttle:api-write']);
    Route::get('/checklists/{checklist}', [ChecklistController::class, 'show']);

    Route::post('/checklists/{checklist}/respostas', [ChecklistController::class, 'storeRespostas'])
        ->middleware(['auth:sanctum', 'throttle:api-write']);

    Route::post('/checklists/{checklist}/finalizar', [ChecklistController::class, 'finalizar'])
        ->middleware(['auth:sanctum', 'throttle:api-write']);
});
