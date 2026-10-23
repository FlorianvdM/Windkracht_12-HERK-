import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatDatum, formatEuro, lesStatusTekst } from '@/lib/format';
import type { LessonRow, ReservationData } from '@/types/reservation';

// De klant meldt dat hij het bedrag heeft overgemaakt (casus 2g).
// We sturen alleen het verzoek naar Laravel; de server zet 'payment_reported' op true.
const meldBetaling = (id: number) => {
    router.post(
        `/klant/reserveringen/${id}/betaald`,
        {},
        { preserveScroll: true },
    );
};

// Morgen als 'jjjj-mm-dd'. Eerder kun je geen nieuwe lesdatum kiezen.
function morgen(): string {
    const d = new Date();
    d.setDate(d.getDate() + 1);

    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

// NIEUW: één lesdag met de knoppen om te annuleren of een nieuwe datum te kiezen (casus 2e).
// Elke les heeft een eigen klein formulier, zodat de foutmeldingen bij de juiste les staan.
function LesRegel({ les }: { les: LessonRow }) {
    const [annuleerOpen, setAnnuleerOpen] = useState(false);
    const annuleerForm = useForm({ reden: '' });
    const verplaatsForm = useForm({ datum: '' });

    // Alleen een geplande les in de toekomst kun je annuleren.
    const kanAnnuleren = les.status === 'gepland' && les.date >= morgen();

    function annuleer() {
        annuleerForm.post(`/klant/lessen/${les.id}/annuleren`, {
            preserveScroll: true,
            onSuccess: () => setAnnuleerOpen(false),
        });
    }

    function verplaats() {
        verplaatsForm.post(`/klant/lessen/${les.id}/verplaatsen`, {
            preserveScroll: true,
        });
    }

    return (
        <li className="space-y-2 py-2">
            <div className="flex flex-wrap items-center justify-between gap-2">
                <span>
                    {formatDatum(les.date)} · {les.time} · {les.instructor}
                </span>
                <div className="flex items-center gap-2">
                    <span className="text-muted-foreground">
                        {lesStatusTekst(les.status)}
                    </span>
                    {kanAnnuleren && !annuleerOpen && (
                        <Button
                            size="sm"
                            variant="outline"
                            onClick={() => setAnnuleerOpen(true)}
                        >
                            Annuleren
                        </Button>
                    )}
                </div>
            </div>

            {/* Formulier: annuleren met reden. De reden is verplicht. */}
            {annuleerOpen && (
                <div className="space-y-2 rounded-md bg-muted/50 p-3">
                    <label htmlFor={`reden-${les.id}`} className="font-medium">
                        Waarom wil je deze les annuleren?
                    </label>
                    <textarea
                        id={`reden-${les.id}`}
                        className="w-full rounded-md border bg-background p-2"
                        rows={2}
                        value={annuleerForm.data.reden}
                        onChange={(e) =>
                            annuleerForm.setData('reden', e.target.value)
                        }
                    />
                    <InputError message={annuleerForm.errors.reden} />
                    <div className="flex gap-2">
                        <Button
                            size="sm"
                            disabled={annuleerForm.processing}
                            onClick={annuleer}
                        >
                            Annulering aanvragen
                        </Button>
                        <Button
                            size="sm"
                            variant="outline"
                            onClick={() => setAnnuleerOpen(false)}
                        >
                            Toch niet
                        </Button>
                    </div>
                </div>
            )}

            {/* Formulier: nieuwe datum kiezen, na goedkeuring of na een annulering door de school. */}
            {les.status === 'nieuwe_datum_kiezen' && (
                <div className="space-y-2 rounded-md bg-muted/50 p-3">
                    <p className="font-medium">Kies een nieuwe lesdatum</p>
                    <div className="flex flex-wrap gap-2">
                        <Input
                            type="date"
                            className="w-auto"
                            min={morgen()}
                            value={verplaatsForm.data.datum}
                            onChange={(e) =>
                                verplaatsForm.setData('datum', e.target.value)
                            }
                        />
                        <Button
                            size="sm"
                            disabled={
                                verplaatsForm.processing ||
                                verplaatsForm.data.datum === ''
                            }
                            onClick={verplaats}
                        >
                            Nieuwe datum opslaan
                        </Button>
                    </div>
                    <InputError message={verplaatsForm.errors.datum} />
                </div>
            )}
        </li>
    );
}

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
                        voorlopig of definitief is. Een les annuleren kan
                        alleen met een reden.
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
                                {reservering.duo && (
                                    <p className="text-muted-foreground">
                                        Duo-lesser: {reservering.duo.name}
                                    </p>
                                )}
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
                                <LesRegel key={les.id} les={les} />
                            ))}
                        </ul>

                        {/* De knop verschijnt alleen als de reservering voorlopig is
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