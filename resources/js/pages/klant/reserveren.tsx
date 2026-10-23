import { Head, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import type { ReactNode } from 'react';
import AlertError from '@/components/alert-error';
import InputError from '@/components/input-error';
import LesdagenKalender from '@/components/lesdagen-kalender';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatDatum, formatEuro, formatUren } from '@/lib/format';
import { cn } from '@/lib/utils';
import type { LocationOption, PackageOption } from '@/types/reservation';

// De stappen van het reserveren (wireframes 8 t/m 12).
// NIEUW: de stap "Duo" zit er nu in. Bij een privéles slaan we die stap over.
type Stap = 'pakket' | 'locatie' | 'lesdagen' | 'duo' | 'controle';

const ALLE_STAPPEN: { id: Stap; naam: string }[] = [
    { id: 'pakket', naam: 'Pakket' },
    { id: 'locatie', naam: 'Locatie' },
    { id: 'lesdagen', naam: 'Lesdagen' },
    { id: 'duo', naam: 'Duo' },
    { id: 'controle', naam: 'Controle' },
];

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
    const [stap, setStap] = useState<Stap>('pakket');
    const form = useForm<{
        package_id: number | null;
        location_id: number | null;
        lesson_dates: string[];
        duo_name: string;
        duo_address: string;
        duo_city: string;
    }>({
        package_id: null,
        location_id: null,
        lesson_dates: [],
        duo_name: '',
        duo_address: '',
        duo_city: '',
    });

    // Zoek de volledige gegevens op van wat de klant heeft gekozen.
    const pakket = packages.find((p) => p.id === form.data.package_id);
    const locatie = locations.find((l) => l.id === form.data.location_id);

    // Is het een duo-pakket? Dan is de stap "Duo" nodig.
    const isDuo = (pakket?.max_persons ?? 1) > 1;
    const stappen = ALLE_STAPPEN.filter((s) => s.id !== 'duo' || isDuo);

    // Alle drie de duo-velden moeten gevuld zijn voordat je verder mag.
    const duoIngevuld =
        form.data.duo_name.trim() !== '' &&
        form.data.duo_address.trim() !== '' &&
        form.data.duo_city.trim() !== '';

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
                {/* Stappenbalk: bij een privéles staat "Duo" er niet tussen. */}
                <ol className="flex flex-wrap gap-2 text-sm">
                    {stappen.map((s, index) => (
                        <li
                            key={s.id}
                            aria-current={stap === s.id ? 'step' : undefined}
                            className={cn(
                                'rounded-md border px-3 py-1.5',
                                stap === s.id
                                    ? 'border-primary bg-primary text-primary-foreground'
                                    : 'text-muted-foreground',
                            )}
                        >
                            {index + 1}. {s.naam}
                        </li>
                    ))}
                </ol>

                {/* Stap: pakket */}
                {stap === 'pakket' && (
                    <section className="space-y-4">
                        <header>
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

                        <Button disabled={!pakket} onClick={() => setStap('locatie')}>
                            Verder naar locatie
                        </Button>
                    </section>
                )}

                {/* Stap: locatie */}
                {stap === 'locatie' && (
                    <section className="space-y-4">
                        <header>
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
                            <Button variant="outline" onClick={() => setStap('pakket')}>
                                Terug
                            </Button>
                            <Button
                                disabled={!locatie}
                                onClick={() => setStap('lesdagen')}
                            >
                                Verder naar lesdagen
                            </Button>
                        </div>
                    </section>
                )}

                {/* Stap: lesdagen */}
                {stap === 'lesdagen' && pakket && locatie && (
                    <section className="space-y-4">
                        <header>
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
                            <Button variant="outline" onClick={() => setStap('locatie')}>
                                Terug
                            </Button>
                            {/* Bij een duo-pakket eerst naar de stap Duo, anders direct naar de controle. */}
                            <Button
                                disabled={
                                    form.data.lesson_dates.length !==
                                    pakket.lesson_days
                                }
                                onClick={() => setStap(isDuo ? 'duo' : 'controle')}
                            >
                                {isDuo ? 'Verder naar duo-lesser' : 'Verder naar controle'}
                            </Button>
                        </div>
                    </section>
                )}

                {/* NIEUW. Stap: gegevens van de duo-lesser (casus 2d) */}
                {stap === 'duo' && pakket && (
                    <section className="space-y-4">
                        <header>
                            <h1 className="text-2xl font-semibold">Je duo-lesser</h1>
                            <p className="text-sm text-muted-foreground">
                                Vul de naam, het adres en de woonplaats in van de
                                persoon die samen met jou les krijgt. Jij ontvangt
                                als aanmelder de totale rekening.
                            </p>
                        </header>

                        <div className="grid max-w-md gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="duo_name">Naam</Label>
                                <Input
                                    id="duo_name"
                                    value={form.data.duo_name}
                                    onChange={(e) => form.setData('duo_name', e.target.value)}
                                    autoComplete="off"
                                />
                                <InputError message={form.errors.duo_name} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="duo_address">Adres</Label>
                                <Input
                                    id="duo_address"
                                    value={form.data.duo_address}
                                    onChange={(e) =>
                                        form.setData('duo_address', e.target.value)
                                    }
                                    autoComplete="off"
                                />
                                <InputError message={form.errors.duo_address} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="duo_city">Woonplaats</Label>
                                <Input
                                    id="duo_city"
                                    value={form.data.duo_city}
                                    onChange={(e) => form.setData('duo_city', e.target.value)}
                                    autoComplete="off"
                                />
                                <InputError message={form.errors.duo_city} />
                            </div>
                        </div>

                        <div className="flex gap-2">
                            <Button variant="outline" onClick={() => setStap('lesdagen')}>
                                Terug
                            </Button>
                            <Button disabled={!duoIngevuld} onClick={() => setStap('controle')}>
                                Verder naar controle
                            </Button>
                        </div>
                    </section>
                )}

                {/* Stap: controle en plaatsen */}
                {stap === 'controle' && pakket && locatie && (
                    <section className="space-y-4">
                        <header>
                            <h1 className="text-2xl font-semibold">Controle</h1>
                            <p className="text-sm text-muted-foreground">
                                De aanmelder betaalt het totaal, ook als het pakket
                                per persoon is geprijsd.
                            </p>
                        </header>

                        <dl className="divide-y rounded-lg border text-sm">
                            <Regel naam="Aanmelder">{auth.user.name}</Regel>
                            {/* NIEUW: de duo-lesser staat in de controle als het een duo-pakket is. */}
                            {isDuo && (
                                <Regel naam="Duo-lesser">
                                    {form.data.duo_name} · {form.data.duo_address},{' '}
                                    {form.data.duo_city}
                                </Regel>
                            )}
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
                                onClick={() => setStap(isDuo ? 'duo' : 'lesdagen')}
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