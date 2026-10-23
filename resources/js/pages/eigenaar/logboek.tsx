import { Head } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

// Eén regel uit storage/logs/inlog.log, al in vier kolommen geknipt door LogController.
type Regel = {
    datum: string;
    tijd: string; // microseconde nauwkeurig, bijvoorbeeld '14:03:22.123456'
    actie: string; // 'INGELOGD' of 'UITGELOGD'
    email: string;
};

// Logboek met alle in- en uitlogmomenten (casus: in/uitlogproces stap 3 en 6).
export default function Logboek({ regels }: { regels: Regel[] }) {
    return (
        <>
            <Head title="Logboek" />

            <div className="mx-auto w-full max-w-4xl space-y-6 p-4">
                <header>
                    <h1 className="text-2xl font-semibold">Logboek</h1>
                    <p className="text-sm text-muted-foreground">
                        Wie wanneer is in- en uitgelogd. De nieuwste regels
                        staan bovenaan (maximaal 200). Het bestand staat op de
                        server in storage/logs/inlog.log.
                    </p>
                </header>

                {regels.length === 0 ? (
                    <p className="rounded-lg border p-4 text-sm text-muted-foreground">
                        Er is nog niemand in- of uitgelogd.
                    </p>
                ) : (
                    // overflow-x-auto: op een smal scherm kun je de tabel opzij schuiven.
                    <div className="overflow-x-auto rounded-lg border">
                        <table className="w-full text-sm">
                            <thead className="bg-muted/50 text-left">
                                <tr>
                                    <th className="p-3 font-medium">Datum</th>
                                    <th className="p-3 font-medium">Tijd</th>
                                    <th className="p-3 font-medium">Actie</th>
                                    <th className="p-3 font-medium">E-mailadres</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {regels.map((regel, index) => (
                                    <tr key={index}>
                                        <td className="p-3">{regel.datum}</td>
                                        <td className="p-3 font-mono">{regel.tijd}</td>
                                        <td className="p-3">
                                            <Badge
                                                variant={
                                                    regel.actie === 'INGELOGD'
                                                        ? 'default'
                                                        : 'secondary'
                                                }
                                            >
                                                {regel.actie === 'INGELOGD'
                                                    ? 'Ingelogd'
                                                    : 'Uitgelogd'}
                                            </Badge>
                                        </td>
                                        <td className="p-3">{regel.email}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </>
    );
}

Logboek.layout = {
    breadcrumbs: [
        { title: 'Eigenaar', href: '/eigenaar' },
        { title: 'Logboek', href: '/eigenaar/logboek' },
    ],
};