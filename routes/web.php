<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Painel\DashboardController;
use App\Http\Controllers\Painel\UsuarioController;
use App\Http\Controllers\Painel\ClienteController;
use App\Http\Controllers\Painel\ConfiguracoesController;
use App\Http\Controllers\Painel\RelatoriosController;
use App\Http\Controllers\Painel\ChecklistController;
use App\Http\Controllers\App\ChecklistAppController;
use App\Http\Controllers\Painel\Operador\ChecklistController as OperadorChecklistController;
use App\Http\Controllers\Painel\Operador\SolicitacaoController as OperadorSolicitacaoController;
use App\Http\Controllers\Painel\Operador\AtrasoController as OperadorAtrasoController;
use App\Http\Controllers\Painel\Cliente\PainelController as ClientePainelController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/docs', function () {
    return redirect('/docs/index.html');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard')
    ->middleware('auth');

Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->name('login.post')
    ->middleware('throttle:login');

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

Route::prefix('app')->name('app.')->group(function () {
    Route::get('/checklist', [ChecklistAppController::class, 'start'])->name('checklist.start');
    Route::get('/checklist/{checklist}/item/{codigo}', [ChecklistAppController::class, 'step'])->name('checklist.step');
    Route::get('/checklist/{checklist}/fim', [ChecklistAppController::class, 'finish'])->name('checklist.finish');

    Route::post('/checklist', [ChecklistAppController::class, 'create'])
        ->name('checklist.create')
        ->middleware(['mobility.key', 'throttle:app-write']);

    Route::post('/checklist/{checklist}/item/{codigo}', [ChecklistAppController::class, 'saveStep'])
        ->name('checklist.step.save')
        ->middleware(['mobility.key', 'throttle:app-write']);
});

Route::get('/acesso-restrito', function () {
    return view('painel.erro-acesso');
})->name('acesso.restrito');

Route::prefix('painel')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])
        ->name('painel.dashboard');

    Route::get('usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::get('usuarios/create', [UsuarioController::class, 'create'])->name('usuarios.create');
    Route::post('usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::get('usuarios/{id}/edit', [UsuarioController::class, 'edit'])->name('usuarios.edit');
    Route::put('usuarios/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::delete('usuarios/{id}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
    Route::delete('usuarios/delete/multiple', [UsuarioController::class, 'destroyMultiple'])->name('usuarios.destroy.multiple');

    Route::resource('clientes', ClienteController::class)->names('painel.clientes');
    Route::patch('clientes/{cliente}/toggle', [ClienteController::class, 'toggle'])->name('painel.clientes.toggle');

    Route::get('relatorios', [RelatoriosController::class, 'index'])->name('painel.relatorios.index');
    Route::get('relatorios/batidas', [RelatoriosController::class, 'batidasIndex'])->name('painel.relatorios.batidas.index');
    Route::get('relatorios/batidas/excel', [RelatoriosController::class, 'batidasExcel'])->name('painel.relatorios.batidas.excel');
    Route::get('relatorios/batidas/pdf', [RelatoriosController::class, 'batidasPdf'])->name('painel.relatorios.batidas.pdf');
    Route::get('relatorios/diario', [RelatoriosController::class, 'diarioIndex'])->name('painel.relatorios.diario.index');
    Route::get('relatorios/diario/excel', [RelatoriosController::class, 'diarioExcel'])->name('painel.relatorios.diario.excel');
    Route::get('relatorios/diario/pdf', [RelatoriosController::class, 'diarioPdf'])->name('painel.relatorios.diario.pdf');

    Route::prefix('checklists')->name('checklists.')->group(function () {
        Route::get('/', [ChecklistController::class, 'index'])->name('index');
        Route::get('/create', [ChecklistController::class, 'create'])->name('create');
        Route::post('/', [ChecklistController::class, 'store'])->name('store');
        Route::get('/{checklist}', [ChecklistController::class, 'show'])->name('show');
    });

    Route::prefix('configuracoes')->name('painel.configuracoes.')->group(function () {
        Route::get('/', [ConfiguracoesController::class, 'index'])->name('index');
        Route::get('/ponto', [ConfiguracoesController::class, 'ponto'])->name('ponto');
        Route::post('/ponto', [ConfiguracoesController::class, 'salvarPonto'])->name('ponto.salvar');
        Route::get('/jornadas', [ConfiguracoesController::class, 'jornadas'])->name('jornadas');
        Route::post('/jornadas', [ConfiguracoesController::class, 'salvarJornadas'])->name('jornadas.salvar');
        Route::post('/jornadas/seed-rafisa', [ConfiguracoesController::class, 'seedJornadasRafisa'])->name('jornadas.seed.rafisa');
    });
});

Route::prefix('painel/operador')->middleware(['auth', 'role:admin'])->name('painel.operador.')->group(function () {
    Route::get('/checklists', [OperadorChecklistController::class, 'index'])->name('checklists.index');
    Route::get('/checklists/{id}', [OperadorChecklistController::class, 'show'])->name('checklists.show');

    Route::get('/solicitacoes', [OperadorSolicitacaoController::class, 'index'])->name('solicitacoes.index');
    Route::get('/solicitacoes/{id}', [OperadorSolicitacaoController::class, 'show'])->name('solicitacoes.show');
    Route::patch('/solicitacoes/{id}/status', [OperadorSolicitacaoController::class, 'updateStatus'])->name('solicitacoes.status');
    Route::patch('/solicitacoes/{id}/atribuir', [OperadorSolicitacaoController::class, 'atribuir'])->name('solicitacoes.atribuir');

    Route::get('/atrasos', [OperadorAtrasoController::class, 'index'])->name('atrasos.index');
    Route::post('/solicitacoes/{id}/atraso', [OperadorAtrasoController::class, 'storeViagem'])->name('atrasos.viagem.store');
    Route::post('/solicitacoes/{id}/atraso-passageiro', [OperadorAtrasoController::class, 'storePassageiro'])->name('atrasos.passageiro.store');
});

Route::prefix('painel/cliente')->middleware(['auth', 'role:cliente'])->name('painel.cliente.')->group(function () {
    Route::get('/solicitacoes', [ClientePainelController::class, 'solicitacoes'])->name('solicitacoes.index');
    Route::get('/atrasos', [ClientePainelController::class, 'atrasos'])->name('atrasos.index');
});
