<?php

namespace App\Services;

use App\Mail\PasswordResetMail;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    private const EXPIRY_MINUTES = 15;

    public function sendResetCode(string $email): void
    {
        $code = $this->generateCode();
        $hash = Hash::make($code);

        $user = User::where('email', $email)->first();

        if ($user === null) {
            return;
        }

        DB::transaction(function () use ($user, $hash) {
            PasswordResetCode::activeForEmail($user->email)
                ->update(['used_at' => now()]);

            PasswordResetCode::create([
                'email'      => $user->email,
                'code'       => $hash,
                'expires_at' => now()->addMinutes(self::EXPIRY_MINUTES),
            ]);
        });

        Mail::to($user->email)->queue(new PasswordResetMail($code, self::EXPIRY_MINUTES));
    }

    public function resetPassword(string $email, string $code, string $newPassword): void
    {
        $userId = null;

        DB::transaction(function () use ($email, $code, $newPassword, &$userId) {
            $resetCode = PasswordResetCode::activeForEmail($email)
                ->latest()
                ->lockForUpdate()
                ->first();

            if ($resetCode === null || ! Hash::check($code, $resetCode->code)) {
                throw ValidationException::withMessages([
                    'code' => ['The provided verification code is invalid or expired.'],
                ]);
            }

            $resetCode->update(['used_at' => now()]);

            PasswordResetCode::activeForEmail($email)
                ->update(['used_at' => now()]);

            $userId = User::where('email', $email)->value('id');

            User::where('email', $email)
                ->update(['password' => Hash::make($newPassword)]);
        });

        if ($userId !== null) {
            $refreshTtl = (int) config('jwt.refresh_ttl', 20160);
            Cache::put("jwt_valid_after:{$userId}", now()->timestamp, now()->addMinutes($refreshTtl));
        }
    }

    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);
    }
}
