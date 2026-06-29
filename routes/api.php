<?php

use App\Http\Controllers\Api\AdminAtrasoController;
use App\Http\Controllers\Api\AdminSolicitacaoController;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\ChecklistController as ApiChecklistController;
use App\Http\Controllers\Api\ClienteSolicitacaoController;
use App\Http\Controllers\Api\FuncionarioFeedbackController;
use App\Http\Controllers\Api\FuncionarioTripController;
use App\Http\Controllers\Api\MotoristaViagemController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ChecklistController;
use App\Http\Controllers\Api\V2\CatalogoController;
use App\Http\Controllers\Api\V2\MotoristaPagamentoController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [ApiAuthController::class, 'login'])
    ->middleware('throttle:login');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/auth/logout', [ApiAuthController::class, 'logout']);
    Route::get('/me', [ApiAuthController::class, 'me']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'read'])
        ->middleware('throttle:api-write');
});

Route::middleware(['auth:sanctum', 'throttle:api-write'])->group(function () {
    Route::post('/checklists/iniciar', [ApiChecklistController::class, 'iniciar'])->middleware('role:admin,operador,motorista');
    Route::post('/checklists/{id}/respostas', [ApiChecklistController::class, 'respostas'])->middleware('role:admin,operador,motorista');
    Route::post('/checklists/{id}/finalizar', [ApiChecklistController::class, 'finalizar'])->middleware('role:admin,operador,motorista');
});

Route::middleware(['auth:sanctum', 'role:admin,operador,motorista'])->group(function () {
    Route::get('/checklists/itens', [ApiChecklistController::class, 'itens']);
    Route::get('/checklists/{id}', [ApiChecklistController::class, 'show'])->whereNumber('id');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/cliente/solicitacoes', [ClienteSolicitacaoController::class, 'store'])
        ->middleware(['role:cliente', 'throttle:api-write']);
    Route::get('/cliente/solicitacoes', [ClienteSolicitacaoController::class, 'index'])
        ->middleware('role:cliente');

    Route::get('/admin/solicitacoes', [AdminSolicitacaoController::class, 'index'])
        ->middleware('role:admin,operador');
    Route::patch('/admin/solicitacoes/{id}/status', [AdminSolicitacaoController::class, 'status'])
        ->middleware(['role:admin,operador', 'throttle:api-write']);
    Route::patch('/admin/solicitacoes/{id}/atribuir', [AdminSolicitacaoController::class, 'atribuir'])
        ->middleware(['role:admin,operador', 'throttle:api-write']);
    Route::post('/admin/solicitacoes/{id}/atraso', [AdminAtrasoController::class, 'storeViagem'])
        ->middleware(['role:admin,operador', 'throttle:api-write']);
    Route::post('/admin/solicitacoes/{id}/atraso-passageiro', [AdminAtrasoController::class, 'storePassageiro'])
        ->middleware(['role:admin,operador', 'throttle:api-write']);
});

Route::middleware(['auth:sanctum', 'role:motorista'])->prefix('motorista')->group(function () {
    Route::get('/viagens', [MotoristaViagemController::class, 'index']);
    Route::get('/viagens/{id}', [MotoristaViagemController::class, 'show']);
    Route::post('/viagens/{id}/iniciar', [MotoristaViagemController::class, 'iniciar'])->middleware('throttle:api-write');
    Route::post('/viagens/{id}/finalizar', [MotoristaViagemController::class, 'finalizar'])->middleware('throttle:api-write');
    Route::post('/viagens/{id}/atraso', [MotoristaViagemController::class, 'atraso'])->middleware('throttle:api-write');
    Route::post('/viagens/{id}/ocorrencia', [MotoristaViagemController::class, 'ocorrencia'])->middleware('throttle:api-write');
});

Route::middleware(['auth:sanctum', 'role:funcionario'])
    ->get('/app/funcionario/trip/active', [FuncionarioTripController::class, 'active']);

Route::middleware(['auth:sanctum', 'role:funcionario', 'throttle:api-write'])
    ->post('/app/funcionario/feedback', [FuncionarioFeedbackController::class, 'store']);

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum');

    Route::get('/checklists', [ChecklistController::class, 'index'])
        ->middleware(['auth:sanctum', 'role:admin,operador,motorista']);
    Route::post('/checklists', [ChecklistController::class, 'store'])
        ->middleware(['auth:sanctum', 'role:admin,operador,motorista', 'throttle:api-write']);
    Route::get('/checklists/{checklist}', [ChecklistController::class, 'show'])
        ->middleware(['auth:sanctum', 'role:admin,operador,motorista']);

    Route::post('/checklists/{checklist}/respostas', [ChecklistController::class, 'storeRespostas'])
        ->middleware(['auth:sanctum', 'role:admin,operador,motorista', 'throttle:api-write']);

    Route::post('/checklists/{checklist}/finalizar', [ChecklistController::class, 'finalizar'])
        ->middleware(['auth:sanctum', 'role:admin,operador,motorista', 'throttle:api-write']);
});

