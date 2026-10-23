<?php

namespace App\Http\Controllers\Klant;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * De klant annuleert een lesdatum met een reden en kiest later een nieuwe datum (casus 2e).
 */
class LessonController extends Controller
{
    /**
     * Stap 1: de klant vraagt een annulering aan. Een reden is verplicht.
     * De eigenaar keurt de reden daarna goed of af (zie Eigenaar\CancellationController).
     */
    public function cancel(Request $request, Lesson $lesson): RedirectResponse
    {
        $this->controleerEigenaar($request, $lesson);

        $data = $request->validate([
            'reden' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'reden.required' => 'Geef een reden op.',
            'reden.min' => 'Geef een duidelijke reden op (minimaal 5 tekens).',
            'reden.max' => 'De reden mag maximaal 500 tekens zijn.',
        ]);

        // Alleen een geplande les in de toekomst kun je annuleren.
        if ($lesson->status !== Lesson::GEPLAND || ! $lesson->lesson_date->isFuture()) {
            return back()->withErrors(['reden' => 'Deze les kun je niet (meer) annuleren.']);
        }

        $lesson->update([
            'status' => Lesson::ANNULERING_AANGEVRAAGD,
            'cancel_reason' => $data['reden'],
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Je annulering is aangevraagd. De eigenaar beoordeelt je reden.']);

        return back();
    }

    /**
     * Stap 2: na goedkeuring (of een annulering door de school) kiest de klant een nieuwe lesdatum.
     */
    public function reschedule(Request $request, Lesson $lesson): RedirectResponse
    {
        $this->controleerEigenaar($request, $lesson);

        $data = $request->validate([
            'datum' => ['required', 'date_format:Y-m-d', 'after:today'],
        ], [
            'datum.required' => 'Kies een nieuwe datum.',
            'datum.date_format' => 'Dit is geen geldige datum.',
            'datum.after' => 'Kies een datum vanaf morgen.',
        ]);

        if ($lesson->status !== Lesson::NIEUWE_DATUM_KIEZEN) {
            return back()->withErrors(['datum' => 'Voor deze les kun je geen nieuwe datum kiezen.']);
        }

        // Een instructeur geeft maximaal één les per dag. Is de dag al bezet?
        $bezet = Lesson::query()
            ->where('instructor_id', $lesson->instructor_id)
            ->whereDate('lesson_date', $data['datum'])
            ->whereKeyNot($lesson->id)
            ->exists();

        if ($bezet) {
            return back()->withErrors(['datum' => 'De instructeur heeft op die dag al een les. Kies een andere dag.']);
        }

        try {
            $lesson->update([
                'lesson_date' => $data['datum'],
                'status' => Lesson::GEPLAND,
                'cancel_reason' => null,
            ]);
        } catch (UniqueConstraintViolationException) {
            // Iemand anders was net sneller met dezelfde dag.
            return back()->withErrors(['datum' => 'Die dag is zojuist bezet geraakt. Kies een andere dag.']);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Je nieuwe lesdatum is opgeslagen.']);

        return back();
    }

    /**
     * Een klant mag alleen lessen van zijn eigen reserveringen aanpassen.
     * 404 in plaats van 403: een andere klant mag niet eens weten dat deze les bestaat.
     */
    private function controleerEigenaar(Request $request, Lesson $lesson): void
    {
        abort_unless($lesson->reservation->user_id === $request->user()->id, 404);
    }
}