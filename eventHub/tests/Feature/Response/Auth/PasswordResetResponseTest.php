<?php

namespace Tests\Feature\Response\Auth;

use App\Models\User;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

#[Group('response')]
#[Group('auth')]
class PasswordResetResponseTest extends TestCase
{
    private const EXPECTED_MESSAGE = 'If an account with that email exists, a verification code has been sent.';

    #[Test]
    public function it_returns_200_for_existing_email(): void
    {
        $user = User::factory()->create();

        $this->postJson(route('auth.password-reset.request'), ['email' => $user->email])
            ->assertOk()
            ->assertJson(['message' => self::EXPECTED_MESSAGE]);
    }

    #[Test]
    public function it_returns_200_for_nonexistent_email(): void
    {
        $this->postJson(route('auth.password-reset.request'), ['email' => 'ghost@example.com'])
            ->assertOk()
            ->assertJson(['message' => self::EXPECTED_MESSAGE]);
    }

    #[Test]
    public function response_body_is_identical_for_existing_and_nonexistent_email(): void
    {
        $user = User::factory()->create();

        $existingResponse = $this->postJson(
            route('auth.password-reset.request'),
            ['email' => $user->email]
        )->json();

        $missingResponse = $this->postJson(
            route('auth.password-reset.request'),
            ['email' => 'ghost@example.com']
        )->json();

        $this->assertSame($existingResponse, $missingResponse);
    }
}
