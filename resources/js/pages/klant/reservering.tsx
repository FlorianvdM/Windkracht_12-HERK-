import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { formatDatum, formatEuro, formatUren } from '@/lib/format';
import type { ReservationData } from '@/types/reservation';

// Bevestiging na het plaatsen van een reservering (wireframe 13).
// Het prototype verstuurt geen echte e-mail (Won't have). We tonen hier hoe de mail eruit zou zien.
export default function Reservering({
    reservation,
}: {
    reservation: ReservationData;
}) {
    const { package: pakket, location: locatie, customer, lessons } = reservation;

    // De tekst van de voorbeeldmail. Elke regel komt op een nieuwe regel te staan.
    const mailTekst = [
        `Beste ${customer.name},`,
        '',
        'Bedankt voor je reservering bij Kitesurfschool Windkracht-12.',
        'De reservering is voorlopig en wordt definitief nadat de betaling binnen is.',
        '',
        `Pakket: ${pakket.name} (${formatUren(pakket.hours)} uur)`,
        `Locatie: ${locatie.name}`,
        'Lesdagen:',
        ...lessons.map((les) => `- ${formatDatum(les.date)}, ${les.time}`),
        '',
        `Te voldoen: ${formatEuro(reservation.total_cents)}`,
        `(${formatEuro(reservation.per_person_cents)} per persoon. De aanmelder ontvangt de totale rekening.)`,
        '',
        'Betaalgegevens (voorbeeld, geen echte incasso)',
        'Rekening: NL12 WKRF 0000 0012 12',
        'T.n.v.: Kitesurfschool Windkracht-12',
        `Omschrijving: ${reservation.number}`,
        '',
        'Na ontvangst zet de eigenaar de status op definitief. Je krijgt dan een bevestiging.',
    ].join('\n');

    return (
        <>
            <Head title={`Reservering ${reservation.number}`} />

            <div className="mx-auto w-full max-w-3xl space-y-6 p-4">
                <header>
                    <p className="text-sm text-muted-foreground">
                        Na het plaatsen
                    </p>
                    <h1 className="text-2xl font-semibold">Rekening verstuurd</h1>
                    <p className="text-sm text-muted-foreground">
                        De reservering blijft voorlopig tot de eigenaar de
                        betaling op definitief zet.
                    </p>
                </header>

                <div className="divide-y rounded-lg border text-sm">
                    <div className="space-y-1 p-4">
                        <p>
                            <span className="text-muted-foreground">Van </span>
                            reserveren@kitesurfschool-windkracht12.nl
                        </p>
                        <p>
                            <span className="text-muted-foreground">Aan </span>
                            {customer.email}
                        </p>
                        <p>
                            <span className="text-muted-foreground">
                                Onderwerp{' '}
                            </span>
                            <strong>Rekening reservering {reservation.number}</strong>
                        </p>
                    </div>
                    <div className="p-4 whitespace-pre-line">{mailTekst}</div>
                </div>

                <Button asChild>
                    <Link href="/klant/reserveringen">Naar mijn lessen</Link>
                </Button>
            </div>
        </>
    );
}

Reservering.layout = {
    breadcrumbs: [
        { title: 'Klant', href: '/klant' },
        { title: 'Mijn reserveringen', href: '/klant/reserveringen' },
    ],
};
