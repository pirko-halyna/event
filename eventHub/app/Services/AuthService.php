<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Laravel\Prompts\Key;
use App\Models\{PasswordResetToken, User};
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
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

    /**
     * Reset users password by token
     * if token is valid - set new password for user and delete token
     *
     * @param string $token
     * @param string $newPassword
     * @return void
     * @throws ValidationException
     */
    public function resetPassword(string $token, string $newPassword): void
    {
        // find token
        $token = PasswordResetToken::where('token', $token)->first();

        // update password
        $user = $token->user;
        $user->password = $newPassword;
        $user->save();

        // delete used token
        $token->delete();
    }

    public function getUserByToken(string $token): ?User
    {
        try {
            return JWTAuth::setToken($token)->toUser();
        } catch (\Exception $e) {
            return null;
        }
    }
}
