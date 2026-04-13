<?php

namespace Tests\Feature\Response\Auth;

use App\Models\User;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

/**
 * Class PasswordResetResponseTest
 */
#[Group('response')]
#[Group('auth')]
class PasswordResetResponseTest extends TestCase
{
    #[Test]
    public function successfulPasswordResetRequestResponse(): void
    {
        User::factory()->create([
            'email' => 'test@test.com',
        ]);

        $response = $this->postJson(route('auth.password-reset.request'), [
            'email' => 'test@test.com',
        ]);

        $response->assertOk();
    }

    #[Test]
    public function nonExistentEmailResponse(): void
    {
        $response = $this->postJson(route('auth.password-reset.request'), [
            'email' => 'nonexistent@test.com',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The email address is not registered.',
                'errors' => [
                    'email' => ['The email address is not registered.'],
                ]
            ]);
    }
}
