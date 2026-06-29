<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Systex • Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b0b0b">

    {{-- Bootstrap + Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root{
            --bg-main: #0b0b0b;
            --bg-card: rgba(18,18,20,.85);
            --border-card: rgba(255,255,255,.12);
            --text-main: #ffffff;
            --text-muted: rgba(255,255,255,.75);
            --red-main: #ff2a2a;
        }

        body{
            min-height: 100vh;
            margin: 0;
            background:
                radial-gradient(800px 400px at 20% 10%, rgba(255,42,42,.15), transparent 60%),
                radial-gradient(700px 400px at 80% 80%, rgba(255,255,255,.06), transparent 60%),
                linear-gradient(180deg, #0b0b0b, #0f0f10);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .login-container{
            width: 100%;
            max-width: 420px;
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 18px;
            padding: 32px 28px;
            box-shadow: 0 20px 60px rgba(0,0,0,.6);
            backdrop-filter: blur(10px);
        }

        .form-control{
            background: rgba(0,0,0,.45);
            border: 1px solid rgba(255,255,255,.15);
            color: #fff;
            border-radius: 12px;
            padding: 12px;
        }

        .form-control::placeholder{
            color: rgba(255,255,255,.5);
        }

        .form-control:focus{
            background: rgba(0,0,0,.55);
            border-color: var(--red-main);
            box-shadow: 0 0 0 .2rem rgba(255,42,42,.25);
            color: #fff;
        }

        .btn-login{
            background: linear-gradient(135deg, #ff2a2a, #ff4a4a);
            border: none;
            border-radius: 14px;
            padding: 12px;
            font-size: 16px;
            color: #fff;
            box-shadow: 0 10px 30px rgba(255,42,42,.25);
            transition: all .15s ease;
        }

        .btn-login:hover{
            transform: translateY(-1px);
            filter: brightness(1.05);
        }

        .btn-login:active{
            transform: translateY(0);
        }

        .alert{
            border-radius: 12px;
            font-size: 14px;
        }

        .auth-copy{ color: rgba(255,255,255,.72); }

        a:focus-visible,
        button:focus-visible,
        input:focus-visible{
            outline: 3px solid rgba(255,70,70,.5);
            outline-offset: 2px;
        }

        @media (max-width: 480px){
            body{ align-items: flex-start; padding: 18px 12px; }
            .login-container{ padding: 24px 18px; border-radius: 16px; }
        }

        footer{
            font-size: 12px;
        }
    </style>
</head>

<body>

    <div class="login-container">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
