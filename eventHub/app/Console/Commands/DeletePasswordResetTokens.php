<?php

namespace App\Console\Commands;

use App\Models\PasswordResetToken;
use Illuminate\Console\Command;

class DeletePasswordResetTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'password-reset-tokens:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset tokens';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        PasswordResetToken::where('created_at', '<', now()->subDay())->delete();
    }
}
