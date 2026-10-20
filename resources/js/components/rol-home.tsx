import { Head, Link, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

// Eén functie op de homepage. Heeft het onderdeel een 'href', dan is het een link.
// Zonder 'href' is de pagina er nog niet en tonen we "Binnenkort".
type Onderdeel = {
    titel: string;
    uitleg: string;
    href?: string;
};

// Gedeelde opmaak voor de homepage van klant, instructeur en eigenaar.
// Zo hoef ik de opmaak maar één keer te maken.
export default function RolHome({
    titel,
    uitleg,
    onderdelen,
}: {
    titel: string;
    uitleg: string;
    onderdelen: Onderdeel[];
}) {
    // De ingelogde gebruiker wordt door Laravel aan elke pagina meegegeven (HandleInertiaRequests).
    const { auth } = usePage().props;

    return (
        <>
            <Head title={titel} />

            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title={`Welkom, ${auth.user.name}`}
                    description={uitleg}
                />

                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {onderdelen.map((onderdeel) => {
                        const kaart = (
                            <Card className="h-full">
                                <CardHeader>
                                    <CardTitle className="flex items-center justify-between gap-2">
                                        {onderdeel.titel}
                                        {!onderdeel.href && (
                                            <Badge variant="secondary">
                                                Binnenkort
                                            </Badge>
                                        )}
                                    </CardTitle>
                                    <CardDescription>
                                        {onderdeel.uitleg}
                                    </CardDescription>
                                </CardHeader>
                            </Card>
                        );

                        return onderdeel.href ? (
                            <Link key={onderdeel.titel} href={onderdeel.href}>
                                {kaart}
                            </Link>
                        ) : (
                            <div key={onderdeel.titel}>{kaart}</div>
                        );
                    })}
                </div>
            </div>
        </>
    );
}
