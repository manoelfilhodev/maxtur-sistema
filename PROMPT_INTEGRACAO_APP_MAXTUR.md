# Prompt para o Codex do aplicativo MaxTur

Copie e envie todo o conteúdo abaixo para o Codex que está trabalhando no repositório do app.

---

Você é um especialista sênior em integração de aplicativos mobile, arquitetura cliente/API, autenticação segura e experiência operacional para transporte fretado.

Projeto: aplicativo MaxTur.

Objetivo: atualizar o app para utilizar exclusivamente a API canônica MaxTur v2. Antes de alterar código, identifique a stack, a arquitetura, o gerenciamento de estado, a biblioteca HTTP, o armazenamento seguro e os testes existentes. Preserve os padrões do projeto e não reescreva áreas sem necessidade.

## 1. Configuração da API

- Desenvolvimento Android Emulator: `http://10.0.2.2:8000/api/v2`
- Desenvolvimento iOS Simulator, desktop e web local: `http://127.0.0.1:8000/api/v2`
- Produção: `https://app.maxtur.systex.com.br/api/v2`
- Não hardcode a URL no código Dart. Use `API_BASE_URL` por ambiente/flavor e remova qualquer fallback silencioso para localhost em builds release.
- O build de produção deve falhar se `API_BASE_URL` estiver vazio, usar HTTP ou contiver `localhost`, `127.0.0.1` ou `10.0.2.2`.
- Produção deve usar somente HTTPS e validação normal do certificado. Não implemente bypass de TLS/certificado.
- Envie sempre `Accept: application/json`.
- Em requisições JSON, envie `Content-Type: application/json`.
- Endpoints protegidos usam `Authorization: Bearer {token}`.
- Toda operação autenticada `POST`, `PATCH`, `PUT` ou `DELETE`, exceto autenticação, deve enviar `Idempotency-Key` com UUID persistido até a operação concluir.
- Se a conexão cair, repita a mesma operação com a mesma chave. Uma nova ação do usuário deve receber uma nova chave.
- O servidor pode retornar `Idempotency-Replayed: true` quando devolver o resultado anterior.

Exemplos Flutter:

```bash
# Desenvolvimento web/desktop/iOS Simulator
flutter run --dart-define=API_BASE_URL=http://127.0.0.1:8000/api/v2

# Desenvolvimento Android Emulator
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/v2

# Android release
flutter build appbundle --release \
  --dart-define=API_BASE_URL=https://app.maxtur.systex.com.br/api/v2

# iOS release
flutter build ipa --release \
  --dart-define=API_BASE_URL=https://app.maxtur.systex.com.br/api/v2
```

No CI/CD, cadastre `API_BASE_URL` como variável do ambiente de build. Não trate a URL como segredo, mas mantenha sua definição centralizada e auditável.

## 2. Envelope padrão

Respostas JSON seguem:

```json
{
  "ok": true,
  "message": "Descrição legível",
  "data": {}
}
```

Erros de validação:

```json
{
  "ok": false,
  "message": "Dados inválidos.",
  "data": {
    "errors": {
      "campo": ["Mensagem de validação"]
    }
  }
}
```

Trate pelo menos: `401` sessão inválida, `403` perfil sem acesso, `404` recurso indisponível, `409` conflito/idempotência, `422` regra ou validação e `429` limite de requisições.

## 3. Autenticação

### Login

`POST /auth/login`

```json
{
  "email": "motorista@empresa.com",
  "password": "senha",
  "device_name": "Android - Samsung A54"
}
```

O token está em `data.token`, a expiração em `data.expires_at` e o usuário em `data.user`. Armazene o token somente no mecanismo seguro da plataforma, nunca em armazenamento aberto ou logs.

### Perfil, renovação e logout

- `GET /me`
- `POST /auth/refresh`
- `POST /auth/logout`

Implemente um interceptor: adicionar Bearer token, tratar `401`, tentar uma única renovação quando aplicável e impedir loops. O login em outro dispositivo não invalida o token atual.

## 4. Motorista

- `GET /motorista/viagens`
- `GET /motorista/viagens/{id}`
- Filtros opcionais: `status`, `data_inicio`, `data_fim`, `per_page` (máximo 100).
- `POST /motorista/viagens/{id}/iniciar`
- `POST /motorista/viagens/{id}/finalizar`
- `POST /motorista/viagens/{id}/atraso`

```json
{
  "minutos_atraso": 15,
  "motivo": "Trânsito intenso",
  "ocorrido_em": "2026-06-29T08:30:00-03:00"
}
```

- `POST /motorista/viagens/{id}/ocorrencia`

```json
{
  "tipo": "Operacional",
  "descricao": "Alteração do local de desembarque",
  "ocorrido_em": "2026-06-29T09:10:00-03:00",
  "evidencia_base64": "data:image/jpeg;base64,..."
}
```

`evidencia_base64` é opcional. Aceite JPEG, PNG ou WebP e comprima/redimensione no app antes do envio. O servidor limita a imagem a 4 MB decodificados.

Mostre claramente as regras de status: só iniciar quando `pronta_para_execucao`; só finalizar quando `em_andamento` ou `atrasada`. Use `iniciada_em` e `finalizada_em` retornados pelo servidor como fonte oficial.

## 5. Checklist

- `GET /checklists/itens`
- `GET /checklists/{id}`
- `POST /checklists/iniciar`

Para motorista:

```json
{
  "solicitacao_id": 123,
  "veiculo_id": 10
}
```

O servidor identifica o motorista autenticado. Não envie outro motorista.

- `POST /checklists/{id}/respostas`

```json
{
  "respostas": [
    {"codigo": 1, "status": "ok"},
    {
      "codigo": 2,
      "status": "falha",
      "observacao": "Pneu com desgaste",
      "foto_base64": "data:image/jpeg;base64,..."
    }
  ]
}
```

