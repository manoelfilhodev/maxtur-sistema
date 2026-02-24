<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MobilityAppKey
{
    public function handle(Request $request, Closure $next)
    {
        $key = (string) $request->header('X-APP-KEY', '');
        $expected = (string) env('MOBILITY_APP_KEY', '');

        if ($expected === '' || $key === '' || !hash_equals($expected, $key)) {
            return response()->json([
                'message' => 'Unauthorized (invalid app key).'
            ], 401);
        }

        return $next($request);
    }
}
