<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class JwtAuthMiddleware
{
    public function __construct(private readonly JwtService $jwtService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization', '');

        if (! str_starts_with($header, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Missing bearer token.',
            ], 401);
        }

        try {
            $payload = $this->jwtService->decode(substr($header, 7));
            $user = User::find($payload['sub'] ?? null);
        } catch (Throwable) {
            $user = null;
        }

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
