<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        $this->loginLimiter();
        $this->passwordResetRequestLimiter();
        $this->passwordResetConfirmLimiter();
        $this->registerLimiter();
    }

    private function loginLimiter(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = strtolower(trim((string) $request->input('email', '')));

            return [
                Limit::perMinute(5)->by('login|email|' . $email),
                Limit::perMinute(20)->by('login|ip|' . $request->ip()),
            ];
        });
    }

    private function passwordResetRequestLimiter(): void
    {
        RateLimiter::for('password-reset-request', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->ip()),
            ];
        });
    }

    private function passwordResetConfirmLimiter(): void
    {
        RateLimiter::for('password-reset-confirm', function (Request $request) {
            return [
                Limit::perMinute(10)->by($request->ip()),
                Limit::perMinute(5)->by('email|' . $request->input('email', '')),
            ];
        });
    }

    private function registerLimiter(): void
    {
        RateLimiter::for('register', function (Request $request) {
            return [
                Limit::perMinute(10)->by($request->ip()),
            ];
        });
    }
}
