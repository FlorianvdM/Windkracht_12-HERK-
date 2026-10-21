import { Head, Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatEuro } from '@/lib/format';

// Eén rij in het overzicht. Dit zijn precies de velden die
// Eigenaar\ReservationController@index naar de pagina stuurt.
type Reservering = {
    id: number;
    number: string;
    status: string;
    payment_reported: boolean;
    total_cents: number;
    lessons_count: number;
    customer: { name: string; email: string };
    package: { name: string };
    location: { name: string };
};

// Overzicht van alle reserveringen voor de eigenaar (casus 3i).
// Met de filterknop "Nog niet betaald" zie je alleen de voorlopige reserveringen.
export default function Reserveringen({
    filter,
    counts,
    reservations,
}: {
    filter: 'alle' | 'onbetaald';
    counts: { alle: number; onbetaald: number };
    reservations: Reservering[];
}) {
    return (
        <>
            <Head title="Reserveringen" />

            <div className="mx-auto w-full max-w-6xl space-y-6 p-4">
                <header>
                    <h1 className="text-2xl font-semibold">Reserveringen</h1>
                    <p className="text-sm text-muted-foreground">
                        Een voorlopige reservering is nog niet gecontroleerd op
                        betaling. Maak hem definitief zodra de betaling binnen
                        is.
                    </p>
                </header>

                {/* Filterknoppen: de actieve knop is gevuld, de andere heeft alleen een rand.
                    Elke knop is een gewone link met ?filter=... in het webadres. */}
                <div className="flex gap-2">
                    <Button
                        asChild
                        variant={filter === 'alle' ? 'default' : 'outline'}
                    >
                        <Link href="/eigenaar/reserveringen">
                            Alle ({counts.alle})
                        </Link>
                    </Button>
                    <Button
                        asChild
                        variant={filter === 'onbetaald' ? 'default' : 'outline'}
                    >
                        <Link href="/eigenaar/reserveringen?filter=onbetaald">
                            Nog niet betaald ({counts.onbetaald})
                        </Link>
                    </Button>
                </div>

                {/* Lege lijst: een korte uitleg in plaats van een lege tabel */}
                {reservations.length === 0 && (
                    <div className="rounded-lg border p-6 text-sm text-muted-foreground">
                        {filter === 'onbetaald'
                            ? 'Alle reserveringen zijn betaald en definitief.'
                            : 'Er zijn nog geen reserveringen.'}
                    </div>
                )}

                {reservations.length > 0 && (
                    // overflow-x-auto: op een smal scherm scrolt de tabel zijwaarts
                    <div className="overflow-x-auto rounded-lg border">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b bg-muted/50 text-muted-foreground">
                                <tr>
                                    <th className="p-3 font-medium">Nummer</th>
                                    <th className="p-3 font-medium">Klant</th>
                                    <th className="p-3 font-medium">Pakket</th>
                                    <th className="p-3 font-medium">Locatie</th>
                                    <th className="p-3 font-medium">Bedrag</th>
                                    <th className="p-3 font-medium">Status</th>
                                    <th className="p-3 font-medium"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {reservations.map((reservering) => {
                                    const isDefinitief =
                                        reservering.status === 'definitief';

                                    return (
                                        <tr key={reservering.id}>
                                            <td className="p-3 font-medium">
                                                {reservering.number}
                                            </td>
                                            <td className="p-3">
                                                {reservering.customer.name}
                                                <div className="text-muted-foreground">
                                                    {reservering.customer.email}
                                                </div>
                                            </td>
                                            <td className="p-3">
                                                {reservering.package.name}
                                                <div className="text-muted-foreground">
                                                    {reservering.lessons_count}{' '}
                                                    {reservering.lessons_count === 1
                                                        ? 'lesdag'
                                                        : 'lesdagen'}
                                                </div>
                                            </td>
                                            <td className="p-3">
                                                {reservering.location.name}
                                            </td>
                                            <td className="p-3">
                                                {formatEuro(reservering.total_cents)}
                                            </td>
                                            <td className="p-3">
                                                <div className="flex flex-wrap gap-2">
                                                    {/* Definitief is de belangrijkste status: die krijgt de opvallende kleur */}
                                                    <Badge
                                                        variant={
                                                            isDefinitief
                                                                ? 'default'
                                                                : 'secondary'
                                                        }
                                                    >
                                                        {isDefinitief
                                                            ? 'Definitief'
                                                            : 'Voorlopig'}
                                                    </Badge>
                                                    {/* De klant heeft aangegeven dat hij heeft betaald */}
                                                    {reservering.payment_reported && (
                                                        <Badge variant="outline">
                                                            Betaling gemeld
                                                        </Badge>
                                                    )}
                                                </div>
                                            </td>
                                            <td className="p-3 text-right">
                                                {/* Naar het scherm "Definitief maken" (route eigenaar.reservations.show) */}
                                                <Button
                                                    asChild
                                                    size="sm"
                                                    variant={
                                                        isDefinitief
                                                            ? 'outline'
                                                            : 'default'
                                                    }
                                                >
                                                    <Link
                                                        href={`/eigenaar/reserveringen/${reservering.id}`}
                                                    >
                                                        {isDefinitief
                                                            ? 'Bekijken'
                                                            : 'Definitief maken'}
                                                    </Link>
                                                </Button>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </>
    );
}

Reserveringen.layout = {
    breadcrumbs: [
        { title: 'Eigenaar', href: '/eigenaar' },
        { title: 'Reserveringen', href: '/eigenaar/reserveringen' },
    ],
};