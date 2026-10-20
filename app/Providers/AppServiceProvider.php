<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
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

        // De wachtwoordregels uit de casus gelden overal: bij registreren, wachtwoord vergeten
        // en wachtwoord wijzigen. Ze gelden ook op mijn eigen computer, niet alleen in productie.
        Password::defaults(function (): Password {
            // Minimaal 12 tekens, hoofdletter (en kleine letter), cijfer en leesteken.
            $regels = Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols();

            // 'uncompromised' vraagt aan een externe website of het wachtwoord in een datalek staat.
            // Dat doen we alleen in productie, anders werkt registreren niet zonder internet.
            return app()->isProduction() ? $regels->uncompromised() : $regels;
        });
    }
}
