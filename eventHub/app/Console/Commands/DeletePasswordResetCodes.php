<?php

namespace App\Console\Commands;

use App\Models\PasswordResetCode;
use Illuminate\Console\Command;

class DeletePasswordResetCodes extends Command
{
    protected $signature = 'password-reset-codes:delete';

    protected $description = 'Delete expired and used password reset codes';

    public function handle(): void
    {
        PasswordResetCode::where('expires_at', '<', now())
            ->orWhereNotNull('used_at')
            ->delete();
    }
}
