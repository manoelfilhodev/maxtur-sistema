<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Systex')</title>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body class="@if(session('theme') === 'dark') dark-mode @endif">
    @include('partials.sidebar')
    <main class="main-content">
        @include('partials.navbar')
        <div class="container-fluid p-4">
            @yield('content')
        </div>
    </main>
</body>
</html>
