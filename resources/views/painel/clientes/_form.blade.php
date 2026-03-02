@php
  $cliente = $cliente ?? null;
  $isCreate = $isCreate ?? false;
@endphp

<div class="row g-3">
  <div class="col-md-8">
    <label class="form-label fw-semibold">Razao social *</label>
    <input name="razao_social" class="form-control" required
           value="{{ old('razao_social', $cliente->razao_social ?? '') }}"
           placeholder="Ex: Empresa XYZ LTDA">
    @error('razao_social') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label fw-semibold">Nome fantasia</label>
    <input name="nome_fantasia" class="form-control"
           value="{{ old('nome_fantasia', $cliente->nome_fantasia ?? '') }}"
           placeholder="Ex: XYZ">
    @error('nome_fantasia') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label fw-semibold">Documento</label>
    <input name="documento" class="form-control"
           value="{{ old('documento', $cliente->documento ?? '') }}"
           placeholder="CPF/CNPJ">
    @error('documento') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label fw-semibold">CNPJ</label>
    <input name="cnpj" class="form-control"
           value="{{ old('cnpj', $cliente->cnpj ?? '') }}"
           placeholder="00.000.000/0000-00">
    @error('cnpj') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label fw-semibold">E-mail</label>
    <input name="email" type="email" class="form-control"
           value="{{ old('email', $cliente->email ?? '') }}"
           placeholder="contato@empresa.com">
    @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-3">
    <label class="form-label fw-semibold">Telefone</label>
    <input name="telefone" class="form-control"
           value="{{ old('telefone', $cliente->telefone ?? '') }}"
           placeholder="(11) 99999-9999">
    @error('telefone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-3">
    <label class="form-label fw-semibold">WhatsApp</label>
    <input name="whatsapp" class="form-control"
           value="{{ old('whatsapp', $cliente->whatsapp ?? '') }}"
           placeholder="(11) 99999-9999">
    @error('whatsapp') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label fw-semibold">Cidade</label>
    <input name="cidade" class="form-control"
           value="{{ old('cidade', $cliente->cidade ?? '') }}"
           placeholder="Ex: Sao Paulo">
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
    <label class="form-label fw-semibold">Observacoes</label>
    <textarea name="observacoes" class="form-control" rows="4"
              placeholder="Notas internas e detalhes do atendimento">{{ old('observacoes', $cliente->observacoes ?? '') }}</textarea>
    @error('observacoes') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  @if($isCreate)
    <div class="col-12"><hr style="border-color: rgba(255,255,255,.12);"></div>
    <div class="col-md-6">
      <label class="form-label fw-semibold">Email CLIENT_ADMIN *</label>
      <input name="email_admin" type="email" class="form-control"
             value="{{ old('email_admin') }}"
             placeholder="admin.cliente@empresa.com" required>
      @error('email_admin') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
      <label class="form-label fw-semibold">Nome CLIENT_ADMIN</label>
      <input name="nome_admin" class="form-control"
             value="{{ old('nome_admin') }}"
             placeholder="Responsavel do cliente">
      @error('nome_admin') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>
  @endif
</div>

