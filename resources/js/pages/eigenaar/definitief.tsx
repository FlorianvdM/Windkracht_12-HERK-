import { Head, useForm } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatDatum, formatEuro, formatUren } from '@/lib/format';
import type { OwnerReservation } from '@/types/reservation';

// Eén voorbeeldmail: eerst van/aan/onderwerp, daaronder de tekst.
function Mail({
    titel,
    aan,
    onderwerp,
    tekst,
}: {
    titel: string;
    aan: string;
    onderwerp: string;
    tekst: string;
}) {
    return (
        <div className="space-y-2">
            <p className="text-sm text-muted-foreground">{titel}</p>
            <div className="divide-y rounded-lg border text-sm">
                <div className="space-y-1 p-4">
                    <p>
                        <span className="text-muted-foreground">Van </span>
                        reserveren@kitesurfschool-windkracht12.nl
                    </p>
                    <p>
                        <span className="text-muted-foreground">Aan </span>
                        {aan}
                    </p>
                    <p>
                        <span className="text-muted-foreground">Onderwerp </span>
                        <strong>{onderwerp}</strong>
                    </p>
                </div>
                <div className="p-4 whitespace-pre-line">{tekst}</div>
            </div>
        </div>
    );
}

// De pagina "Definitief maken" (wireframe 37).
// De eigenaar controleert eerst of de betaling binnen is en maakt de reservering daarna definitief.
export default function Definitief({
    reservation,
}: {
    reservation: OwnerReservation;
}) {
    const form = useForm({});
    const isDefinitief = reservation.status === 'definitief';

    // Stuurt de status naar Laravel (Eigenaar\ReservationController@confirm).
    function maakDefinitief() {
        form.post(`/eigenaar/reserveringen/${reservation.id}/definitief`);
    }

    // Tekst van de bevestiging. De klant en de instructeur krijgen dezelfde inhoud.
    function mailTekst(naam: string): string {
        return [
            `Beste ${naam},`,
            '',
            `Reservering ${reservation.number} is definitief. De kitesurflessen gaan door.`,
            '',
            `Pakket: ${reservation.package.name} (${formatUren(reservation.package.hours)} uur)`,
            `Locatie: ${reservation.location.name}`,
            ...(reservation.instructor
                ? [`Instructeur: ${reservation.instructor.name}`]
                : []),
            'Lesdagen:',
            ...reservation.lessons.map(
                (les) => `- ${formatDatum(les.date)}, ${les.time}`,
            ),
            '',
            'Tot op het strand.',
            'Kitesurfschool Windkracht-12',
        ].join('\n');
    }

    const onderwerp = `Bevestiging ${reservation.number}`;

    return (
        <>
            <Head title={`Definitief maken ${reservation.number}`} />

            <div className="mx-auto w-full max-w-5xl space-y-6 p-4">
                <header>
                    <h1 className="text-2xl font-semibold">Definitief maken</h1>
                    <p className="text-sm text-muted-foreground">
                        Pas na controle van de betaling. De status gaat van
                        voorlopig naar definitief en er gaat een bevestiging naar
                        klant en instructeur.
                    </p>
                </header>

                {/* De reservering en de knop */}
                <div className="space-y-3 rounded-lg border p-4 text-sm">
                    <div className="flex items-start justify-between gap-2">
                        <div>
                            <h2 className="text-base font-medium">
                                {reservation.number} · {reservation.customer.name}
                            </h2>
                            <p className="text-muted-foreground">
                                {reservation.package.name} ·{' '}
                                {reservation.location.name} ·{' '}
                                {formatEuro(reservation.total_cents)} ·{' '}
                                {reservation.payment_reported
                                    ? 'betaling gemeld'
                                    : 'betaling niet gemeld'}
                            </p>
                        </div>
                        <Badge variant={isDefinitief ? 'default' : 'secondary'}>
                            {isDefinitief ? 'Definitief' : 'Voorlopig'}
                        </Badge>
                    </div>

                    {isDefinitief ? (
                        <p className="text-muted-foreground">
                            Deze reservering is definitief. De bevestiging
                            hieronder is naar de klant en de instructeur gegaan.
                        </p>
                    ) : (
                        <Button
                            disabled={form.processing}
                            onClick={maakDefinitief}
                        >
                            Maak definitief
                        </Button>
                    )}
                </div>

                {/* De twee voorbeeldmails */}
                <div className="grid gap-4 lg:grid-cols-2">
                    <Mail
                        titel="Naar de klant"
                        aan={reservation.customer.email}
                        onderwerp={onderwerp}
                        tekst={mailTekst(reservation.customer.name)}
                    />
                    {reservation.instructor && (
                        <Mail
                            titel="Naar de instructeur"
                            aan={reservation.instructor.email}
                            onderwerp={onderwerp}
                            tekst={mailTekst(reservation.instructor.name)}
                        />
                    )}
                </div>

                <p className="text-sm text-muted-foreground">
                    In het prototype worden deze e-mails niet echt verstuurd. Ze
                    worden als voorbeeld getoond.
                </p>

            </div>
        </>
    );
}

Definitief.layout = {
    breadcrumbs: [
        { title: 'Eigenaar', href: '/eigenaar' },
        { title: 'Definitief maken', href: '#' },
    ],
};
