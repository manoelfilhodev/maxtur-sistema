@extends('layouts.app')

@section('content')
<div class="container py-4">

  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <h4 class="mb-0 text-white">Configurações • Jornadas & Escalas</h4>
      <div class="text-muted small">Os horários ficam salvos no banco (settings: <code>ponto.jornadas</code>).</div>
    </div>

    <div class="d-flex gap-2">
      <a href="{{ route('painel.configuracoes.index') }}" class="btn btn-sm btn-outline-light">Voltar</a>

      <form method="POST" action="{{ route('painel.configuracoes.jornadas.seed.rafisa') }}">
        @csrf
        <button class="btn btn-sm btn-danger">
          Carregar padrão (Rafisa)
        </button>
      </form>
    </div>
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

  <div class="row g-3">
    {{-- Preview --}}
    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm"
        style="background: rgba(255,255,255,.04); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,.08);">
        <div class="card-body">
          <div class="text-white fw-semibold mb-2">Jornadas atuais</div>

          @if(empty($jornadas))
            <div class="text-muted small">
              Nenhuma jornada cadastrada ainda. Clique em <b>Carregar padrão (Rafisa)</b>.
            </div>
          @else
            <div class="d-flex flex-column gap-2">
              @foreach($jornadas as $j)
                <div class="p-3 rounded-3"
                     style="background: rgba(0,0,0,.25); border: 1px solid rgba(255,255,255,.06);">
                  <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                      <div class="text-white fw-semibold">{{ $j['nome'] ?? $j['id'] }}</div>
                      <div class="text-muted small">
                        Tipo: <b>{{ $j['tipo'] ?? '-' }}</b>
                        @if(!empty($j['dias_semana']))
                          • Dias: {{ implode(',', $j['dias_semana']) }}
                        @endif
                      </div>
                    </div>
                    <span class="badge text-bg-dark" style="border:1px solid rgba(255,255,255,.12)">
                      {{ $j['id'] ?? '---' }}
                    </span>
                  </div>

                  @if(($j['tipo'] ?? '') === 'FIXA' && !empty($j['entrada']))
                    <div class="text-muted small mt-2">
                      ⏱ {{ $j['entrada'] }} → {{ $j['saida'] }}
                    </div>
                  @endif

                  @if(($j['tipo'] ?? '') === 'FIXA_TURNOS' && !empty($j['turnos']))
                    <div class="mt-2 d-flex flex-column gap-1">
                      @foreach($j['turnos'] as $t)
                        <div class="text-muted small">
                          • {{ $t['nome'] ?? $t['id'] }} ({{ $t['entrada'] }} → {{ $t['saida'] }})
                        </div>
                      @endforeach
                    </div>
                  @endif

                  @if(!empty($j['tolerancia']))
                    <div class="text-muted small mt-2">
                      Tolerância: entrada {{ $j['tolerancia']['entrada'] ?? 0 }}m • saída {{ $j['tolerancia']['saida'] ?? 0 }}m
                    </div>
                  @endif
                </div>
              @endforeach
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- Editor JSON (simples e seguro pra shared hosting) --}}
    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm"
        style="background: rgba(255,255,255,.04); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,.08);">
        <div class="card-body">
          <div class="text-white fw-semibold mb-2">Editor (JSON)</div>
          <div class="text-muted small mb-2">
            Você pode editar aqui quando precisar adicionar/remover jornadas. Se colar algo inválido, o sistema bloqueia.
          </div>

          <form method="POST" action="{{ route('painel.configuracoes.jornadas.salvar') }}">
            @csrf
            <textarea name="jornadas_json" rows="18" class="form-control"
              style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;"
            >{{ old('jornadas_json', json_encode($jornadas, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) }}</textarea>

            <button class="btn btn-danger w-100 mt-3">Salvar Jornadas</button>
          </form>

        </div>
      </div>
    </div>
  </div>

</div>
@endsection
