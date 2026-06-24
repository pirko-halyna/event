<?php

namespace Tests\Feature\Request\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

/**
 * Class RegisterRequestTest
 */
#[Group('request')]
#[Group('auth')]
class RegisterRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    #[Test]
    public function email_is_required(): void
    {
        $this->postJson(route('auth.register'))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email' => 'The email field is required.']);
    }

    #[Test]
    public function email_must_be_a_valid_email(): void
    {
        $this->postJson(route('auth.register'), ['email' => 'invalid-email'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email' => 'The email field must be a valid email address.']);
    }

    #[Test]
    public function email_field_must_be_unique()
    {
        $password = Str::random(8);
        $user = User::factory()->create();
        $response = $this->postJson(route('auth.register'), [
            'first_name' => $user->firstname,
            'last_name' => $user->lastname,
            'email' => $user->email,
            'password' => $user->password,
            'password_confirmation' => $user->password_confirmation,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    #[Test]
    public function password_is_required(): void
    {
        $this->postJson(route('auth.register'))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password' => 'The password field is required.']);
    }

    #[Test]
    public function password_must_be_a_string(): void
    {
        $this->postJson(route('auth.register'), [
            'password' => 12345,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password' => 'The password field must be a string.']);
    }

    #[Test]
    public function first_name_is_required(): void
    {
        $this->postJson(route('auth.register'))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['first_name' => 'The first name field is required.']);
    }

    #[Test]
    public function first_name_must_be_a_string(): void
    {
        $this->postJson(route('auth.register'), [
            'first_name' => 1948983,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['first_name' => 'The first name field must be a string.']);
    }

    #[Test]
    public function first_name_is_rejected_if_too_long(): void
    {
        $this->postJson(route('auth.register'), [
            'first_name' => str_repeat('a', 256),
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'first_name' => 'The first name field must not be greater than 255 characters.'
            ]);
    }

    #[Test]
    public function last_name_is_required(): void
    {
        $this->postJson(route('auth.register'))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['last_name' => 'The last name field is required.']);
    }

    #[Test]
    public function last_name_must_be_a_string(): void
    {
        $this->postJson(route('auth.register'), [
            'last_name' => 1948983,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['last_name' => 'The last name field must be a string.']);
    }

    #[Test]
    public function last_name_is_rejected_if_too_long(): void
    {
        $this->postJson(route('auth.register'), [
            'last_name' => str_repeat('a', 256),
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'last_name' => 'The last name field must not be greater than 255 characters.'
            ]);
    }

    #[Test]
    public function phone_field_can_be_null(): void
    {
        $password = Str::random(8);
        $userData = User::factory()->withPasswordConfirmation($password)->withoutPhone()->raw();
        $this->postJson(route('auth.register'), $userData)
            ->assertStatus(200);
    }

    #[Test]
    public function phone_field_is_rejected_if_not_string(): void
    {
        $this->postJson(route('auth.register'), [
            'phone' => 466879898,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['phone' => 'The phone field must be a string.']);
    }

    #[Test]
    public function phone_field_must_have_more_than_nine_characters()
    {
        $response = $this->postJson(route('auth.register'), [
            'phone' => '12345678',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('phone');
    }
}
