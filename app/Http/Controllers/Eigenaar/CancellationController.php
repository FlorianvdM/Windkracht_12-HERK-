<?php

namespace App\Http\Controllers\Eigenaar;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Support\StandaardMail;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * De eigenaar beoordeelt annuleringen van klanten en kan zelf lessen annuleren (casus 2e en 3g).
 */
class CancellationController extends Controller
{
    /**
     * Eén pagina met twee lijsten: de aanvragen van klanten en de komende lessen.
     */
    public function index(): Response
    {
        // Aanvragen van klanten die nog op een beslissing wachten.
        $aanvragen = Lesson::query()
            ->where('status', Lesson::ANNULERING_AANGEVRAAGD)
            ->with(['reservation.customer', 'reservation.location', 'instructor'])
            ->orderBy('lesson_date')
            ->get();

        // De komende lessen (vandaag t/m 60 dagen vooruit), zodat de eigenaar er één kan annuleren.
        $komend = Lesson::query()
            ->where('status', Lesson::GEPLAND)
            ->whereDate('lesson_date', '>=', today())
            ->whereDate('lesson_date', '<=', today()->addDays(60))
            ->with(['reservation.customer', 'reservation.location', 'instructor'])
            ->orderBy('lesson_date')
            ->get();

        return Inertia::render('eigenaar/annuleringen', [
            'aanvragen' => $aanvragen->map(fn (Lesson $lesson): array => $this->data($lesson))->values(),
            'komend' => $komend->map(fn (Lesson $lesson): array => $this->data($lesson))->values(),
        ]);
    }

    /**
     * De reden is goedgekeurd: de klant mag een nieuwe lesdatum kiezen.
     */
    public function approve(Lesson $lesson): RedirectResponse
    {
        if ($lesson->status === Lesson::ANNULERING_AANGEVRAAGD) {
            $lesson->update(['status' => Lesson::NIEUWE_DATUM_KIEZEN]);

            StandaardMail::naarKlant($lesson, 'Je annulering is goedgekeurd', [
                'Je annulering is goedgekeurd.',
                'Je kunt in je overzicht "Mijn reserveringen" een nieuwe lesdatum kiezen.',
            ]);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Goedgekeurd. De klant kan een nieuwe datum kiezen.']);
        }

        return back();
    }

    /**
     * De reden is afgekeurd: de les gaat gewoon door.
     */
    public function reject(Lesson $lesson): RedirectResponse
    {
        if ($lesson->status === Lesson::ANNULERING_AANGEVRAAGD) {
            $lesson->update(['status' => Lesson::GEPLAND, 'cancel_reason' => null]);

            StandaardMail::naarKlant($lesson, 'Je annulering is afgewezen', [
                'Je annulering is helaas afgewezen. De les gaat gewoon door.',
                'Heb je vragen? Neem dan contact met ons op.',
            ]);

            Inertia::flash('toast', ['type' => 'info', 'message' => 'Afgewezen. De les gaat door.']);
        }

        return back();
    }

    /**
     * Eén les als eenvoudige lijst voor de React-pagina.
     *
     * @return array<string, mixed>
     */
    private function data(Lesson $lesson): array
    {
        return [
            'id' => $lesson->id,
            'date' => $lesson->lesson_date->format('Y-m-d'),
            'time' => substr($lesson->start_time, 0, 5),
            'reason' => $lesson->cancel_reason,
            'number' => $lesson->reservation->number,
            'customer' => $lesson->reservation->customer->name,
            'location' => $lesson->reservation->location->name,
            'instructor' => $lesson->instructor->name,
        ];
    }
}