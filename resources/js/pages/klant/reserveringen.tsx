import { Head, Link, router } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatDatum, formatEuro } from '@/lib/format';
import type { ReservationData } from '@/types/reservation';

// De status van een les als leesbare tekst: 'gepland' wordt 'Gepland'.
const lesStatus = (status: string) =>
    status.charAt(0).toUpperCase() + status.slice(1);

// NIEUW: de klant meldt dat hij het bedrag heeft overgemaakt (casus 2g).
// We sturen alleen het verzoek naar Laravel; de server zet 'payment_reported' op true.
const meldBetaling = (id: number) => {
    router.post(
        `/klant/reserveringen/${id}/betaald`,
        {},
        { preserveScroll: true },
    );
};

// Overzicht van de eigen reserveringen (wireframe 14).
export default function Reserveringen({
    reservations,
}: {
    reservations: ReservationData[];
}) {
    return (
        <>
            <Head title="Mijn reserveringen" />

            <div className="mx-auto w-full max-w-4xl space-y-6 p-4">
                <header>
                    <h1 className="text-2xl font-semibold">Mijn pakketten</h1>
                    <p className="text-sm text-muted-foreground">
                        Elke reservering toont de lesdagen en of de status
                        voorlopig of definitief is.
                    </p>
                </header>

                {reservations.length === 0 && (
                    <div className="space-y-3 rounded-lg border p-6 text-sm">
                        <p>Je hebt nog geen reserveringen.</p>
                        <Button asChild>
                            <Link href="/klant/reserveren">Reserveren</Link>
                        </Button>
                    </div>
                )}

                {reservations.map((reservering) => (
                    <article
                        key={reservering.id}
                        className="rounded-lg border p-4 text-sm"
                    >
                        <div className="flex items-start justify-between gap-2">
                            <div>
                                <h2 className="text-base font-medium">
                                    {reservering.package.name}
                                </h2>
                                <p className="text-muted-foreground">
                                    {reservering.number} ·{' '}
                                    {reservering.location.name} ·{' '}
                                    {formatEuro(reservering.total_cents)}
                                </p>
                            </div>
                            <div className="flex gap-2">
                                {reservering.payment_reported && (
                                    <Badge variant="outline">Betaling gemeld</Badge>
                                )}
                                {/* Definitief is de belangrijkste status: die krijgt de opvallende kleur. */}
                                <Badge
                                    variant={
                                        reservering.status === 'definitief'
                                            ? 'default'
                                            : 'secondary'
                                    }
                                >
                                    {reservering.status === 'definitief'
                                        ? 'Definitief'
                                        : 'Voorlopig'}
                                </Badge>
                            </div>
                        </div>

                        <ul className="mt-3 divide-y border-t">
                            {reservering.lessons.map((les) => (
                                <li
                                    key={les.id}
                                    className="flex items-center justify-between gap-2 py-2"
                                >
                                    <span>
                                        {formatDatum(les.date)} · {les.time} ·{' '}
                                        {les.instructor}
                                    </span>
                                    <span className="text-muted-foreground">
                                        {lesStatus(les.status)}
                                    </span>
                                </li>
                            ))}
                        </ul>

                        {/* NIEUW: de knop verschijnt alleen als de reservering voorlopig is
                            en de klant nog niet heeft gemeld dat hij betaald heeft. */}
                        {reservering.status === 'voorlopig' &&
                            !reservering.payment_reported && (
                                <div className="mt-3 border-t pt-3">
                                    <Button
                                        size="sm"
                                        onClick={() => meldBetaling(reservering.id)}
                                    >
                                        Betaling melden
                                    </Button>
                                </div>
                            )}
                    </article>
                ))}
            </div>
        </>
    );
}

Reserveringen.layout = {
    breadcrumbs: [
        { title: 'Klant', href: '/klant' },
        { title: 'Mijn reserveringen', href: '/klant/reserveringen' },
    ],
};