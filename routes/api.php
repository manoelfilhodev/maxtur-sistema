<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\ChecklistController as ApiChecklistController;
use App\Http\Controllers\Api\ClienteSolicitacaoController;
use App\Http\Controllers\Api\AdminSolicitacaoController;
use App\Http\Controllers\Api\AdminAtrasoController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\FuncionarioTripController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ChecklistController;

Route::post('/auth/login', [ApiAuthController::class, 'login'])
    ->middleware('throttle:login');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/auth/logout', [ApiAuthController::class, 'logout']);
    Route::get('/me', [ApiAuthController::class, 'me']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'read'])
        ->middleware('throttle:api-write');
});

Route::middleware(['auth:sanctum', 'throttle:api-write'])->group(function () {
    Route::post('/checklists/iniciar', [ApiChecklistController::class, 'iniciar']);
    Route::post('/checklists/{id}/respostas', [ApiChecklistController::class, 'respostas']);
    Route::post('/checklists/{id}/finalizar', [ApiChecklistController::class, 'finalizar']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/cliente/solicitacoes', [ClienteSolicitacaoController::class, 'store'])
        ->middleware(['role:cliente', 'throttle:api-write']);
    Route::get('/cliente/solicitacoes', [ClienteSolicitacaoController::class, 'index'])
        ->middleware('role:cliente');

    Route::get('/admin/solicitacoes', [AdminSolicitacaoController::class, 'index'])
        ->middleware('role:admin');
    Route::patch('/admin/solicitacoes/{id}/status', [AdminSolicitacaoController::class, 'status'])
        ->middleware(['role:admin', 'throttle:api-write']);
    Route::patch('/admin/solicitacoes/{id}/atribuir', [AdminSolicitacaoController::class, 'atribuir'])
        ->middleware(['role:admin', 'throttle:api-write']);
    Route::post('/admin/solicitacoes/{id}/atraso', [AdminAtrasoController::class, 'storeViagem'])
        ->middleware(['role:admin', 'throttle:api-write']);
    Route::post('/admin/solicitacoes/{id}/atraso-passageiro', [AdminAtrasoController::class, 'storePassageiro'])
        ->middleware(['role:admin', 'throttle:api-write']);
});

Route::middleware(['auth:sanctum', 'role:funcionario'])
    ->get('/app/funcionario/trip/active', [FuncionarioTripController::class, 'active']);

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
