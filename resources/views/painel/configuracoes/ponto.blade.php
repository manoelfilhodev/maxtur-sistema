@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <h4 class="mb-0 text-white">Configurações • Regras de Ponto</h4>
      <div class="text-muted small">Defina o padrão do sistema (padrão global por enquanto)</div>
    </div>
    <a href="{{ route('painel.configuracoes.index') }}" class="btn btn-sm btn-outline-light">
      Voltar
    </a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('painel.configuracoes.ponto.salvar') }}">
    @csrf

    <div class="row g-3">
      <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm"
          style="background: rgba(255,255,255,.04); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,.08);">
          <div class="card-body">
            <div class="text-white fw-semibold mb-2">Validações</div>

            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" name="validar_sequencia" value="1"
                     id="validar_sequencia" @checked(($regras['validar_sequencia'] ?? true) === true)>
              <label class="form-check-label text-white" for="validar_sequencia">
                Validar sequência (Entrada → Saída → Entrada)
              </label>
              <div class="text-muted small">Evita registros fora da ordem.</div>
            </div>

            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" name="bloquear_duplicado" value="1"
                     id="bloquear_duplicado" @checked(($regras['bloquear_duplicado'] ?? true) === true)>
              <label class="form-check-label text-white" for="bloquear_duplicado">
                Bloquear duplicidade (ex.: duas Entradas seguidas)
              </label>
              <div class="text-muted small">Impede inconsistências automáticas.</div>
            </div>

            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="alertar_fora_janela" value="1"
                     id="alertar_fora_janela" @checked(($regras['alertar_fora_janela'] ?? true) === true)>
              <label class="form-check-label text-white" for="alertar_fora_janela">
                Alertar quando bater fora da janela
              </label>
              <div class="text-muted small">Marca e permite notificação futura para admin.</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm"
          style="background: rgba(255,255,255,.04); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,.08);">
          <div class="card-body">
            <div class="text-white fw-semibold mb-2">Tolerâncias padrão (minutos)</div>

            <div class="mb-2">
              <label class="text-muted small">Entrada</label>
              <input type="number" min="0" max="120" class="form-control"
                     name="tolerancia_entrada"
                     value="{{ old('tolerancia_entrada', $tolerancia['entrada'] ?? 5) }}">
            </div>

            <div class="mb-0">
              <label class="text-muted small">Saída</label>
              <input type="number" min="0" max="120" class="form-control"
                     name="tolerancia_saida"
                     value="{{ old('tolerancia_saida', $tolerancia['saida'] ?? 5) }}">
            </div>
          </div>
        </div>

        <button class="btn btn-danger w-100 mt-3">
          Salvar configurações
        </button>
      </div>
    </div>
  </form>
</div>
@endsection
