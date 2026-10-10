<?php

namespace App\Providers;

use App\View\Composers\MenuComposer;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
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
        $this->configureDefaults();

        /*
        |--------------------------------------------------------------------------
        | SuperAdmin bypass
        |--------------------------------------------------------------------------
        */

        Gate::before(function ($user, $ability) {
            return $user->hasRole('SuperAdmin')
                ? true
                : null;
        });

        /*
        |--------------------------------------------------------------------------
        | Menu Composer
        |--------------------------------------------------------------------------
        */

        View::composer(
            'app',
            MenuComposer::class
        );

        /*
        |--------------------------------------------------------------------------
        | Correo de recuperación de contraseña
        |--------------------------------------------------------------------------
        */

        ResetPassword::toMailUsing(
            function (object $notifiable, string $token): MailMessage {
                $url = url(route('password.reset', [
                    'token' => $token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ], false));

                return (new MailMessage)
                    ->subject('Restablece tu contraseña | Sonríe Corriendo')
                    ->view('emails.auth.reset-password', [
                        'url' => $url,
                        'name' => $notifiable->name ?? '',
                    ]);
            }
        );
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn (): ?Password => app()->isProduction()
                ? Password::min(12)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
                : null,
        );
    }
}