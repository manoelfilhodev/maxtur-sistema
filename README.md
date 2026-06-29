# Maxtur Sistema (Laravel 12)

API e painel web do ecossistema Maxtur, com autenticação por token (Sanctum), perfis por role e integração direta com o app Flutter.

## Repositórios

- App Flutter: `https://github.com/manoelfilhodev/maxtur-app`
- API Laravel: `https://github.com/manoelfilhodev/maxtur-sistema`

## Perfis suportados

- `admin`
- `cliente`
- `motorista`

O app Flutter roteia após login usando o retorno de `GET /api/v2/me`.

## Modelo de negócio (resumo)

- 1 operador (tenant principal)
- vários clientes finais vinculados ao operador
- veículos e motoristas sempre pertencem ao operador
- usuários `cliente` obrigatoriamente com `cliente_id`
- usuários `admin` e `motorista` com `cliente_id = null`

## Stack

- Laravel 12
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

## Integração com Flutter

Use sempre a URL completa da API v2 em `API_BASE_URL`:

- Android Emulator: `http://10.0.2.2:8000/api/v2`
- iOS Simulator/web/desktop local: `http://127.0.0.1:8000/api/v2`
- Produção: `https://app.maxtur.systex.com.br/api/v2`

Exemplos:

```bash
flutter run -d chrome --dart-define=API_BASE_URL=http://127.0.0.1:8000/api/v2
flutter build appbundle --release --dart-define=API_BASE_URL=https://app.maxtur.systex.com.br/api/v2
```

O app não deve possuir fallback para localhost em builds de produção.

## CORS

Arquivo: `config/cors.php`

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
```

## Endpoints canônicos (API v2)

- `POST /api/v2/auth/login`
- `POST /api/v2/auth/logout`
- `POST /api/v2/auth/refresh`
- `GET /api/v2/me`
- `GET /api/v2/motorista/viagens`
- `GET /api/v2/checklists/itens`
- `POST /api/v2/checklists/iniciar`
- `POST /api/v2/checklists/{id}/respostas`
- `POST /api/v2/checklists/{id}/finalizar`
- `GET /api/v2/motorista/pagamentos/extrato`
- `GET /api/v2/motorista/pagamentos/extrato.pdf`
- `GET|POST /api/v2/cliente/solicitacoes`
- `GET /api/v2/admin/solicitacoes`
- `GET /api/v2/notifications`

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

Produção, quando habilitada:

- `https://app.maxtur.systex.com.br/docs`

## Deploy em produção

Configuração mínima do `.env` no servidor:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://app.maxtur.systex.com.br
APP_TIMEZONE=America/Sao_Paulo
APP_LOCALE=pt_BR
SANCTUM_EXPIRATION=1440
CORS_ALLOWED_ORIGINS=https://app.maxtur.systex.com.br
SCRIBE_BASE_URL=https://app.maxtur.systex.com.br
SCRIBE_PUBLIC_DOCS_ENABLED=false
```

Depois de publicar o código:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Antes de liberar o app, valide `GET https://app.maxtur.systex.com.br/api/v2/me`. Sem token, ele deve responder `401` em JSON; depois execute login, viagens, checklist e extrato com um usuário autorizado.

## Painel web

- Operador:
  - `/painel/operador/checklists`
  - `/painel/operador/solicitacoes`
  - `/painel/operador/atrasos`
- Cliente final:
  - `/painel/cliente/solicitacoes`
  - `/painel/cliente/atrasos`

## Usuários de homologação

Crie ou redefina usuários exclusivamente no ambiente de homologação. Nunca documente senhas reais ou reutilize credenciais de produção no repositório.

## Qualidade e validação

```bash
php artisan route:list
php artisan test
```

## Troubleshooting

- `419` no login: app chamou rota web (`/login`) com CSRF. Use `/api/v2/auth/login`.
- `DioException [connection error]` no Flutter Web: normalmente CORS, URL base incorreta ou API offline.
- No app, não usar `/painel`; os endpoints canônicos são `/api/v2/*`.

## Compatibilidade

- Rotas legadas em `/api/v1` foram mantidas para não quebrar integrações antigas.
