<header class="navbar navbar-expand-lg navbar-dark"
    style="background: rgba(18,18,20,.75);
           backdrop-filter: blur(12px);
           border-bottom: 1px solid rgba(255,255,255,.08);">

    <div class="container-fluid">

        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-fingerprint text-danger"></i>
            <strong class="text-white">SYSTEX MaxTur</strong>
            <span class="text-muted" style="font-size:12px;">Operação de transporte</span>
        </div>

        <ul class="navbar-nav ms-auto align-items-center gap-3">
            <li class="nav-item text-white">
                {{ Auth::user()->name }}
            </li>

            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-light">
                        <i class="fa fa-sign-out-alt me-1"></i> Sair
                    </button>
                </form>
            </li>
        </ul>

    </div>
</header>
