@php
    $segments = Request::segments();
@endphp

<div class="page-title-box mb-3">
    <h4 class="page-title text-white mb-1">
        {{ ucfirst(end($segments)) }}
    </h4>

    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item text-muted">SYSTEX</li>
        <li class="breadcrumb-item text-muted">Ponto</li>

        @foreach ($segments as $segment)
            <li class="breadcrumb-item {{ $loop->last ? 'active text-white' : 'text-muted' }}">
                {{ ucfirst($segment) }}
            </li>
        @endforeach
    </ol>
</div>
