<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/iconly/bold.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/perfect-scrollbar/perfect-scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- SweetAlert-->
    <link rel="stylesheet" href="{{ asset('vendors/sweetalert2/sweetalert2.min.css') }}">

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('images/favicon.svg') }}" type="image/x-icon">
</head>

<body>
    <div id="app">
        @include('partials.sidebar')

        <div id="main">
            @include('partials.navbar')

            <div class="main-content">
                @yield('content')
            </div>

            @include('partials.footer')
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('vendors/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>

    <!-- SweetAlert -->
    <script src="{{ asset('vendors/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <!-- Componente global de alerts -->
    <x-swal-alerts />
    @if ($errors->any())
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            html: `
                <ul style="text-align: left;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            `,
            confirmButtonColor: '#d33',
            confirmButtonText: 'OK'
        });
    </script>
    @endif
    <script>
        function confirmarExclusao(event) {
            event.preventDefault();
            Swal.fire({
                title: 'Tem certeza?',
                text: "Esta ação não pode ser desfeita. Sua conta será excluída permanentemente!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-excluir').submit();
                }
            });
        }
    </script>




</body>

</html>