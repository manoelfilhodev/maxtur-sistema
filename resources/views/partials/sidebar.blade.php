@php
    $user = auth()->user();
    $isMaster = $user && method_exists($user, 'isMaster') ? $user->isMaster() : false;
@endphp

<div class="leftside-menu systex-sidebar">
    <div class="systex-brand">
        <div class="brand-mark" aria-hidden="true">
            <i class="fa-solid fa-fingerprint"></i>
        </div>
        <div class="brand-text">
            <strong>SYSTEX</strong>
            <small>{{ $isMaster ? 'Painel Administrativo' : 'Painel Cliente' }}</small>
        </div>
    </div>

    <div class="h-100" id="leftside-menu-container" data-simplebar>
        <ul class="side-nav mt-3">
            <li class="side-nav-title">NAVEGAÇÃO</li>

            @if($isMaster || ($user && ($user->isAdmin() || $user->isOperador())))
                <li class="side-nav-item">
                    <a href="{{ route('painel.dashboard') }}" class="side-nav-link {{ request()->routeIs('painel.dashboard') ? 'active-systex' : '' }}">
                        <i class="fa fa-home"></i><span>Dashboard</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('painel.operador.solicitacoes.index') }}" class="side-nav-link {{ request()->routeIs('painel.operador.solicitacoes.*') ? 'active-systex' : '' }}">
                        <i class="bi bi-sign-turn-right"></i><span>Viagens</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('painel.operador.atrasos.index') }}" class="side-nav-link {{ request()->routeIs('painel.operador.atrasos.*') ? 'active-systex' : '' }}">
                        <i class="bi bi-clock-history"></i><span>Atrasos e Ocorrências</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('painel.operador.checklists.index') }}" class="side-nav-link {{ request()->routeIs('painel.operador.checklists.*') ? 'active-systex' : '' }}">
                        <i class="bi bi-clipboard-check"></i><span>Checklists</span>
                    </a>
                </li>
            @endif

            @if($isMaster || ($user && $user->isAdmin()))
                <li class="side-nav-item">
                    <a href="{{ route('usuarios.index') }}" class="side-nav-link {{ request()->routeIs('usuarios.*') ? 'active-systex' : '' }}">
                        <i class="fa fa-users"></i><span>Usuários</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('painel.clientes.index') }}" class="side-nav-link {{ request()->routeIs('painel.clientes.*') ? 'active-systex' : '' }}">
                        <i class="fa fa-building"></i><span>Clientes</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('master.veiculos.index') }}" class="side-nav-link {{ request()->routeIs('master.veiculos.*') ? 'active-systex' : '' }}">
                        <i class="bi bi-truck"></i><span>Veículos</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('master.motoristas.index') }}" class="side-nav-link {{ request()->routeIs('master.motoristas.*') ? 'active-systex' : '' }}">
                        <i class="bi bi-person-badge"></i><span>Motoristas</span>
                    </a>
                </li>
                {{-- Relatórios, Feedbacks e Configurações continuam acessíveis por rota,
                     mas ficam fora da navegação principal da demo para evitar telas legadas de ponto. --}}
            @elseif(!$isMaster && !($user && ($user->isAdmin() || $user->isOperador())))
                <li class="side-nav-item">
                    <a href="{{ route('tenant.home') }}" class="side-nav-link {{ request()->routeIs('tenant.home') ? 'active-systex' : '' }}">
                        <i class="fa fa-home"></i><span>Início</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('tenant.funcionarios.index') }}" class="side-nav-link {{ request()->routeIs('tenant.funcionarios.*') ? 'active-systex' : '' }}">
                        <i class="fa fa-users"></i><span>Funcionários</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('tenant.viagens.index') }}" class="side-nav-link {{ request()->routeIs('tenant.viagens.*') ? 'active-systex' : '' }}">
                        <i class="bi bi-sign-turn-right"></i><span>Minhas Viagens</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('tenant.relatorios.index') }}" class="side-nav-link {{ request()->routeIs('tenant.relatorios.*') ? 'active-systex' : '' }}">
                        <i class="fa fa-file-alt"></i><span>Relatórios</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('tenant.feedbacks.index') }}" class="side-nav-link {{ request()->routeIs('tenant.feedbacks.*') ? 'active-systex' : '' }}">
                        <i class="bi bi-chat-left-text"></i><span>Feedbacks</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('tenant.home') }}" class="side-nav-link">
                        <i class="bi bi-person-circle"></i><span>Perfil</span>
                    </a>
                </li>
            @endif
        </ul>

        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}" class="mb-3">
                @csrf
                <button type="submit" class="btn btn-outline-light w-100">
                    <i class="fa fa-sign-out-alt me-1"></i> Sair
                </button>
            </form>
            <div class="mini muted">Powered by</div>
            <div class="fw-bold text-white" style="letter-spacing:.3px;">SYSTEX Sistemas Inteligentes</div>
            <div class="mini muted">© {{ date('Y') }}</div>
        </div>
    </div>
</div>

<style>
    .systex-sidebar{background: linear-gradient(180deg, #0b0b0b, #111111);border-right: 1px solid rgba(255,255,255,.08);}
    .systex-brand{display:flex;align-items:center;gap:12px;padding:18px 16px;border-bottom:1px solid rgba(255,255,255,.08);}
    .systex-brand .brand-mark{width:42px;height:42px;border-radius:14px;display:grid;place-items:center;background:rgba(255,42,42,.12);border:1px solid rgba(255,42,42,.25);box-shadow:0 12px 28px rgba(255,42,42,.10);}
    .systex-brand .brand-mark i{color:#ff2a2a;}
    .systex-brand .brand-text strong{display:block;color:#fff;font-weight:900;letter-spacing:.5px;line-height:1.05;}
    .systex-brand .brand-text small{display:block;color:rgba(255,255,255,.58);margin-top:2px;font-size:12px;}
    .side-nav-title{margin:14px 14px 8px;font-size:11px;letter-spacing:.8px;color:rgba(255,255,255,.38);text-transform:uppercase;}
    .side-nav-link{color:rgba(255,255,255,.78)!important;border-radius:12px;margin:6px 10px;padding:10px 12px;transition:.18s ease;display:flex;align-items:center;gap:10px;}
    .side-nav-link i{width:18px;text-align:center;color:rgba(255,255,255,.55);transition:.18s ease;}
    .side-nav-link:hover{background:rgba(255,255,255,.06);color:#fff!important;transform:translateY(-1px);}
    .side-nav-link:hover i{color:rgba(255,255,255,.85);}
    .side-nav-link.active-systex{background:rgba(255,42,42,.12);border:1px solid rgba(255,42,42,.18);box-shadow:0 12px 26px rgba(255,42,42,.08);color:#fff!important;}
    .side-nav-link.active-systex i{color:#ff2a2a;}
    .sidebar-footer{padding:14px 16px 16px;border-top:1px solid rgba(255,255,255,.08);margin-top:18px;}
    .sidebar-footer .mini{font-size:12px;color:rgba(255,255,255,.55);line-height:1.25;}
</style>
