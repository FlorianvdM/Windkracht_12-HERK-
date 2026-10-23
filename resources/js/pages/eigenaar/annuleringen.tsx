import { Head, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { formatDatum } from '@/lib/format';

// Eén les zoals Eigenaar\CancellationController hem meestuurt.
type Les = {
    id: number;
    date: string;
    time: string;
    reason: string | null; // de reden die de klant heeft opgegeven
    number: string;
    customer: string;
    location: string;
    instructor: string;
};

// Een knop stuurt een POST-verzoek naar Laravel. preserveScroll: de pagina springt niet naar boven.
const verstuur = (adres: string, gegevens: Record<string, string> = {}) =>
    router.post(adres, gegevens, { preserveScroll: true });

// De eigenaar annuleert een les met een standaardknop (casus 3g). Zelfde adres als bij de instructeur.
function annuleer(les: Les, reden: 'ziekte' | 'weer') {
    const tekst =
        reden === 'ziekte' ? 'ziekte van de instructeur' : 'slecht weer (windkracht > 10)';

    if (confirm(`Les van ${les.customer} annuleren wegens ${tekst}?`)) {
        verstuur(`/instructeur/lessen/${les.id}/annuleren`, { reden });
    }
}

// Korte omschrijving van een les: datum, tijd, klant, locatie en instructeur.
function LesInfo({ les }: { les: Les }) {
    return (
        <div>
            <p className="font-medium">
                {formatDatum(les.date)} · {les.time} · {les.customer}
            </p>
            <p className="text-muted-foreground">
                {les.number} · {les.location} · {les.instructor}
            </p>
        </div>
    );
}

// Annuleringen: aanvragen van klanten beoordelen (casus 2e) en zelf lessen annuleren (casus 3g).
export default function Annuleringen({
    aanvragen,
    komend,
}: {
    aanvragen: Les[];
    komend: Les[];
}) {
    return (
        <>
            <Head title="Annuleringen" />

            <div className="mx-auto w-full max-w-4xl space-y-8 p-4">
                <section className="space-y-3">
                    <header>
                        <h1 className="text-2xl font-semibold">
                            Aanvragen van klanten
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Keur je de reden goed, dan kiest de klant zelf een
                            nieuwe lesdatum.
                        </p>
                    </header>

                    {aanvragen.length === 0 && (
                        <p className="rounded-lg border p-4 text-sm text-muted-foreground">
                            Er zijn geen aanvragen.
                        </p>
                    )}

                    <ul className="divide-y rounded-lg border text-sm">
                        {aanvragen.map((les) => (
                            <li key={les.id} className="space-y-2 p-3">
                                <LesInfo les={les} />
                                <p>
                                    <span className="text-muted-foreground">
                                        Reden:{' '}
                                    </span>
                                    {les.reason}
                                </p>
                                <div className="flex gap-2">
                                    <Button
                                        size="sm"
                                        onClick={() =>
                                            verstuur(`/eigenaar/annuleringen/${les.id}/goedkeuren`)
                                        }
                                    >
                                        Goedkeuren
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        onClick={() =>
                                            verstuur(`/eigenaar/annuleringen/${les.id}/afwijzen`)
                                        }
                                    >
                                        Afwijzen
                                    </Button>
                                </div>
                            </li>
                        ))}
                    </ul>
                </section>

                <section className="space-y-3">
                    <header>
                        <h2 className="text-xl font-semibold">Komende lessen</h2>
                        <p className="text-sm text-muted-foreground">
                            Annuleer een les bij ziekte of slecht weer. De klant
                            krijgt automatisch een standaardmail.
                        </p>
                    </header>

                    {komend.length === 0 && (
                        <p className="rounded-lg border p-4 text-sm text-muted-foreground">
                            Er zijn geen komende lessen.
                        </p>
                    )}

                    <ul className="divide-y rounded-lg border text-sm">
                        {komend.map((les) => (
                            <li
                                key={les.id}
                                className="flex flex-wrap items-center justify-between gap-2 p-3"
                            >
                                <LesInfo les={les} />
                                <div className="flex gap-2">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        onClick={() => annuleer(les, 'ziekte')}
                                    >
                                        Ziekte
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        onClick={() => annuleer(les, 'weer')}
                                    >
                                        Slecht weer
                                    </Button>
                                </div>
                            </li>
                        ))}
                    </ul>
                </section>
            </div>
        </>
    );
}

Annuleringen.layout = {
    breadcrumbs: [
        { title: 'Eigenaar', href: '/eigenaar' },
        { title: 'Annuleringen', href: '/eigenaar/annuleringen' },
    ],
};