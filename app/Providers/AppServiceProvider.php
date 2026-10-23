<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
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
        $this->logInEnUitloggen();
    }

    /**
     * NIEUW: elke keer dat iemand in- of uitlogt, schrijven we een regel in storage/logs/inlog.log
     * (casus: in/uitlogproces stap 3 en 6). Laravel geeft zelf een 'Login'- en 'Logout'-event,
     * daar luisteren we naar. Ook na het registreren word je ingelogd, dus dat komt er ook in.
     */
    protected function logInEnUitloggen(): void
    {
        Event::listen(Login::class, function (Login $event): void {
            $this->schrijfLogregel('INGELOGD', $event->user->email);
        });

        Event::listen(Logout::class, function (Logout $event): void {
            // Bij een verlopen sessie kan de gebruiker leeg zijn. Dan valt er niets te loggen.
            if ($event->user !== null) {
                $this->schrijfLogregel('UITGELOGD', $event->user->email);
            }
        });
    }

    /**
     * Eén regel in het logbestand: datum | tijd (microseconde nauwkeurig) | actie | e-mailadres.
     * Voorbeeld: 2026-10-23 | 14:03:22.123456 | INGELOGD | lara.visser@email.nl
     */
    protected function schrijfLogregel(string $actie, string $email): void
    {
        $nu = now();

        File::append(
            storage_path('logs/inlog.log'),
            $nu->format('Y-m-d').' | '.$nu->format('H:i:s.u')." | {$actie} | {$email}".PHP_EOL,
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