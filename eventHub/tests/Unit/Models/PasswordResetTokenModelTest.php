<?php

namespace Tests\Unit\Models;

use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PasswordResetTokenModelTest extends TestCase
{
    #[Test]
    public function it_has_a_token()
    {
        $passwordResetToken = PasswordResetToken::create([
            'email' => 'test@domen.com',
        ]);

        $this->assertNotEmpty($passwordResetToken->token);
    }
}
