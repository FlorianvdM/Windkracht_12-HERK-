import { Head, Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatDatum } from '@/lib/format';

type Weergave = 'dag' | 'week' | 'maand';

// Eén les, zoals LessonController (instructeur) hem meestuurt.
type Les = {
    id: number;
    date: string; // '2026-10-22'
    time: string; // '10:00'
    status: string; // 'gepland' of 'geannuleerd'
    number: string; // reserveringsnummer, bijvoorbeeld 'WK12-1007'
    reservation_status: 'voorlopig' | 'definitief';
    customer: string;
    location: string;
    package: string;
};

type Props = {
    weergave: Weergave;
    datum: string; // de dag waar we omheen kijken
    start: string; // eerste dag van de periode
    eind: string; // laatste dag van de periode
    vorige: string; // datum voor de knop Vorige
    volgende: string; // datum voor de knop Volgende
    vandaag: string;
    lessons: Les[];
};

const WEERGAVEN: { waarde: Weergave; label: string }[] = [
    { waarde: 'dag', label: 'Dag' },
    { waarde: 'week', label: 'Week' },
    { waarde: 'maand', label: 'Maand' },
];

// Het adres van deze pagina met een andere weergave of datum.
const adres = (weergave: Weergave, datum: string) =>
    `/instructeur/lessen?weergave=${weergave}&datum=${datum}`;

// De titel boven de lijst: een dag, een week (van t/m) of een maand.
function periodeTitel(weergave: Weergave, start: string, eind: string) {
    if (weergave === 'dag') {
        return formatDatum(start);
    }

    if (weergave === 'week') {
        return `${formatDatum(start)} t/m ${formatDatum(eind)}`;
    }

    const [jaar, maand] = start.split('-').map(Number);

    return new Intl.DateTimeFormat('nl-NL', {
        month: 'long',
        year: 'numeric',
    }).format(new Date(jaar, maand - 1, 1));
}

// Dag-, week- en maandoverzicht van de eigen lessen van de instructeur (casus 1f).
export default function Lessen({
    weergave,
    datum,
    start,
    eind,
    vorige,
    volgende,
    vandaag,
    lessons,
}: Props) {
    // De lessen groeperen per dag, zodat we per dag een kopje kunnen tonen.
    const perDag = lessons.reduce<Record<string, Les[]>>((groepen, les) => {
        (groepen[les.date] ??= []).push(les);

        return groepen;
    }, {});
    const dagen = Object.keys(perDag).sort();

    return (
        <>
            <Head title="Mijn lessen" />

            <div className="mx-auto w-full max-w-4xl space-y-6 p-4">
                <header className="space-y-1">
                    <h1 className="text-2xl font-semibold">Mijn lessen</h1>
                    <p className="text-sm text-muted-foreground">
                        {periodeTitel(weergave, start, eind)} · {lessons.length}{' '}
                        {lessons.length === 1 ? 'les' : 'lessen'}
                    </p>
                </header>

                <div className="flex flex-wrap items-center justify-between gap-2">
                    {/* Wisselen tussen dag, week en maand. De gekozen datum blijft gelijk. */}
                    <div className="flex gap-2">
                        {WEERGAVEN.map((optie) => (
                            <Button
                                key={optie.waarde}
                                asChild
                                size="sm"
                                variant={
                                    optie.waarde === weergave
                                        ? 'default'
                                        : 'outline'
                                }
                            >
                                <Link href={adres(optie.waarde, datum)}>
                                    {optie.label}
                                </Link>
                            </Button>
                        ))}
                    </div>

                    {/* Een periode terug, naar vandaag, of een periode vooruit. */}
                    <div className="flex gap-2">
                        <Button asChild size="sm" variant="outline">
                            <Link href={adres(weergave, vorige)}>Vorige</Link>
                        </Button>
                        <Button asChild size="sm" variant="outline">
                            <Link href={adres(weergave, vandaag)}>Vandaag</Link>
                        </Button>
                        <Button asChild size="sm" variant="outline">
                            <Link href={adres(weergave, volgende)}>
                                Volgende
                            </Link>
                        </Button>
                    </div>
                </div>

                {lessons.length === 0 && (
                    <div className="rounded-lg border p-6 text-sm text-muted-foreground">
                        Je hebt geen lessen in deze periode.
                    </div>
                )}

                {dagen.map((dag) => (
                    <section key={dag} className="space-y-2">
                        <h2 className="font-medium">{formatDatum(dag)}</h2>
                        <ul className="divide-y rounded-lg border text-sm">
                            {perDag[dag].map((les) => (
                                <li
                                    key={les.id}
                                    className="flex flex-wrap items-center justify-between gap-2 p-3"
                                >
                                    <div>
                                        <p className="font-medium">
                                            {les.time} · {les.customer}
                                        </p>
                                        <p className="text-muted-foreground">
                                            {les.location} · {les.package} ·{' '}
                                            {les.number}
                                        </p>
                                    </div>
                                    <div className="flex gap-2">
                                        {les.status === 'geannuleerd' && (
                                            <Badge variant="outline">
                                                Geannuleerd
                                            </Badge>
                                        )}
                                        <Badge
                                            variant={
                                                les.reservation_status ===
                                                'definitief'
                                                    ? 'default'
                                                    : 'secondary'
                                            }
                                        >
                                            {les.reservation_status ===
                                            'definitief'
                                                ? 'Definitief'
                                                : 'Voorlopig'}
                                        </Badge>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </section>
                ))}
            </div>
        </>
    );
}

Lessen.layout = {
    breadcrumbs: [
        { title: 'Instructeur', href: '/instructeur' },
        { title: 'Mijn lessen', href: '/instructeur/lessen' },
    ],
};