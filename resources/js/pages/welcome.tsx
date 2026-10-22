import { Head, Link, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatEuro, formatUren } from '@/lib/format';
import { dashboard, login, register } from '@/routes';

// De gegevens van één pakket, zoals HomeController ze meestuurt.
type Pakket = {
    id: number;
    name: string;
    price_cents: number;
    hours: number;
    lesson_days: number;
    max_persons: number;
};

// De locaties uit de casus.
const LOCATIES = [
    'Zandvoort',
    'Muiderberg',
    'Wijk aan Zee',
    'IJmuiden',
    'Scheveningen',
    'Hoek van Holland',
];

// De stappen om te reserveren. Ze volgen het proces uit de casus.
const STAPPEN = [
    {
        titel: 'Maak een account',
        tekst: 'Registreer met je e-mailadres en kies een veilig wachtwoord.',
    },
    {
        titel: 'Kies je pakket',
        tekst: 'Kies een pakket, een locatie en je lesdagen.',
    },
    {
        titel: 'Ontvang de rekening',
        tekst: 'Je reservering is voorlopig tot je betaald hebt.',
    },
    {
        titel: 'Reservering definitief',
        tekst: 'Na ontvangst van je betaling maakt de eigenaar de reservering definitief.',
    },
];

// De foto's staan in public/images. Ontbreekt een foto, dan verbergen we hem (zie onError hieronder).
const FOTOS = [
    { src: '/images/foto-1.jpg', alt: 'Kitesurfles op het strand' },
    { src: '/images/foto-2.jpg', alt: 'Instructeur helpt een leerling' },
    { src: '/images/foto-3.jpg', alt: 'Kitesurfen bij zonsondergang' },
];

// Algemene homepage: voor iedereen toegankelijk (casus: Algemene homepage).
export default function Welcome({ packages }: { packages: Pakket[] }) {
    // De ingelogde gebruiker (of niemand) wordt door Laravel aan elke pagina meegegeven.
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Kitesurfschool Windkracht-12" />

            <div className="min-h-screen bg-background text-foreground">
                {/* Bovenbalk met de knoppen voor inloggen en registreren */}
                <header className="border-b">
                    <nav className="mx-auto flex w-full max-w-5xl items-center justify-between p-4">
                        <span className="text-lg font-semibold">
                            Windkracht-12
                        </span>
                        <div className="flex items-center gap-2">
                            {auth.user ? (
                                <Button asChild>
                                    <Link href={dashboard()}>
                                        Naar mijn omgeving
                                    </Link>
                                </Button>
                            ) : (
                                <>
                                    <Button asChild variant="ghost">
                                        <Link href={login()}>Inloggen</Link>
                                    </Button>
                                    <Button asChild>
                                        <Link href={register()}>
                                            Registreren
                                        </Link>
                                    </Button>
                                </>
                            )}
                        </div>
                    </nav>
                </header>

                <main className="mx-auto w-full max-w-5xl space-y-12 p-4 py-10">
                    {/* Kop met de belangrijkste knop */}
                    <section className="space-y-4">
                        <h1 className="text-4xl font-semibold">
                            Leer kitesurfen aan de Nederlandse kust
                        </h1>
                        <p className="max-w-2xl text-muted-foreground">
                            Kitesurfschool Windkracht-12 geeft al 8 jaar
                            kitesurflessen. Je leert in kleine groepen van
                            maximaal 2 personen per instructeur. Kite, board en
                            wetsuit zijn bij de prijs inbegrepen.
                        </p>
                        <Button asChild size="lg">
                            <Link href={auth.user ? dashboard() : register()}>
                                Reserveer een les
                            </Link>
                        </Button>
                    </section>

                    {/* Foto's */}
                    <section className="grid gap-4 md:grid-cols-3">
                        {FOTOS.map((foto) => (
                            <img
                                key={foto.src}
                                src={foto.src}
                                alt={foto.alt}
                                className="h-48 w-full rounded-lg object-cover"
                                // Bestaat de foto niet, dan tonen we geen kapot plaatje.
                                onError={(e) => {
                                    e.currentTarget.style.display = 'none';
                                }}
                            />
                        ))}
                    </section>

                    {/* Informatie over kitesurfen */}
                    <section className="space-y-3">
                        <h2 className="text-2xl font-semibold">
                            Wat is kitesurfen?
                        </h2>
                        <p className="max-w-3xl text-muted-foreground">
                            Kitesurfen is een watersport waarbij je op een
                            kleine surfplank staat en je laat voortrekken door
                            een kite, een soort vlieger. Bij sterke wind kun je
                            snelheden halen van wel 100 km/h. In Nederland is
                            kitesurfen op de binnenwateren niet toegestaan. Je
                            kunt het wel doen op de plekken (spots) langs de
                            kust.
                        </p>
                        <p className="max-w-3xl text-muted-foreground">
                            Onze instructeurs rijden vanuit Utrecht naar de
                            locatie van jouw les. Je kunt kiezen uit:{' '}
                            {LOCATIES.join(', ')}.
                        </p>
                    </section>

                    {/* De pakketten, rechtstreeks uit de database */}
                    <section className="space-y-4">
                        <h2 className="text-2xl font-semibold">Onze pakketten</h2>
                        <div className="grid gap-4 md:grid-cols-2">
                            {packages.map((pakket) => (
                                <Card key={pakket.id}>
                                    <CardHeader>
                                        <CardTitle>{pakket.name}</CardTitle>
                                        <CardDescription>
                                            {formatEuro(pakket.price_cents)} per
                                            persoon, inclusief alle materialen
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <ul className="space-y-1 text-sm text-muted-foreground">
                                            <li>
                                                {formatUren(pakket.hours)} uur
                                                les
                                            </li>
                                            <li>
                                                {pakket.lesson_days === 1
                                                    ? '1 dagdeel'
                                                    : `${pakket.lesson_days} dagdelen`}
                                            </li>
                                            <li>
                                                {pakket.max_persons === 1
                                                    ? 'Eén persoon per les'
                                                    : `Maximaal ${pakket.max_persons} personen per les`}
                                            </li>
                                        </ul>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </section>

                    {/* Hoe reserveer je? */}
                    <section className="space-y-4">
                        <h2 className="text-2xl font-semibold">
                            Zo reserveer je
                        </h2>
                        <ol className="grid gap-4 md:grid-cols-4">
                            {STAPPEN.map((stap, index) => (
                                <li
                                    key={stap.titel}
                                    className="space-y-1 rounded-lg border p-4 text-sm"
                                >
                                    <p className="text-muted-foreground">
                                        Stap {index + 1}
                                    </p>
                                    <p className="font-medium">{stap.titel}</p>
                                    <p className="text-muted-foreground">
                                        {stap.tekst}
                                    </p>
                                </li>
                            ))}
                        </ol>
                        <p className="text-sm text-muted-foreground">
                            Alle reserveringen lopen via deze website. Telefonisch
                            reserveren is niet meer mogelijk.
                        </p>
                    </section>
                </main>

                <footer className="border-t p-4 text-center text-sm text-muted-foreground">
                    Kitesurfschool Windkracht-12 · Utrecht
                </footer>
            </div>
        </>
    );
}