<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ActivationController;
use App\Http\Controllers\Auth\PasswordResetController;
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
use App\Http\Controllers\Painel\Operador\OcorrenciaController as OperadorOcorrenciaController;
use App\Http\Controllers\Master\VeiculoController as MasterVeiculoController;
use App\Http\Controllers\Master\MotoristaController as MasterMotoristaController;
use App\Http\Controllers\Master\ViagemController as MasterViagemController;
use App\Http\Controllers\Tenant\FuncionarioController as TenantFuncionarioController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\Tenant\ViagemController as TenantViagemController;
use App\Http\Controllers\Tenant\RelatorioController as TenantRelatorioController;
use App\Http\Controllers\Web\FuncionarioFeedbackController as WebFuncionarioFeedbackController;
use App\Http\Controllers\Web\NotificationController as WebNotificationController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/docs', function () {
    abort_if(app()->environment('production') && !config('scribe.public_docs_enabled'), 404);

    return redirect('/docs/index.html');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard')
    ->middleware('auth');

Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->name('login');

Route::get('/app/login', [LoginController::class, 'showLoginForm'])
    ->name('app.login');

Route::post('/login', [LoginController::class, 'login'])
    ->name('login.post')
    ->middleware('throttle:login');

Route::post('/app/login', [LoginController::class, 'login'])
    ->name('app.login.post')
    ->middleware('throttle:login');

Route::get('/esqueci-senha', [PasswordResetController::class, 'requestForm'])
    ->name('password.request');

Route::post('/esqueci-senha', [PasswordResetController::class, 'updatePassword'])
    ->name('password.update')
    ->middleware('throttle:login');

Route::get('/ativar-conta/{token}', [ActivationController::class, 'showForm'])->name('activation.show');
Route::post('/ativar-conta/{token}', [ActivationController::class, 'activate'])->name('activation.activate');

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/notificacoes/{notification}/abrir', [WebNotificationController::class, 'open'])
        ->name('web.notifications.open');
});

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

Route::prefix('master')->middleware(['auth', 'master'])->name('master.')->group(function () {
    Route::get('/', fn () => redirect('/painel'))->name('home');
    Route::get('/clientes', fn () => redirect()->route('painel.clientes.index'))->name('clientes.index');
    Route::get('/clientes/create', fn () => redirect()->route('painel.clientes.create'))->name('clientes.create');
    Route::get('/clientes/{cliente}', fn ($cliente) => redirect()->route('painel.clientes.show', $cliente))->name('clientes.show');
    Route::get('/veiculos', fn () => redirect()->route('master.veiculos.index'))->name('legacy.veiculos');
    Route::get('/motoristas', fn () => redirect()->route('master.motoristas.index'))->name('legacy.motoristas');
    Route::get('/viagens', fn () => redirect()->route('master.viagens.index'))->name('legacy.viagens');
});

Route::prefix('app')->middleware(['auth', 'tenant'])->name('tenant.')->group(function () {
    Route::get('/', [TenantDashboardController::class, 'index'])->name('home');

    Route::get('/funcionarios', [TenantFuncionarioController::class, 'index'])->name('funcionarios.index');
    Route::get('/funcionarios/create', [TenantFuncionarioController::class, 'create'])->name('funcionarios.create');
    Route::get('/funcionarios/import', [TenantFuncionarioController::class, 'showImport'])->name('funcionarios.import.form');
    Route::get('/funcionarios/import/template-csv', [TenantFuncionarioController::class, 'downloadTemplateCsv'])->name('funcionarios.import.template.csv');
    Route::get('/funcionarios/import/template-xlsx', [TenantFuncionarioController::class, 'downloadTemplateXlsx'])->name('funcionarios.import.template.xlsx');
    Route::post('/funcionarios', [TenantFuncionarioController::class, 'store'])->name('funcionarios.store');
    Route::post('/funcionarios/store-multiple', [TenantFuncionarioController::class, 'storeMultiple'])->name('funcionarios.store-multiple');
    Route::post('/funcionarios/import', [TenantFuncionarioController::class, 'importCsv'])->name('funcionarios.import');
    Route::get('/funcionarios/import/errors-download', [TenantFuncionarioController::class, 'downloadImportErrors'])->name('funcionarios.import.errors');
    Route::post('/funcionarios/enviar-convites', [TenantFuncionarioController::class, 'sendInviteBulk'])->name('funcionarios.send-invite-bulk');
    Route::delete('/funcionarios/excluir-lote', [TenantFuncionarioController::class, 'destroyBulk'])->name('funcionarios.destroy-bulk');
    Route::post('/funcionarios/{funcionario}/enviar-convite', [TenantFuncionarioController::class, 'sendInvite'])->name('funcionarios.send-invite');
    Route::post('/funcionarios/{funcionario}/regenerar-ativacao', [TenantFuncionarioController::class, 'regenerateActivation'])->name('funcionarios.regenerate-activation');
    Route::get('/funcionarios/{funcionario}', [TenantFuncionarioController::class, 'show'])->name('funcionarios.show');

    Route::get('/viagens', [TenantViagemController::class, 'index'])->name('viagens.index');
    Route::get('/relatorios', [TenantRelatorioController::class, 'index'])->name('relatorios.index');
    Route::get('/feedbacks', [WebFuncionarioFeedbackController::class, 'index'])->name('feedbacks.index');
    Route::get('/feedbacks/{feedback}', [WebFuncionarioFeedbackController::class, 'show'])->name('feedbacks.show');
});

