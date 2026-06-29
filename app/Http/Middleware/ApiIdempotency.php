<?php

namespace App\Http\Middleware;

use App\Models\ApiIdempotencyKey;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiIdempotency
{
    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        $key = trim((string) $request->header('Idempotency-Key'));
        if ($key === '' || mb_strlen($key) > 100) {
            return response()->json([
                'ok' => false,
                'message' => 'O header Idempotency-Key é obrigatório e deve ter até 100 caracteres.',
                'data' => ['errors' => ['idempotency_key' => ['Informe uma chave única para esta operação.']]],
            ], 422);
        }

        $user = $request->user();
        $fingerprint = ['query' => $request->query(), 'body' => $request->all()];
        $this->sortRecursive($fingerprint);
        $hash = hash('sha256', $request->method().'|'.$request->path().'|'.json_encode($fingerprint));
        $existing = ApiIdempotencyKey::query()
            ->where('user_id', $user->id)
            ->where('idempotency_key', $key)
            ->first();

        if ($existing) {
            if (! hash_equals($existing->request_hash, $hash)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Idempotency-Key já utilizada com outro conteúdo.',
                    'data' => null,
                ], 409);
            }

            if ($existing->response_status !== null) {
                return response($existing->response_body, $existing->response_status, [
                    'Content-Type' => 'application/json',
                    'Idempotency-Replayed' => 'true',
                ]);
            }

            return response()->json([
                'ok' => false,
                'message' => 'Operação com esta chave ainda está em processamento.',
                'data' => null,
            ], 409);
        }

        try {
            $record = ApiIdempotencyKey::query()->create([
                'user_id' => $user->id,
                'idempotency_key' => $key,
                'method' => $request->method(),
                'path' => $request->path(),
                'request_hash' => $hash,
                'expires_at' => now()->addHours(48),
            ]);
        } catch (QueryException) {
            return response()->json([
                'ok' => false,
                'message' => 'Operação duplicada detectada. Consulte o resultado anterior.',
                'data' => null,
            ], 409);
        }

        try {
            $response = $next($request);
            $record->update([
                'response_status' => $response->getStatusCode(),
                'response_body' => $response->getContent(),
            ]);

            $response->headers->set('Idempotency-Replayed', 'false');

            return $response;
        } catch (\Throwable $exception) {
            $record->delete();
            throw $exception;
        }
    }

    private function sortRecursive(array &$value): void
    {
        if (! array_is_list($value)) {
            ksort($value);
        }

        foreach ($value as &$item) {
            if (is_array($item)) {
                $this->sortRecursive($item);
            }
        }
    }
}
