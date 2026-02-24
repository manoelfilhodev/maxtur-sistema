# Maxtur - Laravel 11

Backend e painel web do projeto Maxtur com modelo de negocio:
- 1 operador (tenant principal)
- varios clientes finais atendidos pelo operador
- usuarios com papel `admin`, `cliente` e `motorista`

## Requisitos
- PHP 8.2+
- Composer
- MySQL/MariaDB

## Setup rapido
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=ChecklistItensSeeder
php artisan storage:link
php artisan serve
```

## Modulos principais
- Checklist de veiculos (escopo do operador)
- Solicitacoes de viagem (escopo de cliente final)
- Controle de atrasos (viagem e passageiro)
- Notificacoes MVP (`VIAGEM_SOLICITADA`, `CHECKLIST_REPROVADO`)
- API com Sanctum (token pessoal para Flutter)

## Regras de escopo
- Sempre filtrar por `operador_id`
- Usuario `cliente`: tambem filtrar por `cliente_id`
- Usuario `motorista`: checklists dele e viagens atribuidas (escopo operador)

## Upload de imagens do checklist
- Entrada API: `foto_base64`
- Persistencia em:
  - `storage/app/public/checklists/{checklist_id}/itens/{codigo}/{timestamp}_{rand}.jpg`
- Campo salvo em banco:
  - `storage/checklists/{checklist_id}/itens/{codigo}/{arquivo}.jpg`

## API (rotas principais)

### Auth
- `POST /api/auth/login` (publico, throttle `login`)
- `POST /api/auth/logout` (`auth:sanctum`)
- `GET /api/me` (`auth:sanctum`)

### Checklist
- `POST /api/checklists/iniciar` (`auth:sanctum`, `throttle:api-write`)
- `POST /api/checklists/{id}/respostas` (`auth:sanctum`, `throttle:api-write`)
- `POST /api/checklists/{id}/finalizar` (`auth:sanctum`, `throttle:api-write`)

### Solicitacoes
- `POST /api/cliente/solicitacoes` (`auth:sanctum`, `role:cliente`, `throttle:api-write`)
- `GET /api/cliente/solicitacoes` (`auth:sanctum`, `role:cliente`)
- `GET /api/admin/solicitacoes` (`auth:sanctum`, `role:admin`)
- `PATCH /api/admin/solicitacoes/{id}/status` (`auth:sanctum`, `role:admin`, `throttle:api-write`)
- `PATCH /api/admin/solicitacoes/{id}/atribuir` (`auth:sanctum`, `role:admin`, `throttle:api-write`)

### Atrasos
- `POST /api/admin/solicitacoes/{id}/atraso` (`auth:sanctum`, `role:admin`, `throttle:api-write`)
- `POST /api/admin/solicitacoes/{id}/atraso-passageiro` (`auth:sanctum`, `role:admin`, `throttle:api-write`)

### Notificacoes
- `GET /api/notifications` (`auth:sanctum`)
- `PATCH /api/notifications/{id}/read` (`auth:sanctum`, `throttle:api-write`)

## Payloads de exemplo (Flutter)

### Login
Request:
```json
{
  "email": "admin@maxtur.com",
  "password": "123456"
}
```

Response (200):
```json
{
  "ok": true,
  "message": "Login realizado com sucesso",
  "data": {
    "token": "1|token...",
    "user": {
      "id": 1,
      "name": "Admin",
      "email": "admin@maxtur.com",
      "role": "admin",
      "operador_id": 1,
      "cliente_id": null
    }
  }
}
```

### Iniciar checklist
```json
{
  "veiculo_id": 10,
  "motorista_id": 25
}
```

### Responder checklist
```json
{
  "respostas": [
    {
      "codigo": 1,
      "status": "ok"
    },
    {
      "codigo": 2,
      "status": "falha",
      "observacao": "Extintor vencido",
      "foto_base64": "data:image/jpeg;base64,/9j/4AAQSk..."
    }
  ]
}
```

## Painel web MVP
- Operador:
  - `/painel/operador/checklists`
  - `/painel/operador/solicitacoes`
  - `/painel/operador/atrasos`
- Cliente final:
  - `/painel/cliente/solicitacoes`
  - `/painel/cliente/atrasos`

## Compatibilidade
- Rotas antigas em `/api/v1` foram mantidas para nao quebrar integracoes existentes.
