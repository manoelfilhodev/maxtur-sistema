@php
  $cliente = $cliente ?? null;
@endphp

<div class="row g-3">
  <div class="col-md-8">
    <label class="form-label fw-semibold">Razão social *</label>
    <input name="razao_social" class="form-control" required
           value="{{ old('razao_social', $cliente->razao_social ?? '') }}"
           placeholder="Ex: Rafisa Log LTDA">
    @error('razao_social') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label fw-semibold">Nome fantasia</label>
    <input name="nome_fantasia" class="form-control"
           value="{{ old('nome_fantasia', $cliente->nome_fantasia ?? '') }}"
           placeholder="Ex: Rafisa">
    @error('nome_fantasia') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label fw-semibold">Documento (CPF/CNPJ)</label>
    <input name="documento" class="form-control"
           value="{{ old('documento', $cliente->documento ?? '') }}"
           placeholder="Somente números ou com máscara">
    @error('documento') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label fw-semibold">E-mail</label>
    <input name="email" type="email" class="form-control"
           value="{{ old('email', $cliente->email ?? '') }}"
           placeholder="contato@empresa.com.br">
    @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-2">
    <label class="form-label fw-semibold">Telefone</label>
    <input name="telefone" class="form-control"
           value="{{ old('telefone', $cliente->telefone ?? '') }}"
           placeholder="(11) 99999-9999">
    @error('telefone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-2">
    <label class="form-label fw-semibold">WhatsApp</label>
    <input name="whatsapp" class="form-control"
           value="{{ old('whatsapp', $cliente->whatsapp ?? '') }}"
           placeholder="(11) 99999-9999">
    @error('whatsapp') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-5">
    <label class="form-label fw-semibold">Cidade</label>
    <input name="cidade" class="form-control"
           value="{{ old('cidade', $cliente->cidade ?? '') }}"
           placeholder="Ex: Cajamar">
    @error('cidade') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-2">
    <label class="form-label fw-semibold">UF</label>
    <input name="uf" class="form-control text-uppercase"
           value="{{ old('uf', $cliente->uf ?? '') }}"
           maxlength="2" placeholder="SP">
    @error('uf') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-5">
    <label class="form-label fw-semibold">Status</label>
    <select name="ativo" class="form-select">
      <option value="1" {{ old('ativo', ($cliente->ativo ?? true) ? 1 : 0) == 1 ? 'selected' : '' }}>Ativo</option>
      <option value="0" {{ old('ativo', ($cliente->ativo ?? true) ? 1 : 0) == 0 ? 'selected' : '' }}>Inativo</option>
    </select>
    @error('ativo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-12">
    <label class="form-label fw-semibold">Observações</label>
    <textarea name="observacoes" class="form-control" rows="4"
              placeholder="Notas internas, regras do contrato, detalhes do atendimento...">{{ old('observacoes', $cliente->observacoes ?? '') }}</textarea>
    @error('observacoes') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>
</div>
