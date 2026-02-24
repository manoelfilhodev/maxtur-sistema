<nav class="navbar">
    <div class="navbar-left">
        <h4 class="text-white fw-bold">Olá, {{ Auth::user()->name ?? 'Visitante' }}</h4>
    </div>
    <div class="navbar-right">
        <button onclick="document.body.classList.toggle('dark-mode')">🌗</button>
    </div>
</nav>
