@extends('layouts.app')

@section('page-heading')
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
  <div>
    <h3 class="fw-bold mb-1 text-white">Relatórios</h3>
    <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">
      Exporte em Excel ou PDF, com filtros por período e colaborador
    </div>
  </div>
</div>
@endsection

@section('content')
<div class="row g-3">

  <div class="col-12 col-lg-6">
    <a href="{{ route('painel.relatorios.batidas.index') }}" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100"
           style="background: rgba(255,255,255,.06); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,.10);">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-white fw-bold" style="font-size: 16px;">Batidas por Funcionário</div>
              <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">
                Excel com abas por colaborador + PDF organizado por seção
              </div>
            </div>
            <div class="text-white-50">
              <i class="fa fa-users" style="font-size: 18px;"></i>
            </div>
          </div>

          <div class="mt-3 d-flex gap-2">
            <span class="badge rounded-pill bg-dark border" style="border-color: rgba(255,255,255,.15) !important;">Excel</span>
            <span class="badge rounded-pill bg-dark border" style="border-color: rgba(255,255,255,.15) !important;">PDF</span>
            <span class="badge rounded-pill bg-dark border" style="border-color: rgba(255,255,255,.15) !important;">Período</span>
            <span class="badge rounded-pill bg-dark border" style="border-color: rgba(255,255,255,.15) !important;">Colaborador</span>
          </div>
        </div>
      </div>
    </a>
  </div>

  <div class="col-12 col-lg-6">
    <a href="{{ route('painel.relatorios.diario.index') }}" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100"
           style="background: rgba(255,255,255,.06); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,.10);">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-white fw-bold" style="font-size: 16px;">Relatório Diário</div>
              <div class="text-muted" style="color: rgba(255,255,255,.65) !important;">
                Visão consolidada por dia, com status e total de jornada
              </div>
            </div>
            <div class="text-white-50">
              <i class="fa fa-calendar-day" style="font-size: 18px;"></i>
            </div>
          </div>

          <div class="mt-3 d-flex gap-2">
            <span class="badge rounded-pill bg-dark border" style="border-color: rgba(255,255,255,.15) !important;">Excel</span>
            <span class="badge rounded-pill bg-dark border" style="border-color: rgba(255,255,255,.15) !important;">PDF</span>
            <span class="badge rounded-pill bg-dark border" style="border-color: rgba(255,255,255,.15) !important;">Status</span>
            <span class="badge rounded-pill bg-dark border" style="border-color: rgba(255,255,255,.15) !important;">Jornada</span>
          </div>
        </div>
      </div>
    </a>
  </div>

</div>
@endsection
