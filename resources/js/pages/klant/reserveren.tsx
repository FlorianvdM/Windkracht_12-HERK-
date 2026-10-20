import { Head, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import type { ReactNode } from 'react';
import AlertError from '@/components/alert-error';
import LesdagenKalender from '@/components/lesdagen-kalender';
import { Button } from '@/components/ui/button';
import { formatDatum, formatEuro, formatUren } from '@/lib/format';
import { cn } from '@/lib/utils';
import type { LocationOption, PackageOption } from '@/types/reservation';

// De stappen van het reserveren (wireframes 8 t/m 12).
// De stap "Duo" uit het ontwerp is een Could have en zit nog niet in het prototype.
const STAPPEN = ['Pakket', 'Locatie', 'Lesdagen', 'Controle'];

// Een aanklikbare kaart, bijvoorbeeld voor een pakket of een locatie.
function Keuzekaart({
    gekozen,
    onKies,
    children,
}: {
    gekozen: boolean;
    onKies: () => void;
    children: ReactNode;
}) {
    return (
        <button
            type="button"
            aria-pressed={gekozen}
            onClick={onKies}
            className={cn(
                'w-full rounded-xl border p-4 text-left transition-colors hover:bg-accent',
                gekozen && 'border-primary bg-accent ring-2 ring-primary/30',
            )}
        >
            {children}
        </button>
    );
}

export default function Reserveren({
    packages,
    locations,
}: {
    packages: PackageOption[];
    locations: LocationOption[];
}) {
    const { auth } = usePage().props;

    // Op welke stap zit de klant? We bewaren alle keuzes in het formulier.
    // Pas bij de laatste stap sturen we alles in één keer naar Laravel.
    const [stap, setStap] = useState(1);
    const form = useForm<{
        package_id: number | null;
        location_id: number | null;
        lesson_dates: string[];
    }>({
        package_id: null,
        location_id: null,
        lesson_dates: [],
    });

    // Zoek de volledige gegevens op van wat de klant heeft gekozen.
    const pakket = packages.find((p) => p.id === form.data.package_id);
    const locatie = locations.find((l) => l.id === form.data.location_id);

    // Een ander pakket of een andere locatie? Dan wissen we de lesdagen,
    // want het aantal dagen en de bezette dagen kunnen anders zijn.
    function kiesPakket(id: number) {
        form.setData({ ...form.data, package_id: id, lesson_dates: [] });
    }
    function kiesLocatie(id: number) {
        form.setData({ ...form.data, location_id: id, lesson_dates: [] });
    }

    // Verstuur de reservering naar Laravel (ReservationController@store).
    function plaatsReservering() {
        form.post('/klant/reserveren');
    }

    const alleFouten = Object.values(form.errors);

    return (
        <>
            <Head title="Reserveren" />

            <div className="mx-auto w-full max-w-4xl space-y-6 p-4">
                {/* Stappenbalk */}
                <ol className="flex flex-wrap gap-2 text-sm">
                    {STAPPEN.map((naam, index) => (
                        <li
                            key={naam}
                            aria-current={stap === index + 1 ? 'step' : undefined}
                            className={cn(
                                'rounded-md border px-3 py-1.5',
                                stap === index + 1
                                    ? 'border-primary bg-primary text-primary-foreground'
                                    : 'text-muted-foreground',
                            )}
                        >
                            {index + 1}. {naam}
                        </li>
                    ))}
                </ol>

                {/* Stap 1: pakket */}
                {stap === 1 && (
                    <section className="space-y-4">
                        <header>
                            <p className="text-sm text-muted-foreground">Stap 1</p>
                            <h1 className="text-2xl font-semibold">Kies een pakket</h1>
                            <p className="text-sm text-muted-foreground">
                                Het aantal lesdagen volgt uit het pakket. Privé is
                                één persoon, de andere pakketten maximaal twee.
                            </p>
                        </header>

                        <div className="grid gap-3 md:grid-cols-2">
                            {packages.map((p) => (
                                <Keuzekaart
                                    key={p.id}
                                    gekozen={form.data.package_id === p.id}
                                    onKies={() => kiesPakket(p.id)}
                                >
                                    <div className="flex items-baseline justify-between gap-2">
                                        <span className="font-medium">{p.name}</span>
                                        <span className="text-lg font-semibold">
                                            {formatEuro(p.price_cents)}
                                        </span>
                                    </div>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {formatUren(p.hours)} uur · {p.lesson_days}{' '}
                                        {p.lesson_days === 1 ? 'dagdeel' : 'dagdelen'} ·{' '}
                                        {p.max_persons === 1
                                            ? 'één persoon'
                                            : 'per persoon'}
                                    </p>
                                </Keuzekaart>
                            ))}
                        </div>

                        <Button disabled={!pakket} onClick={() => setStap(2)}>
                            Verder naar locatie
                        </Button>
                    </section>
                )}

                {/* Stap 2: locatie */}
                {stap === 2 && (
                    <section className="space-y-4">
                        <header>
                            <p className="text-sm text-muted-foreground">Stap 2</p>
                            <h1 className="text-2xl font-semibold">Kies een locatie</h1>
                            <p className="text-sm text-muted-foreground">
                                De locatie bepaalt welke instructeur de les geeft.
                            </p>
                        </header>

                        <div className="grid gap-3 md:grid-cols-2">
                            {locations.map((l) => (
                                <Keuzekaart
                                    key={l.id}
                                    gekozen={form.data.location_id === l.id}
                                    onKies={() => kiesLocatie(l.id)}
                                >
                                    <span className="font-medium">{l.name}</span>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        Instructeur: {l.instructor}
                                    </p>
                                </Keuzekaart>
                            ))}
                        </div>

                        <div className="flex gap-2">
                            <Button variant="outline" onClick={() => setStap(1)}>
                                Terug
                            </Button>
                            <Button disabled={!locatie} onClick={() => setStap(3)}>
                                Verder naar lesdagen
                            </Button>
                        </div>
                    </section>
                )}

                {/* Stap 3: lesdagen */}
                {stap === 3 && pakket && locatie && (
                    <section className="space-y-4">
                        <header>
                            <p className="text-sm text-muted-foreground">Stap 3</p>
                            <h1 className="text-2xl font-semibold">Kies de lesdagen</h1>
                            <p className="text-sm text-muted-foreground">
                                {pakket.name} heeft {pakket.lesson_days}{' '}
                                {pakket.lesson_days === 1 ? 'dagdeel' : 'dagdelen'}.{' '}
                                {form.data.lesson_dates.length} van{' '}
                                {pakket.lesson_days} gekozen. Een instructeur geeft
                                één les per dag.
                            </p>
                        </header>

                        <LesdagenKalender
                            bezet={locatie.booked_dates}
                            gekozen={form.data.lesson_dates}
                            maxAantal={pakket.lesson_days}
                            onWijzig={(dagen) =>
                                form.setData({ ...form.data, lesson_dates: dagen })
                            }
                        />

                        <div className="flex gap-2">
                            <Button variant="outline" onClick={() => setStap(2)}>
                                Terug
                            </Button>
                            <Button
                                disabled={
                                    form.data.lesson_dates.length !==
                                    pakket.lesson_days
                                }
                                onClick={() => setStap(4)}
                            >
                                Verder naar controle
                            </Button>
                        </div>
                    </section>
                )}

                {/* Stap 4: controle en plaatsen */}
                {stap === 4 && pakket && locatie && (
                    <section className="space-y-4">
                        <header>
                            <p className="text-sm text-muted-foreground">Stap 4</p>
                            <h1 className="text-2xl font-semibold">Controle</h1>
                            <p className="text-sm text-muted-foreground">
                                De aanmelder betaalt het totaal, ook als het pakket
                                per persoon is geprijsd.
                            </p>
                        </header>

                        <dl className="divide-y rounded-lg border text-sm">
                            <Regel naam="Aanmelder">{auth.user.name}</Regel>
                            <Regel naam="Pakket">
                                {pakket.name} · {formatUren(pakket.hours)} uur
                            </Regel>
                            <Regel naam="Locatie">
                                {locatie.name} · {locatie.instructor}
                            </Regel>
                            <Regel naam="Lesdagen">
                                {form.data.lesson_dates.map((dag) => (
                                    <div key={dag}>{formatDatum(dag)}</div>
                                ))}
                            </Regel>
                            <Regel naam="Per persoon">
                                {formatEuro(pakket.price_cents)}
                            </Regel>
                            <Regel naam="Totaal op de rekening">
                                <strong>
                                    {formatEuro(
                                        pakket.price_cents * pakket.max_persons,
                                    )}
                                </strong>
                            </Regel>
                        </dl>

                        {pakket.max_persons > 1 && (
                            <p className="text-sm text-muted-foreground">
                                De gegevens van je duo-lesser vul je in een latere
                                versie in. In dit prototype staan ze er nog niet bij.
                            </p>
                        )}

                        {alleFouten.length > 0 && (
                            <AlertError
                                title="De reservering is niet geplaatst."
                                errors={alleFouten}
                            />
                        )}

                        <div className="flex gap-2">
                            <Button
                                variant="outline"
                                disabled={form.processing}
                                onClick={() => setStap(3)}
                            >
                                Terug
                            </Button>
                            <Button
                                disabled={form.processing}
                                onClick={plaatsReservering}
                            >
                                Reservering plaatsen
                            </Button>
                        </div>
                    </section>
                )}
            </div>
        </>
    );
}

// Eén regel in de controletabel: links de naam, rechts de waarde.
function Regel({ naam, children }: { naam: string; children: ReactNode }) {
    return (
        <div className="grid gap-1 p-3 sm:grid-cols-[12rem_1fr]">
            <dt className="text-muted-foreground">{naam}</dt>
            <dd>{children}</dd>
        </div>
    );
}

Reserveren.layout = {
    breadcrumbs: [
        { title: 'Klant', href: '/klant' },
        { title: 'Reserveren', href: '/klant/reserveren' },
    ],
};
