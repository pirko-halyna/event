<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        ResetPassword::createUrlUsing(fn ($notifiable, string $token) =>
            rtrim(config('app.frontend_url', config('app.url')), '/')
            . '/reset-password?token=' . $token
            . '&email=' . urlencode($notifiable->getEmailForPasswordReset())
        );

        $this->loginLimiter();
        $this->passwordLimiter();
        $this->registerLimiter();
    }

    private function loginLimiter(): void {
        RateLimiter::for('login', function (Request $request) {
            $email = $request->input('email');

            return [
                Limit::perMinute(3)->by($email ?: $request->ip()),
            ];
        });
    }

    private function passwordLimiter(): void {
        RateLimiter::for('password-reset', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->ip()),
            ];
        });
    }

    private function registerLimiter(): void {
        RateLimiter::for('register', function (Request $request) {
            return [
                Limit::perMinute(10)->by($request->ip()),
            ];
        });
    }
}
