<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    /**
     * Login member by email and password
     * if rememberMe is true - set longer TTL
     *
     * @param array $credentials
     * @param bool $rememberMe
     * @return string
     * @throws AuthenticationException
     */
    public function login(array $credentials, bool $rememberMe = false): string
    {
        $ttl = $rememberMe ? config('jwt.remember_me_ttl') : config('jwt.ttl');

        if (!$token = auth()->setTTL($ttl)->attempt($credentials)) {
            throw new AuthenticationException();
        }

        return $token;
    }

    /**
     * Register new user and login
     *
     * @param array $data
     * @return string
     */
    public function register(array $data): string
    {
        $user = User::create($data);

        Log::info('User created successfully.', ['user_id' => $user->id]);

        return auth()->login($user);
    }

    public function authenticateToken(string $token): ?User
    {
        try {
            $user = JWTAuth::setToken($token)->authenticate();
        } catch (JWTException $e) {
            Log::warning('JWT authentication failed.', ['message' => $e->getMessage()]);
            return null;
        }

        if (!$user) {
            return null;
        }

        $validAfter = Cache::get("jwt_valid_after:{$user->id}");
        $issuedAt   = JWTAuth::getPayload()->get('iat');

        if ($validAfter !== null && $issuedAt < $validAfter) {
            return null;
        }

        return $user;
    }
}
