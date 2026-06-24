<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeletePasswordResetTokens extends Command
{
    protected $signature = 'password-reset-tokens:delete';

    protected $description = 'Delete expired password reset tokens';

    public function handle(): void
    {
        DB::table('password_reset_tokens')
            ->where('created_at', '<', now()->subMinutes(config('auth.passwords.users.expire')))
            ->delete();
    }
}
