@extends('layouts.app')

@section('content')
<div class="container py-4">
  <h4 class="mb-3 text-white">Configurações</h4>

  <div class="row g-3">

    {{-- Regras de Ponto --}}
    <div class="col-12 col-lg-6">
      <a href="{{ route('painel.configuracoes.ponto') }}" class="text-decoration-none">
        <div class="card border-0 shadow-sm"
             style="background: rgba(255,255,255,.04); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,.08);">
          <div class="card-body">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-3 d-flex align-items-center justify-content-center"
                   style="width:44px;height:44px;background: rgba(255,0,0,.08); border:1px solid rgba(255,0,0,.18);">
                <i class="bi bi-gear text-danger"></i>
              </div>
              <div>
                <div class="text-white fw-semibold">Regras de Ponto</div>
                <div class="text-muted small">Sequência, duplicidade, tolerâncias e alertas</div>
              </div>
              <div class="ms-auto text-muted"><i class="bi bi-chevron-right"></i></div>
            </div>
          </div>
        </div>
      </a>
    </div>

    {{-- Jornadas & Escalas --}}
    <div class="col-12 col-lg-6">
      <a href="{{ route('painel.configuracoes.jornadas') }}" class="text-decoration-none">
        <div class="card border-0 shadow-sm"
             style="background: rgba(255,255,255,.04); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,.08);">
          <div class="card-body">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-3 d-flex align-items-center justify-content-center"
                   style="width:44px;height:44px;background: rgba(255,0,0,.08); border:1px solid rgba(255,0,0,.18);">
                <i class="bi bi-clock text-danger"></i>
              </div>
              <div>
                <div class="text-white fw-semibold">Jornadas & Escalas</div>
                <div class="text-muted small">Modelos de horário, turnos e diarista</div>
              </div>
              <div class="ms-auto text-muted"><i class="bi bi-chevron-right"></i></div>
            </div>
          </div>
        </div>
      </a>
    </div>

  </div>
</div>
@endsection
