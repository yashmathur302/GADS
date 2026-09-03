<?php

namespace App\Providers;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        Password::defaults(function () {
            $rule = Password::min(12)->mixedCase()->numbers()->symbols();

            // Skip the breach lookup (an external API call) in automated
            // tests so the suite stays fast and doesn't depend on network
            // access; it still applies in local, staging, and production.
            return $this->app->environment('testing') ? $rule : $rule->uncompromised();
        });

        $this->registerSecurityEventLogging();
    }

    /**
     * Log authentication-related security events to a dedicated channel.
     *
     * Only identifying, non-sensitive metadata is recorded — never
     * passwords, tokens, or other secrets.
     */
    private function registerSecurityEventLogging(): void
    {
        Event::listen(function (Login $event) {
            Log::channel('security')->info('User login', [
                'user_id' => $event->user->getAuthIdentifier(),
                'ip' => request()->ip(),
            ]);
        });

        Event::listen(function (Failed $event) {
            Log::channel('security')->warning('Failed login attempt', [
                'email' => $event->credentials['email'] ?? null,
                'ip' => request()->ip(),
            ]);
        });

        Event::listen(function (Lockout $event) {
            Log::channel('security')->warning('Login throttled', [
                'ip' => $event->request->ip(),
            ]);
        });

        Event::listen(function (Logout $event) {
            Log::channel('security')->info('User logout', [
                'user_id' => $event->user?->getAuthIdentifier(),
                'ip' => request()->ip(),
            ]);
        });

        Event::listen(function (PasswordReset $event) {
            Log::channel('security')->info('Password reset completed', [
                'user_id' => $event->user->getAuthIdentifier(),
                'ip' => request()->ip(),
            ]);
        });
    }
}
