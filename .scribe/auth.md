# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {YOUR_AUTH_KEY}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Obtenha o token em <code>POST /api/v2/auth/login</code> e envie <code>Authorization: Bearer TOKEN</code>. Operações de escrita autenticadas também exigem <code>Idempotency-Key</code>.
