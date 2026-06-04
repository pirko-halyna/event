<?php

namespace Tests\Unit\Commands;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeletePasswordResetTokensCommandTest extends TestCase
{
    #[Test]
    public function handle_deletes_expired_tokens(): void
    {
        $expiredUser = User::factory()->create();
        $activeUser  = User::factory()->create();

        DB::table('password_reset_tokens')->insert([
            ['email' => $expiredUser->email, 'token' => hash('sha256', 'old'), 'created_at' => now()->subDays(2)],
            ['email' => $activeUser->email,  'token' => hash('sha256', 'new'), 'created_at' => now()],
        ]);

        $this->artisan('password-reset-tokens:delete')->assertSuccessful();

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $expiredUser->email]);
        $this->assertDatabaseHas('password_reset_tokens',    ['email' => $activeUser->email]);
    }
}
