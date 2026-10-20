<?php

namespace App\Http\Controllers\Eigenaar;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Reserveringen beheren door de eigenaar: zien wat nog niet is betaald en een reservering definitief maken.
 */
class ReservationController extends Controller
{
    /**
     * De pagina "Definitief maken" met een voorbeeld van de twee bevestigingsmails (wireframe 37).
     */
    public function show(Reservation $reservation): Response
    {
        $reservation->load([
            'customer',
            'package',
            'location',
            'lessons' => fn ($query) => $query->orderBy('lesson_date'),
            'lessons.instructor',
        ]);

        // De instructeur is degene die de lessen geeft. Die krijgt ook een bevestiging.
        $instructor = $reservation->lessons->first()?->instructor;

        return Inertia::render('eigenaar/definitief', [
            'reservation' => [
                'id' => $reservation->id,
                'number' => $reservation->number,
                'status' => $reservation->status->value,
                'payment_reported' => $reservation->payment_reported,
                'total_cents' => $reservation->total_cents,
                'package' => [
                    'name' => $reservation->package->name,
                    'hours' => (float) $reservation->package->hours,
                ],
                'location' => ['name' => $reservation->location->name],
                'customer' => [
                    'name' => $reservation->customer->name,
                    'email' => $reservation->customer->email,
                ],
                'instructor' => $instructor === null ? null : [
                    'name' => $instructor->name,
                    'email' => $instructor->email,
                ],
                'lessons' => $reservation->lessons->map(fn ($lesson): array => [
                    'id' => $lesson->id,
                    'date' => $lesson->lesson_date->format('Y-m-d'),
                    'time' => substr($lesson->start_time, 0, 5),
                    'status' => $lesson->status,
                ])->values(),
            ],
        ]);
    }

    /**
     * Zet de status van voorlopig naar definitief. De eigenaar doet dit nadat hij heeft gezien
     * dat de betaling binnen is (casus 3j). De e-mails worden in het prototype niet echt verstuurd.
     */
    public function confirm(Reservation $reservation): RedirectResponse
    {
        // Is de reservering al definitief? Dan hoeft er niets te gebeuren.
        if ($reservation->status === ReservationStatus::Definitief) {
            Inertia::flash('toast', ['type' => 'info', 'message' => "Reservering {$reservation->number} was al definitief."]);

            return redirect()->route('eigenaar.reservations.show', $reservation);
        }

        $reservation->update(['status' => ReservationStatus::Definitief]);

        Inertia::flash('toast', ['type' => 'success', 'message' => "Reservering {$reservation->number} is definitief."]);

        return redirect()->route('eigenaar.reservations.show', $reservation);
    }
}
