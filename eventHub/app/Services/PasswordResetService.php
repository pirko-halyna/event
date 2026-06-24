<?php

namespace App\Services;

use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    /**
     * Broker generates a hashed token, stores it, and triggers
     * User::sendPasswordResetNotification() with the raw token.
     * Silent for unknown emails to prevent user enumeration.
     */
    public function sendResetCode(string $email): void
    {
        Password::sendResetLink(['email' => $email]);
    }

    /**
     * Broker validates the token against the stored hash, executes the
     * callback, then deletes the token — all handled internally.
     *
     * @throws ValidationException
     */
    public function resetPassword(string $email, string $token, string $newPassword): void
    {
        $status = Password::reset(
            [
                'email'                 => $email,
                'token'                 => $token,
                'password'              => $newPassword,
                'password_confirmation' => $newPassword,
            ],
            function ($user, string $password): void {
                $user->password = $password;
                $user->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'token' => [__($status)],
            ]);
        }
    }
}
