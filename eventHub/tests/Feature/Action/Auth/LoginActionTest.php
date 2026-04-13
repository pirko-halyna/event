<?php

namespace Tests\Feature\Action\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

/**
 * Class LoginActionTest
 */
#[Group('action')]
#[Group('auth')]
class LoginActionTest extends TestCase
{
    private array $credentials;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $password = Str::random(10);
        $this->user = User::factory()->create(['password' => $password]);

        $this->credentials = [
            'email' => $this->user->email,
            'password' => $password,
        ];
        $this->withoutMiddleware();
    }

    #[Test]
    public function login_auth_works(): void
    {
        $this->postJson(route('auth.login'), $this->credentials);

        $this->assertAuthenticatedAs($this->user);
    }

    #[Test]
    public function if_remember_me_true_ttl_is_longer(): void
    {
        $this->postJson(
            route('auth.login'),
            $this->credentials + ['remember_me' => true]
        );
        $expWithRememberMe = auth()->payload()->get('exp');

        $this->postJson(route('auth.login'), $this->credentials);
        $expWithoutRememberMe = auth()->payload()->get('exp');

        $this->assertTrue($expWithRememberMe > $expWithoutRememberMe);
    }

    #[Test]
    public function if_remember_me_not_passed_ttl_is_hour(): void
    {
        $this->postJson(route('auth.login'), $this->credentials);
        $expWithoutRememberMe = auth()->payload()->get('exp');

        $this->assertTrue($expWithoutRememberMe == Carbon::now()->addHour()->timestamp);
    }

    #[Test]
    public function if_remember_me_is_true_ttl_is_2_weeks(): void
    {
        $this->postJson(route('auth.login'), $this->credentials + ['remember_me' => true]);
        $expWithRememberMe = auth()->payload()->get('exp');

        $this->assertSame($expWithRememberMe, Carbon::now()->addWeeks(2)->timestamp);
    }
}
