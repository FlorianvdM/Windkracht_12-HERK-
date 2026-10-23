<?php

namespace App\Http\Controllers\Klant;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Klant\StoreReservationRequest;
use App\Models\Lesson;
use App\Models\Location;
use App\Models\Package;
use App\Models\Reservation;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Reserveren door de klant: een reservering maken en de eigen reserveringen bekijken.
 */
class ReservationController extends Controller
{
    /**
     * Het reserveringsformulier (pakket, locatie en lesdagen kiezen).
     */
    public function create(): Response
    {
        $locations = Location::query()
            ->with('instructor:id,name')
            ->whereNotNull('instructor_id')   // zonder instructeur kan er geen les worden gegeven
            ->orderBy('name')
            ->get();

        return Inertia::render('klant/reserveren', [
            'packages' => Package::query()->orderBy('price_cents')->get()->map(fn (Package $package): array => [
                'id' => $package->id,
                'name' => $package->name,
                'price_cents' => $package->price_cents,
                'hours' => (float) $package->hours,
                'lesson_days' => $package->lesson_days,
                'max_persons' => $package->max_persons,
            ]),
            'locations' => $locations->map(fn (Location $location): array => [
                'id' => $location->id,
                'name' => $location->name,
                'instructor' => $location->instructor->name,
                // Dagen waarop de instructeur van deze locatie al een les heeft: die zijn bezet.
                // (Ook Wijk aan Zee en Zandvoort delen bijvoorbeeld dezelfde instructeur.)
                'booked_dates' => Lesson::query()
                    ->where('instructor_id', $location->instructor_id)
                    ->whereDate('lesson_date', '>=', today())
                    ->get()
                    ->map(fn (Lesson $lesson): string => $lesson->lesson_date->format('Y-m-d'))
                    ->values(),
            ]),
        ]);
    }

    /**
     * Slaat de reservering op met de status 'voorlopig'.
     */
    public function store(StoreReservationRequest $request): RedirectResponse
    {
        // Alleen gevalideerde velden. Status, totaalbedrag en klant bepaalt de server zelf,
        // zodat niemand die via het formulier kan aanpassen.
        $data = $request->validated();

        $package = Package::findOrFail($data['package_id']);
        $location = Location::findOrFail($data['location_id']);

        try {
            // Een transactie: of alles wordt opgeslagen (reservering én lessen), of niets.
            $reservation = DB::transaction(function () use ($request, $package, $location, $data): Reservation {
                $reservation = Reservation::create([
                    'number' => Reservation::nextNumber(),
                    'user_id' => $request->user()->id,
                    'package_id' => $package->id,
                    'location_id' => $location->id,
                    'status' => ReservationStatus::Voorlopig,
                    'payment_reported' => false,
                    // De aanmelder betaalt het totaal, ook als het pakket per persoon is geprijsd (casus 2d).
                    'total_cents' => $package->price_cents * $package->max_persons,
                    // NIEUW: de duo-lesser alleen opslaan bij een duo-pakket. Bij een privéles blijft het leeg.
                    'duo_name' => $package->max_persons > 1 ? ($data['duo_name'] ?? null) : null,
                    'duo_address' => $package->max_persons > 1 ? ($data['duo_address'] ?? null) : null,
                    'duo_city' => $package->max_persons > 1 ? ($data['duo_city'] ?? null) : null,
                ]);

                foreach ($data['lesson_dates'] as $date) {
                    $reservation->lessons()->create([
                        'instructor_id' => $location->instructor_id,
                        'lesson_date' => $date,
                        'status' => 'gepland',
                    ]);
                }

                return $reservation;
            });
        } catch (UniqueConstraintViolationException) {
            // Iemand anders was net sneller met dezelfde instructeur op dezelfde dag.
            return back()->withErrors([
                'lesson_dates' => 'Een van de gekozen dagen is zojuist bezet geraakt. Kies een andere dag.',
            ]);
        }

        return redirect()->route('klant.reservations.show', $reservation);
    }

    /**
     * Overzicht van de eigen reserveringen ("Mijn pakketten").
     */
    public function index(Request $request): Response
    {
        $reservations = $request->user()->reservations()
            ->with($this->relations())
            ->latest('id')
            ->get();

        return Inertia::render('klant/reserveringen', [
            'reservations' => $reservations->map(fn (Reservation $reservation): array => $this->data($reservation)),
        ]);
    }

    /**
     * Bevestiging na het plaatsen (de "rekening"). Alleen de eigenaar van de reservering mag hem zien.
     */
    public function show(Request $request, Reservation $reservation): Response
    {
        // 404 in plaats van 403: een andere klant mag niet eens weten dat dit nummer bestaat.
        abort_unless($reservation->user_id === $request->user()->id, 404);

        return Inertia::render('klant/reservering', [
            'reservation' => $this->data($reservation->load($this->relations())),
        ]);
    }

    /**
     * De klant meldt dat hij het openstaande bedrag heeft betaald (casus 2g).
     * Dit maakt de reservering nog NIET definitief: dat doet alleen de eigenaar na controle van de betaling.
     */
    public function reportPayment(Request $request, Reservation $reservation): RedirectResponse
    {
        // Net als bij show(): een reservering van een andere klant bestaat voor jou niet (404).
        abort_unless($reservation->user_id === $request->user()->id, 404);

        // Melden heeft alleen zin zolang de reservering voorlopig is.
        if ($reservation->status === ReservationStatus::Voorlopig) {
            $reservation->update(['payment_reported' => true]);
        }

        return back();
    }

    /**
     * De gegevens die we ophalen bij een reservering, zodat we niet per les opnieuw de database in hoeven.
     *
     * @return array<string, mixed>
     */
    private function relations(): array
    {
        return [
            'package',
            'location.instructor',
            'customer',
            'lessons' => fn ($query) => $query->orderBy('lesson_date'),
            'lessons.instructor',
        ];
    }

    /**
     * Zet een reservering om naar een eenvoudige lijst die de React-pagina kan gebruiken.
     *
     * @return array<string, mixed>
     */
    private function data(Reservation $reservation): array
    {
        return [
            'id' => $reservation->id,
            'number' => $reservation->number,
            'status' => $reservation->status->value,
            'payment_reported' => $reservation->payment_reported,
            'total_cents' => $reservation->total_cents,
            'per_person_cents' => $reservation->package->price_cents,
            'package' => [
                'name' => $reservation->package->name,
                'hours' => (float) $reservation->package->hours,
                'max_persons' => $reservation->package->max_persons,
            ],
            'location' => [
                'name' => $reservation->location->name,
                'instructor' => $reservation->location->instructor?->name,
            ],
            'customer' => [
                'name' => $reservation->customer->name,
                'email' => $reservation->customer->email,
            ],
            // NIEUW: de duo-lesser, of null als er geen tweede persoon is (privéles).
            'duo' => $reservation->duo_name === null ? null : [
                'name' => $reservation->duo_name,
                'address' => $reservation->duo_address,
                'city' => $reservation->duo_city,
            ],
            'lessons' => $reservation->lessons->map(fn (Lesson $lesson): array => [
                'id' => $lesson->id,
                'date' => $lesson->lesson_date->format('Y-m-d'),
                'time' => substr($lesson->start_time, 0, 5),
                'status' => $lesson->status,
                'instructor' => $lesson->instructor->name,
            ])->values(),
        ];
    }
}