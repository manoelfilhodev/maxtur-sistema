# Maxtur Sistema (Laravel 11)

API e painel web do ecossistema Maxtur, com autenticação por token (Sanctum), perfis por role e integração direta com o app Flutter.

## Repositórios

- App Flutter: `https://github.com/manoelfilhodev/maxtur-app`
- API Laravel: `https://github.com/manoelfilhodev/maxtur-sistema`

## Perfis suportados

- `admin`
- `cliente`
- `motorista`

O app Flutter roteia após login usando o retorno de `GET /api/me`.

## Modelo de negócio (resumo)

- 1 operador (tenant principal)
- vários clientes finais vinculados ao operador
- veículos e motoristas sempre pertencem ao operador
- usuários `cliente` obrigatoriamente com `cliente_id`
- usuários `admin` e `motorista` com `cliente_id = null`

## Stack

- Laravel 11
- Sanctum (Personal Access Token)
- MySQL/MariaDB
- Blade (painel web)
- Scribe (docs da API)

## Estrutura principal

```text
app/
  Http/
  Models/
  Services/
database/
  migrations/
  seeders/
routes/
  api.php
  web.php
resources/
  views/
public/
  docs/ (Scribe)
```

## Pré-requisitos

- PHP 8.2+
- Composer
- MySQL/MariaDB

## Configuração local

### 1) Instalar dependências e ambiente

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### 2) Banco e dados iniciais

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
```

### 3) Subir API/painel

```bash
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8000
```

## Integração com Flutter (local)

No app Flutter, use `API_BASE_URL` apontando para o backend Laravel:

```text
http://127.0.0.1:8000
```

Exemplo:

```bash
flutter run -d chrome --dart-define=API_BASE_URL=http://127.0.0.1:8000
```

## CORS

Arquivo: `config/cors.php`

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
```

## Endpoints esperados (API)

- `POST /api/auth/login`
- `POST /api/auth/logout`
- `GET /api/me`
- `POST /api/checklists/iniciar`
- `POST /api/checklists/{id}/respostas`
- `POST /api/checklists/{id}/finalizar`
- `POST /api/cliente/solicitacoes`
- `GET /api/cliente/solicitacoes`
- `GET /api/admin/solicitacoes`
- `PATCH /api/admin/solicitacoes/{id}/status`
- `PATCH /api/admin/solicitacoes/{id}/atribuir`
- `POST /api/admin/solicitacoes/{id}/atraso`
- `POST /api/admin/solicitacoes/{id}/atraso-passageiro`
- `GET /api/notifications`
- `PATCH /api/notifications/{id}/read`

## Documentação da API (Scribe)

Gerar docs:

```bash
php artisan scribe:generate
```

Arquivos gerados:

- `public/docs/index.html`
- `public/docs/openapi.yaml`
- `public/docs/postman.json`

Acesso local:

- `http://127.0.0.1:8000/docs`

## Painel web

- Operador:
  - `/painel/operador/checklists`
  - `/painel/operador/solicitacoes`
  - `/painel/operador/atrasos`
- Cliente final:
  - `/painel/cliente/solicitacoes`
  - `/painel/cliente/atrasos`

## Usuários de homologação (seed)

- Admin: `dev@systex.com.br` / `nVbb261214!@`
- Cliente: `cliente.alpha@systex.com` / `123456`
- Motorista: `motorista1@systex.com` / `123456`

## Qualidade e validação

```bash
php artisan route:list
php artisan test
```

## Troubleshooting

- `419` no login: app chamou rota web (`/login`) com CSRF. Use `/api/auth/login`.
- `DioException [connection error]` no Flutter Web: normalmente CORS, URL base incorreta ou API offline.
- No app, não usar `/painel`; endpoints da integração mobile/web são em `/api/*`.

## Compatibilidade

- Rotas legadas em `/api/v1` foram mantidas para não quebrar integrações antigas.
