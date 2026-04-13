<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\AuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthTokenMiddleware
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $user = $this->authService->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        auth()->setUser($user);
        return $next($request);
    }
}
