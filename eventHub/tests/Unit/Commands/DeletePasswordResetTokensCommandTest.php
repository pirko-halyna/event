<?php

namespace Tests\Unit\Commands;

use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeletePasswordResetTokensCommandTest extends TestCase
{
    #[Test]
    public function handle_deletes_expired_tokens()
    {
        $oldToken = PasswordResetToken::factory()->create([
            'created_at' => now()->subDays(2),
        ]);
        $this->assertModelExists($oldToken);

        $newToken = PasswordResetToken::factory()->create();

        $this->artisan('password-reset-tokens:delete')->assertSuccessful();

        $this->assertModelMissing($oldToken);
        $this->assertModelExists($newToken);
    }
}
