@props(['tabs' => []])

<ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
    @foreach ($tabs as $index => $tab)
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $index === 0 ? 'active' : '' }}" id="{{ $tab['id'] }}-tab"
                data-bs-toggle="tab" data-bs-target="#{{ $tab['id'] }}" type="button" role="tab"
                aria-controls="{{ $tab['id'] }}" aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                {{ $tab['title'] }}
            </button>
        </li>
    @endforeach
</ul>
<div class="tab-content" id="myTabContent">
    {{ $slot }}
</div>