Route::prefix('v2')->group(function () {
    Route::post('/auth/login', [ApiAuthController::class, 'login'])->middleware('throttle:login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [ApiAuthController::class, 'me']);
        Route::post('/auth/logout', [ApiAuthController::class, 'logout']);
        Route::post('/auth/refresh', [ApiAuthController::class, 'refresh']);

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::patch('/notifications/{id}/read', [NotificationController::class, 'read'])
            ->middleware(['throttle:api-write', 'api.idempotency']);

        Route::middleware('role:cliente')->group(function () {
            Route::get('/cliente/solicitacoes', [ClienteSolicitacaoController::class, 'index']);
            Route::post('/cliente/solicitacoes', [ClienteSolicitacaoController::class, 'store'])
                ->middleware(['throttle:api-write', 'api.idempotency']);
        });

        Route::middleware('role:admin,operador')->prefix('admin')->group(function () {
            Route::get('/solicitacoes', [AdminSolicitacaoController::class, 'index']);
            Route::patch('/solicitacoes/{id}/status', [AdminSolicitacaoController::class, 'status'])->middleware(['throttle:api-write', 'api.idempotency']);
            Route::patch('/solicitacoes/{id}/atribuir', [AdminSolicitacaoController::class, 'atribuir'])->middleware(['throttle:api-write', 'api.idempotency']);
            Route::post('/solicitacoes/{id}/atraso', [AdminAtrasoController::class, 'storeViagem'])->middleware(['throttle:api-write', 'api.idempotency']);
            Route::post('/solicitacoes/{id}/atraso-passageiro', [AdminAtrasoController::class, 'storePassageiro'])->middleware(['throttle:api-write', 'api.idempotency']);
            Route::get('/catalogos/clientes', [CatalogoController::class, 'clientes']);
            Route::get('/catalogos/motoristas', [CatalogoController::class, 'motoristas']);
            Route::get('/catalogos/veiculos', [CatalogoController::class, 'veiculos']);
            Route::get('/catalogos/passageiros', [CatalogoController::class, 'passageiros']);
        });

        Route::middleware('role:cliente')->get('/cliente/catalogos/passageiros', [CatalogoController::class, 'passageiros']);

        Route::middleware('role:admin,operador,motorista')->prefix('checklists')->group(function () {
            Route::get('/itens', [ApiChecklistController::class, 'itens']);
            Route::get('/{id}', [ApiChecklistController::class, 'show'])->whereNumber('id');
            Route::post('/iniciar', [ApiChecklistController::class, 'iniciar'])->middleware(['throttle:api-write', 'api.idempotency']);
            Route::post('/{id}/respostas', [ApiChecklistController::class, 'respostas'])->middleware(['throttle:api-write', 'api.idempotency']);
            Route::post('/{id}/finalizar', [ApiChecklistController::class, 'finalizar'])->middleware(['throttle:api-write', 'api.idempotency']);
        });

        Route::middleware('role:motorista')->prefix('motorista')->group(function () {
            Route::get('/viagens', [MotoristaViagemController::class, 'index']);
            Route::get('/viagens/{id}', [MotoristaViagemController::class, 'show']);
            Route::post('/viagens/{id}/iniciar', [MotoristaViagemController::class, 'iniciar'])->middleware(['throttle:api-write', 'api.idempotency']);
            Route::post('/viagens/{id}/finalizar', [MotoristaViagemController::class, 'finalizar'])->middleware(['throttle:api-write', 'api.idempotency']);
            Route::post('/viagens/{id}/atraso', [MotoristaViagemController::class, 'atraso'])->middleware(['throttle:api-write', 'api.idempotency']);
            Route::post('/viagens/{id}/ocorrencia', [MotoristaViagemController::class, 'ocorrencia'])->middleware(['throttle:api-write', 'api.idempotency']);
            Route::get('/pagamentos/extrato', [MotoristaPagamentoController::class, 'extrato']);
            Route::get('/pagamentos/extrato.pdf', [MotoristaPagamentoController::class, 'pdf']);
        });

        Route::middleware('role:funcionario')->post('/funcionario/feedback', [FuncionarioFeedbackController::class, 'store'])
            ->middleware(['throttle:api-write', 'api.idempotency']);
    });
});