Route::get('/acesso-restrito', function () {
    return view('painel.erro-acesso');
})->name('acesso.restrito');

Route::prefix('painel')->middleware(['auth', 'role:admin,operador'])->group(function () {
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
    Route::post('clientes/{cliente}/reenviar-ativacao', [ClienteController::class, 'regenerateActivation'])->name('painel.clientes.reenviar-ativacao');

    Route::get('veiculos', [MasterVeiculoController::class, 'index'])->name('master.veiculos.index');
    Route::get('veiculos/create', [MasterVeiculoController::class, 'create'])->name('master.veiculos.create');
    Route::post('veiculos', [MasterVeiculoController::class, 'store'])->name('master.veiculos.store');
    Route::get('veiculos/{veiculo}', [MasterVeiculoController::class, 'show'])->name('master.veiculos.show');

    Route::get('motoristas', [MasterMotoristaController::class, 'index'])->name('master.motoristas.index');
    Route::get('motoristas/create', [MasterMotoristaController::class, 'create'])->name('master.motoristas.create');
    Route::post('motoristas', [MasterMotoristaController::class, 'store'])->name('master.motoristas.store');
    Route::get('motoristas/{motorista}/edit', [MasterMotoristaController::class, 'edit'])->name('master.motoristas.edit');
    Route::put('motoristas/{motorista}', [MasterMotoristaController::class, 'update'])->name('master.motoristas.update');
    Route::get('motoristas/{motorista}', [MasterMotoristaController::class, 'show'])->name('master.motoristas.show');

    Route::get('viagens', [MasterViagemController::class, 'index'])->name('master.viagens.index');
    Route::get('viagens/create', [MasterViagemController::class, 'create'])->name('master.viagens.create');
    Route::post('viagens', [MasterViagemController::class, 'store'])->name('master.viagens.store');
    Route::get('viagens/{viagem}', [MasterViagemController::class, 'show'])->name('master.viagens.show');

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

    Route::get('feedbacks', [WebFuncionarioFeedbackController::class, 'index'])->name('painel.feedbacks.index');
    Route::get('feedbacks/{feedback}', [WebFuncionarioFeedbackController::class, 'show'])->name('painel.feedbacks.show');
});

Route::prefix('painel/operador')->middleware(['auth', 'role:admin,operador'])->name('painel.operador.')->group(function () {
    Route::get('/checklists', [OperadorChecklistController::class, 'index'])->name('checklists.index');
    Route::get('/checklists/{id}', [OperadorChecklistController::class, 'show'])->name('checklists.show');

    Route::get('/solicitacoes', [OperadorSolicitacaoController::class, 'index'])->name('solicitacoes.index');
    Route::get('/solicitacoes/create', [OperadorSolicitacaoController::class, 'create'])->name('solicitacoes.create');
    Route::post('/solicitacoes', [OperadorSolicitacaoController::class, 'store'])->name('solicitacoes.store');
    Route::get('/solicitacoes/{id}', [OperadorSolicitacaoController::class, 'show'])->name('solicitacoes.show');
    Route::patch('/solicitacoes/{id}/status', [OperadorSolicitacaoController::class, 'updateStatus'])->name('solicitacoes.status');
    Route::patch('/solicitacoes/{id}/atribuir', [OperadorSolicitacaoController::class, 'atribuir'])->name('solicitacoes.atribuir');

    Route::get('/atrasos', [OperadorAtrasoController::class, 'index'])->name('atrasos.index');
    Route::post('/solicitacoes/{id}/atraso', [OperadorAtrasoController::class, 'storeViagem'])->name('atrasos.viagem.store');
    Route::post('/solicitacoes/{id}/atraso-passageiro', [OperadorAtrasoController::class, 'storePassageiro'])->name('atrasos.passageiro.store');
    Route::post('/solicitacoes/{id}/ocorrencia', [OperadorOcorrenciaController::class, 'store'])->name('ocorrencias.store');
});

Route::prefix('painel/cliente')->middleware(['auth', 'tenant'])->name('painel.cliente.')->group(function () {
    Route::get('/solicitacoes', fn () => redirect()->route('tenant.viagens.index'))->name('solicitacoes.index');
    Route::get('/atrasos', fn () => redirect()->route('tenant.relatorios.index'))->name('atrasos.index');
});
