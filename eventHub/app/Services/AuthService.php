<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
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

    public function requestPasswordReset(string $email): void
    {
        Password::sendResetLink(['email' => $email]);
    }

    /**
     * Reset user's password via the Password Broker.
     * Throws ValidationException on invalid or expired token.
     *
     * @throws ValidationException
     */
    public function resetPassword(string $token, string $email, string $newPassword): void
    {
        $status = Password::reset(
            [
                'email'                 => $email,
                'password'              => $newPassword,
                'password_confirmation' => $newPassword,
                'token'                 => $token,
            ],
            function (User $user, string $password) {
                $user->forceFill(['password' => $password])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'token' => [__($status)],
            ]);
        }
    }

    public function getUserByToken(string $token): ?User
    {
        try {
            return JWTAuth::setToken($token)->toUser();
        } catch (JWTException $e) {
            Log::warning('JWT authentication failed.', ['message' => $e->getMessage()]);
            return null;
        }
    }
}
