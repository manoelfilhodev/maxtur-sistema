<div class="leftside-menu systex-sidebar">

    <!-- Brand -->
    <div class="systex-brand">
        <div class="brand-mark" aria-hidden="true">
            <i class="fa-solid fa-fingerprint"></i>
        </div>
        <div class="brand-text">
            <strong>SYSTEX</strong>
            <small>Ponto • Cliente: Maxtur</small>
        </div>
    </div>

    <!-- Menu -->
    <div class="h-100" id="leftside-menu-container" data-simplebar>
        <ul class="side-nav mt-3">

            <li class="side-nav-title">NAVEGAÇÃO</li>

            <li class="side-nav-item">
                <a href="{{ route('painel.dashboard') }}"
                   class="side-nav-link {{ request()->routeIs('painel.dashboard') ? 'active-systex' : '' }}">
                    <i class="fa fa-home"></i>
                    <span>Início</span>
                </a>
            </li>

            <li class="side-nav-item">
                <a href="{{ route('usuarios.index') }}"
                   class="side-nav-link {{ request()->routeIs('usuarios.*') ? 'active-systex' : '' }}">
                    <i class="fa fa-users"></i>
                    <span>Usuários</span>
                </a>
            </li>
            <li class="side-nav-item">
                <a href="{{ route('checklists.index') }}"
                   class="side-nav-link {{ request()->routeIs('checklists.*') ? 'active-systex' : '' }}">
                    <i class="bi bi-clipboard-check"></i>
                    <span>Check-list de Veículos</span>
                </a>
            </li>

           <li class="side-nav-item">
                <a href="{{ route('painel.clientes.index') }}"
                   class="side-nav-link {{ request()->routeIs('painel.clientes.*') ? 'active-systex' : '' }}">
                   <i class="fa fa-building"></i>
                   <span>Clientes</span>
                </a>
            </li>

            <li class="side-nav-item">
              <a href="{{ route('painel.relatorios.index') }}"
                 class="side-nav-link {{ request()->routeIs('painel.relatorios.*') ? 'active-systex' : '' }}">
                  <i class="fa fa-file-alt"></i>
                  <span>Relatórios</span>
              </a>
            </li>

            
            <li class="side-nav-item">
    <a class="side-nav-link {{ request()->is('painel/configuracoes*') ? 'active' : '' }}"
       href="{{ route('painel.configuracoes.index') }}">
        <i class="bi bi-gear"></i>
        <span>Configurações</span>
    </a>
</li>



            <li class="side-nav-title mt-3">INTEGRAÇÕES</li>

            <li class="side-nav-item">
                <a href="https://maxtur.com.br/admin/"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="side-nav-link">
                    <i class="fa-solid fa-globe"></i>
                    <span>Admin do Site (cliente)</span>
                    <span class="badge-ext ms-auto">externo</span>
                </a>
            </li>

        </ul>

        <div class="sidebar-footer">
            <div class="mini muted">Powered by</div>
            <div class="fw-bold text-white" style="letter-spacing:.3px;">SYSTEX Sistemas Inteligentes</div>
            <div class="mini muted">© {{ date('Y') }}</div>
        </div>
    </div>
</div>

<style>
    /* ===== Sidebar base ===== */
    .systex-sidebar{
        background: linear-gradient(180deg, #0b0b0b, #111111);
        border-right: 1px solid rgba(255,255,255,.08);
    }

    /* ===== Brand topo ===== */
    .systex-brand{
        display:flex;
        align-items:center;
        gap:12px;
        padding: 18px 16px;
        border-bottom: 1px solid rgba(255,255,255,.08);
    }
    .systex-brand .brand-mark{
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display:grid;
        place-items:center;
        background: rgba(255,42,42,.12);
        border: 1px solid rgba(255,42,42,.25);
        box-shadow: 0 12px 28px rgba(255,42,42,.10);
    }
    .systex-brand .brand-mark i{ color:#ff2a2a; }
    .systex-brand .brand-text strong{
        display:block;
        color:#fff;
        font-weight:900;
        letter-spacing:.5px;
        line-height:1.05;
    }
    .systex-brand .brand-text small{
        display:block;
        color: rgba(255,255,255,.58);
        margin-top: 2px;
        font-size: 12px;
    }

    /* ===== Section titles ===== */
    .side-nav-title{
        margin: 14px 14px 8px;
        font-size: 11px;
        letter-spacing: .8px;
        color: rgba(255,255,255,.38);
        text-transform: uppercase;
    }

    /* ===== Links ===== */
    .side-nav-link{
        color: rgba(255,255,255,.78) !important;
        border-radius: 12px;
        margin: 6px 10px;
        padding: 10px 12px;
        transition: .18s ease;
        display:flex;
        align-items:center;
        gap:10px;
    }

    .side-nav-link i{
        width: 18px;
        text-align:center;
        color: rgba(255,255,255,.55);
        transition: .18s ease;
    }

    .side-nav-link:hover{
        background: rgba(255,255,255,.06);
        color:#fff !important;
        transform: translateY(-1px);
    }

    .side-nav-link:hover i{ color: rgba(255,255,255,.85); }

    /* ===== Active state SYSTEX ===== */
    .side-nav-link.active-systex{
        background: rgba(255,42,42,.12);
        border: 1px solid rgba(255,42,42,.18);
        box-shadow: 0 12px 26px rgba(255,42,42,.08);
        color:#fff !important;
    }
    .side-nav-link.active-systex i{ color:#ff2a2a; }

    /* ===== External badge ===== */
    .badge-ext{
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 999px;
        background: rgba(255,255,255,.06);
        border: 1px solid rgba(255,255,255,.10);
        color: rgba(255,255,255,.70);
    }

    /* ===== Footer ===== */
    .sidebar-footer{
        padding: 14px 16px 16px;
        border-top: 1px solid rgba(255,255,255,.08);
        margin-top: 18px;
    }
    .sidebar-footer .mini{
        font-size: 12px;
        color: rgba(255,255,255,.55);
        line-height: 1.25;
    }
</style>
