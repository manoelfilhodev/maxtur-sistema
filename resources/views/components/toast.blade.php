@if (session('success'))
    <script>
        Toastify({
            text: "{{ session('success') }}",
            duration: 4000,
            close: true,
            gravity: "top",
            position: "right",
            backgroundColor: "#198754", // Verde
        }).showToast();
    </script>
@endif

@if (session('error'))
    <script>
        Toastify({
            text: "{{ session('error') }}",
            duration: 4000,
            close: true,
            gravity: "top",
            position: "right",
            backgroundColor: "#dc3545", // Vermelho
        }).showToast();
    </script>
@endif

@if (session('warning'))
    <script>
        Toastify({
            text: "{{ session('warning') }}",
            duration: 4000,
            close: true,
            gravity: "top",
            position: "right",
            backgroundColor: "#ffc107", // Amarelo
        }).showToast();
    </script>
@endif

@if (session('info'))
    <script>
        Toastify({
            text: "{{ session('info') }}",
            duration: 4000,
            close: true,
            gravity: "top",
            position: "right",
            backgroundColor: "#0dcaf0", // Azul Claro
        }).showToast();
    </script>
@endif
