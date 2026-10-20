// Gegevens die Laravel naar de reserveringspagina's stuurt.
// Moeten overeenkomen met ReservationController.php.

// Een lespakket dat de klant kan kiezen.
export type PackageOption = {
    id: number;
    name: string;
    price_cents: number; // prijs per persoon in eurocenten
    hours: number;
    lesson_days: number; // aantal dagdelen (lesdagen)
    max_persons: number;
};

// Een locatie met de instructeur die daar les geeft.
export type LocationOption = {
    id: number;
    name: string;
    instructor: string;
    booked_dates: string[]; // dagen waarop deze instructeur al een les heeft, bijvoorbeeld '2026-10-22'
};

export type LessonRow = {
    id: number;
    date: string; // '2026-10-22'
    time: string; // '10:00'
    status: string; // 'gepland' of 'geannuleerd'
    instructor: string;
};

export type ReservationData = {
    id: number;
    number: string; // bijvoorbeeld 'WK12-1007'
    status: 'voorlopig' | 'definitief';
    payment_reported: boolean;
    total_cents: number;
    per_person_cents: number;
    package: { name: string; hours: number; max_persons: number };
    location: { name: string; instructor: string | null };
    customer: { name: string; email: string };
    lessons: LessonRow[];
};

// Een reservering zoals de eigenaar die ziet bij "Definitief maken".
export type OwnerReservation = {
    id: number;
    number: string;
    status: 'voorlopig' | 'definitief';
    payment_reported: boolean;
    total_cents: number;
    package: { name: string; hours: number };
    location: { name: string };
    customer: { name: string; email: string };
    instructor: { name: string; email: string } | null;
    lessons: { id: number; date: string; time: string; status: string }[];
};
