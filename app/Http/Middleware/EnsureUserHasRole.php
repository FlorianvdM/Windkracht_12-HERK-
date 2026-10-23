<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Beveiligt een pagina per gebruikersrol.
 * Casus: "Elke pagina is beveiligd tegen niet ingelogd zijn en tegen niet de juiste gebruikersrol hebben."
 *
 * Gebruik in de routes:
 *   ->middleware('role:klant')                 alleen klanten (en de eigenaar)
 *   ->middleware('role:klant,instructeur')     klanten en instructeurs (en de eigenaar)
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Niet ingelogd? Dan naar de inlogpagina. (De 'auth'-middleware vangt dit meestal al eerder af.)
        if ($user === null) {
            return redirect()->route('login');
        }

        // De eigenaar heeft toegang tot alle pagina's (casus: functionele eisen).
        if ($user->hasRole(Role::Eigenaar)) {
            return $next($request);
        }

        // Role::from() maakt van de tekst uit de route een Role. Een typfout in de route
        // (bijvoorbeeld 'klannt') geeft direct een foutmelding, zodat je het meteen merkt.
        $toegestaneRollen = array_map(fn (string $rol): Role => Role::from($rol), $roles);

        if ($user->hasRole(...$toegestaneRollen)) {
            return $next($request);
        }

        // Verkeerde rol: 403 "Verboden". Dit is het bewijs voor deelvraag 2.
        abort(403, 'Je hebt geen toegang tot deze pagina.');
    }
}