Para `falha`, observação e foto são obrigatórias. Permita salvar lotes parciais e retomar o checklist. Finalização:

- `POST /checklists/{id}/finalizar`

Não permita finalizar visualmente enquanto existirem itens ativos sem resposta. Se o resultado for `nao_conforme`, informe que o veículo/viagem poderá ser bloqueado.

## 6. Pagamento do motorista

- `GET /motorista/pagamentos/extrato?data_inicio=YYYY-MM-DD&data_fim=YYYY-MM-DD`
- Filtro opcional: `status`.
- `GET /motorista/pagamentos/extrato.pdf?data_inicio=YYYY-MM-DD&data_fim=YYYY-MM-DD`

O JSON contém `data.motorista`, `data.resumo`, `data.viagens` paginadas e `data.observacao`. Para pagamento por viagem, somente viagens finalizadas têm `elegivel_pagamento=true` e valor maior que zero. Para mensalistas, mostre o salário como valor contratual, sem inventar descontos ou adicionais.

Baixe o PDF como resposta binária autenticada, preserve o nome de arquivo do `Content-Disposition` e ofereça abrir/compartilhar pelo mecanismo nativo.

## 7. Notificações

- `GET /notifications`
- `GET /notifications/unread-count`
- `PATCH /notifications/{id}/read` com `Idempotency-Key`.

Nesta versão, implemente polling ao abrir/retomar o app. Não suponha push notification se o projeto ainda não possui configuração FCM/APNs.

## 8. Cliente

- `GET /cliente/solicitacoes`
- Filtros: `status`, `data_inicio`, `data_fim`, `natureza`, `tipo_periodo`, `per_page`.
- `GET /cliente/catalogos/passageiros`
- `POST /cliente/solicitacoes` com `Idempotency-Key`.

Payload:

```json
{
  "origem": "Matriz",
  "destino": "Aeroporto",
  "data_hora": "2026-07-01T08:00:00-03:00",
  "passageiros_previstos": 12,
  "observacao": "Embarque na portaria 2",
  "passageiro_ids": [1, 2],
  "natureza": "extra",
  "tipo_periodo": "esporadico"
}
```

Valores: `natureza = programada|extra`; `tipo_periodo = diario|mensal|esporadico`.

## 9. Operador/admin, se o app oferecer esse perfil

- `GET /admin/solicitacoes` com filtros de período, cliente, motorista, veículo, status, natureza e tipo/período.
- Catálogos:
  - `GET /admin/catalogos/clientes`
  - `GET /admin/catalogos/motoristas`
  - `GET /admin/catalogos/veiculos`
  - `GET /admin/catalogos/passageiros?cliente_id={id}`
- Escritas com `Idempotency-Key`:
  - `PATCH /admin/solicitacoes/{id}/status`
  - `PATCH /admin/solicitacoes/{id}/atribuir`
  - `POST /admin/solicitacoes/{id}/atraso`
  - `POST /admin/solicitacoes/{id}/atraso-passageiro`

Não permita transições arbitrárias de status. Exiba a mensagem `422` devolvida pela API.

## 10. Funcionário

- `POST /funcionario/feedback` com `tipo=sugestao|critica`, `mensagem` e `Idempotency-Key`.
- Não use o endpoint legado `/api/app/funcionario/trip/active`: ele não faz parte do contrato v2.

## 11. Requisitos de implementação

1. Remova o uso de `/api/v1` e endpoints sem versão no app, sem alterar o backend.
2. Crie uma camada HTTP central, modelos tipados/DTOs e repositórios por domínio.
3. Datas recebidas em ISO 8601 devem ser convertidas para o fuso local apenas na apresentação.
4. Respeite paginação Laravel em `data.data`, `current_page`, `last_page`, `per_page` e `total` ou, no extrato, em `data.viagens`.
5. Não logue token, senha, CPF, fotos base64 ou respostas completas sensíveis.
6. Exiba estado de carregamento, vazio, offline, erro e tentativa novamente.
7. Evite duplo clique em operações, mas mantenha a idempotência como proteção real.
8. Preserve a chave idempotente enquanto uma operação estiver pendente na fila offline.
9. Faça logout local mesmo se a chamada remota falhar, removendo credenciais seguras.
10. Não implemente regras de remuneração no app; apenas apresente os valores calculados pelo servidor.

## 12. Testes obrigatórios

- Login, logout, renovação e expiração.
- Persistência segura do token.
- Interceptor sem loop de renovação.
- Repetição da mesma escrita com a mesma `Idempotency-Key`.
- Lista/detalhe/início/fim da viagem.
- Checklist parcial, falha com foto e finalização.
- Atraso e ocorrência retroativos.
- Extrato e download autenticado do PDF.
- Paginação e estados vazios.
- Respostas 401, 403, 409, 422 e 429.
- Teste que o build/configuração release aponta para `https://app.maxtur.systex.com.br/api/v2` e rejeita URLs locais.
- Smoke test de produção em `GET /me`: sem token deve retornar `401`, nunca `404` ou página HTML.

O endpoint `https://app.maxtur.systex.com.br/api/v2/me` já foi validado publicamente e responde `401` no envelope JSON esperado quando não recebe token. Ainda assim, não considere a integração produtiva concluída enquanto login, viagens, checklist, ocorrências e extrato passarem em um smoke test autenticado com usuário próprio de homologação/produção.

Ao concluir, informe arquivos alterados, telas integradas, endpoints utilizados, estratégia de armazenamento seguro, configuração de desenvolvimento e produção, testes executados e qualquer dependência de URL/credencial de ambiente. Não declare integração concluída sem executar os testes do app e o smoke test contra a API publicada.

---
