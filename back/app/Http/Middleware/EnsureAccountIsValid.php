<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsValid
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->estado !== 'ACTIVO' || ($user->fecha_limite && $user->fecha_limite->lessThanOrEqualTo(Carbon::today()))) {
            return response()->json([
                'message' => 'La cuenta no esta habilitada.',
            ], 403);
        }

        return $next($request);
    }
}
