<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Lesson;
use App\Support\StandaardMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Een les annuleren door een instructeur of de eigenaar (casus 1e en 3g).
 * Er zijn twee knoppen: ziekte van de instructeur en slecht weer (windkracht > 10).
 * Beide sturen een standaardmail naar de klant. De klant kiest daarna zelf een nieuwe datum.
 */
class LessonCancellationController extends Controller
{
    // De twee vaste redenen. De sleutel komt van de knop, de tekst komt in de database en de mail.
    private const REDENEN = [
        'ziekte' => 'Ziekte van de instructeur',
        'weer' => 'Slechte weersomstandigheden (windkracht > 10)',
    ];

    public function store(Request $request, Lesson $lesson): RedirectResponse
    {
        $data = $request->validate([
            'reden' => ['required', 'in:ziekte,weer'],
        ]);

        // Een instructeur mag alleen zijn eigen lessen annuleren. De eigenaar mag alles.
        $user = $request->user();
        if (! $user->hasRole(Role::Eigenaar)) {
            abort_unless($lesson->instructor_id === $user->id, 404);
        }

        // Een les waarvoor de klant al een nieuwe datum moet kiezen, annuleren we niet nog een keer.
        if ($lesson->status === Lesson::NIEUWE_DATUM_KIEZEN) {
            Inertia::flash('toast', ['type' => 'info', 'message' => 'Deze les was al geannuleerd.']);

            return back();
        }

        $reden = self::REDENEN[$data['reden']];

        $lesson->update([
            'status' => Lesson::NIEUWE_DATUM_KIEZEN,
            'cancel_reason' => $reden,
        ]);

        // De standaardmail naar de klant (komt in storage/logs/laravel.log).
        StandaardMail::naarKlant($lesson, 'Je kitesurfles is geannuleerd', [
            "Helaas gaat je kitesurfles niet door. Reden: {$reden}.",
            'Je kunt in je overzicht "Mijn reserveringen" een nieuwe lesdatum kiezen.',
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Les geannuleerd. De klant heeft een mail gekregen.']);

        return back();
    }
}