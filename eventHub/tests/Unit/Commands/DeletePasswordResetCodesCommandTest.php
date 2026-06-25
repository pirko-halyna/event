<?php

namespace Tests\Unit\Commands;

use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeletePasswordResetCodesCommandTest extends TestCase
{
    #[Test]
    public function it_deletes_expired_codes(): void
    {
        $user = User::factory()->create();

        PasswordResetCode::create([
            'email'      => $user->email,
            'code'       => Hash::make('123456'),
            'expires_at' => now()->subDay(),
        ]);

        $this->artisan('password-reset-codes:delete')->assertSuccessful();

        $this->assertDatabaseEmpty('password_reset_codes');
    }

    #[Test]
    public function it_does_not_delete_active_codes(): void
    {
        $user = User::factory()->create();

        PasswordResetCode::create([
            'email'      => $user->email,
            'code'       => Hash::make('123456'),
            'expires_at' => now()->addMinutes(15),
        ]);

        $this->artisan('password-reset-codes:delete')->assertSuccessful();

        $this->assertDatabaseCount('password_reset_codes', 1);
    }

    #[Test]
    public function it_deletes_used_but_unexpired_codes(): void
    {
        $user = User::factory()->create();

        PasswordResetCode::create([
            'email'      => $user->email,
            'code'       => Hash::make('123456'),
            'expires_at' => now()->addMinutes(15),
            'used_at'    => now(),
        ]);

        $this->artisan('password-reset-codes:delete')->assertSuccessful();

        $this->assertDatabaseEmpty('password_reset_codes');
    }
}
