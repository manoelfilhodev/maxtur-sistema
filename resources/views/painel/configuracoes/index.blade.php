@extends('layouts.app')

@section('page-heading')
    @include('partials.panel.page-header', [
        'title' => 'Configurações',
        'subtitle' => 'Parâmetros operacionais do MaxTur',
    ])
@endsection

@section('content')
<div class="sx-container">
    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="sx-settings-card">
                <div class="sx-card-icon"><i class="bi bi-signpost-split"></i></div>
                <div class="sx-card-copy">
                    <strong>Operação de viagens</strong>
                    <span>Regras de aprovação, programação e execução serão consolidadas após a validação do MVP.</span>
                    <div class="mt-3"><span class="sx-badge sx-badge-warning">Em breve</span></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="sx-settings-card">
                <div class="sx-card-icon"><i class="bi bi-shield-check"></i></div>
                <div class="sx-card-copy">
                    <strong>Políticas de checklist</strong>
                    <span>Itens obrigatórios, evidências e bloqueios de veículo serão refinados no pós-MVP.</span>
                    <div class="mt-3"><span class="sx-badge sx-badge-warning">Em breve</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
