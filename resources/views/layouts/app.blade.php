@php
    $segments = Request::segments();
    $title = ucfirst(end($segments) ?: 'Painel');
    $authUser = auth()->user();
    $isTenantUser = $authUser && method_exists($authUser, 'isMaster') ? ! $authUser->isMaster() : false;
@endphp

<!DOCTYPE html>
<html lang="pt-br" data-brand="systex">

<head>
    <meta charset="utf-8" />
    <title>@yield('title', $title) | SYSTEX {{ config('app.name') }}</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema de Ponto com Reconhecimento Facial" name="description" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}">

    <!-- CSS do template -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="light-style" />
    <link href="{{ asset('assets/css/app-dark.min.css') }}" rel="stylesheet" type="text/css" id="dark-style" disabled />

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        :root {
            /* ===== SYSTEX CORE ===== */
            --sys-bg: #0b0b0b;
            --sys-bg-2: #0f0f10;
            --sys-surface: #121214;
            --sys-surface-2: rgba(18, 18, 20, .72);
            --sys-border: rgba(255, 255, 255, .10);
            --sys-border-2: rgba(255, 255, 255, .08);
            --sys-text: #ffffff;
            --sys-muted: rgba(255, 255, 255, .68);

            /* Brand accent (SYSTEX red) */
            --accent: #ff2a2a;
            --accent-2: #ff4d4f;
            --glow: rgba(255, 42, 42, .18);

            /* States */
            --success: #22c55e;
            --warning: #facc15;
            --danger: #ff4d4f;

            --radius: 16px;
        }

        /* Remoções de elementos originais */
        .content::before,
        .content>svg,
        .sidebar-toggle,
        .vertical-menu-toggle,
        .menu-toggle {
            display: none !important;
        }

        /* ===== Base visual SYSTEX ===== */
        body {
            background:
                radial-gradient(900px 520px at 15% 25%, rgba(255, 42, 42, .08), transparent 58%),
                radial-gradient(820px 520px at 85% 70%, rgba(255, 255, 255, .05), transparent 55%),
                linear-gradient(120deg, var(--sys-bg) 0%, var(--sys-bg-2) 55%, #060606 100%);
        }

        /* “linha” discreta no topo do content (vibe produto) */
        .content-page .content {
            position: relative;
        }

        .content-page .content::after {
            content: "";
            position: absolute;
            left: 18px;
            right: 18px;
            top: -12px;
            height: 2px;
            border-radius: 999px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
            opacity: .55;
            filter: drop-shadow(0 0 10px var(--glow));
            pointer-events: none;
        }

        /* Topo operador (SYSTEX) */
        .top-bar-operador {
            background: rgba(18, 18, 20, .78);
            border-bottom: 1px solid var(--sys-border-2);
            padding: .85rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .top-bar-operador .brand-left {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .top-bar-operador .brand-mark {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: rgba(255, 42, 42, .12);
            border: 1px solid rgba(255, 42, 42, .25);
            box-shadow: 0 10px 26px rgba(255, 42, 42, .10);
        }

        .top-bar-operador .brand-mark i {
            color: var(--accent);
            font-size: 15px;
        }

        .top-bar-operador .brand-text strong {
            display: block;
            color: #fff;
            font-weight: 900;
            letter-spacing: .2px;
            line-height: 1.1;
        }

        .top-bar-operador .brand-text span {
            display: block;
            color: var(--sys-muted);
            font-size: 12px;
            margin-top: 2px;
        }

        /* Botões (melhora geral) */
        .btn {
            border-radius: 12px !important;
        }

        .btn-outline-light {
            border-color: rgba(255, 255, 255, .18) !important;
            color: rgba(255, 255, 255, .88) !important;
        }

        .btn-outline-light:hover {
            background: rgba(255, 255, 255, .08) !important;
            border-color: rgba(255, 255, 255, .24) !important;
        }

        .btn-systex {
            background: linear-gradient(135deg, var(--accent), #b30000) !important;
            border: 0 !important;
            font-weight: 800 !important;
            color: #fff !important;
            box-shadow: 0 12px 28px rgba(255, 42, 42, .16);
        }

        .btn-systex:hover {
            transform: translateY(-1px);
            opacity: .96;
            box-shadow: 0 16px 34px rgba(255, 42, 42, .22);
        }

        /* Cards Dashboard */
        .card,
        .card-statistic {
            border-radius: var(--radius) !important;
            transition: .2s;
            border: 1px solid var(--sys-border-2) !important;
            overflow: hidden;
        }

        /* Quando o template estiver em dark, reforça a cara de produto */
        .dark .card,
        .dark .card-statistic {
            background: rgba(18, 18, 20, .72) !important;
            color: #ffffff !important;
            box-shadow: 0 14px 40px rgba(0, 0, 0, .35);
        }

        /* Light continua limpo, mas com identidade SYSTEX */
        .light .card,
        .light .card-statistic {
            background: #ffffff !important;
            color: #111827 !important;
            border-color: rgba(17, 24, 39, .12) !important;
            box-shadow: 0 14px 40px rgba(0, 0, 0, .06);
        }

        .card:hover,
        .card-statistic:hover {
            transform: translateY(-2px);
        }

        /* ===== Base dark/glass global (tenant + master) ===== */
        .dash-card {
            background: rgba(18, 18, 20, .72) !important;
            border: 1px solid rgba(255, 255, 255, .08) !important;
            border-radius: 16px !important;
            box-shadow: 0 16px 45px rgba(0, 0, 0, .35);
            color: #fff !important;
        }

        .dash-card h1,
        .dash-card h2,
        .dash-card h3,
        .dash-card h4,
        .dash-card h5,
        .dash-card h6,
        .dash-card p,
        .dash-card label,
        .dash-card span,
        .dash-card small,
        .dash-card strong {
            color: inherit !important;
        }

        .dash-card .text-muted {
            color: rgba(255, 255, 255, .65) !important;
        }

        .dash-card .table,
        .dash-card .table-responsive,
        .dash-card .table * {
            background: transparent !important;
        }

        .dash-card .table thead th {
            background: rgba(255, 255, 255, .04) !important;
            color: rgba(255, 255, 255, .78) !important;
            border-bottom: 1px solid rgba(255, 255, 255, .08) !important;
        }

        .dash-card .table tbody td {
            color: rgba(255, 255, 255, .88) !important;
            border-color: rgba(255, 255, 255, .06) !important;
        }

        .dash-card .table tbody tr:hover td {
            background: rgba(255, 255, 255, .03) !important;
        }

        .dash-card .form-control,
        .dash-card .form-select,
        .dash-card textarea.form-control {
            background: rgba(255, 255, 255, .06) !important;
            border: 1px solid rgba(255, 255, 255, .10) !important;
            color: rgba(255, 255, 255, .90) !important;
        }

        .dash-card .form-control::placeholder,
        .dash-card textarea.form-control::placeholder {
            color: rgba(255, 255, 255, .45) !important;
        }

        .dash-card .form-control:focus,
        .dash-card .form-select:focus,
        .dash-card textarea.form-control:focus {
            box-shadow: 0 0 0 .2rem rgba(255, 42, 42, .12) !important;
            border-color: rgba(255, 42, 42, .35) !important;
        }

        .dash-card .pagination .page-link {
            background: rgba(255, 255, 255, .04) !important;
            color: rgba(255, 255, 255, .85) !important;
            border-color: rgba(255, 255, 255, .10) !important;
        }

        .dash-card .pagination .page-item.active .page-link {
            background: rgba(255, 42, 42, .18) !important;
            border-color: rgba(255, 42, 42, .32) !important;
            color: #fff !important;
        }

        /* Page heading melhor espaçamento */
        .page-heading {
            padding-top: 10px;
        }

        /* Pequenos detalhes padrão */
        .badge,
        .nav-pills .nav-link,
        .form-control,
        .form-select {
            border-radius: 12px !important;
        }

        /* Opcional: “accent” em links ativos */
        a:hover {
            color: var(--accent) !important;
        }
    </style>

    @yield('head')
</head>

<body class="loading {{ $isTenantUser ? 'dark' : '' }}" data-layout-config='{"darkMode": {{ $isTenantUser ? 'true' : 'false' }}}'>

    <div class="wrapper">

        {{-- SIDEBAR --}}
        @auth
            @include('partials.sidebar')
        @endauth

        <div class="content-page">
            <div class="content">

                

                {{-- 🔥 ESTA DIV É FUNDAMENTAL (igual ao WMS) --}}
                <div class="page-heading">
                    @yield('page-heading')
                </div>

                <div class="page-content">
                    @yield('content')
                </div>

            </div>

            @include('partials.footer')
        </div>

    </div>

    <!-- JS Base -->
    <script src="{{ asset('assets/js/vendor.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.min.js') }}"></script>

    <script>
        // Aplicar tema salvo
        document.addEventListener("DOMContentLoaded", function() {
            const forceTenantDark = @json($isTenantUser);
            const darkMode = forceTenantDark ? true : localStorage.getItem("darkMode") === "true";

            document.getElementById("light-style").disabled = darkMode;
            document.getElementById("dark-style").disabled = !darkMode;

            if (darkMode) {
                document.body.classList.add("dark");
            } else {
                document.body.classList.remove("dark");
            }

            document.documentElement.setAttribute(
                "data-layout-config",
                JSON.stringify({
                    darkMode
                })
            );
        });

        // Alternar tema
        function toggleDarkMode() {
            const darkMode = !(localStorage.getItem("darkMode") === "true");
            localStorage.setItem("darkMode", darkMode);

            document.getElementById("light-style").disabled = darkMode;
            document.getElementById("dark-style").disabled = !darkMode;

            if (darkMode) {
                document.body.classList.add("dark");
            } else {
                document.body.classList.remove("dark");
            }

            document.documentElement.setAttribute(
                "data-layout-config",
                JSON.stringify({
                    darkMode
                })
            );
        }
    </script>

    <!-- PWA -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js');
            });
        }
    </script>

    @yield('scripts')
    @stack('scripts')

</body>

</html>
