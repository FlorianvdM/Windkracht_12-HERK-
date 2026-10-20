import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const WEEKDAGEN = ['ma', 'di', 'wo', 'do', 'vr', 'za', 'zo'];

// Een getal met voorloopnul: 5 wordt '05'.
const tweeCijfers = (getal: number) => String(getal).padStart(2, '0');

// Datum als tekst zoals de database hem gebruikt: '2026-10-22'.
// We bouwen de tekst zelf op, zodat tijdzones nooit een dag kunnen opschuiven.
const datumTekst = (jaar: number, maand: number, dag: number) =>
    `${jaar}-${tweeCijfers(maand + 1)}-${tweeCijfers(dag)}`;

// Maandkalender waarin de klant lesdagen aanklikt (wireframe 10).
// - Dagen tot en met vandaag kunnen niet worden gekozen.
// - Dagen waarop de instructeur al een les heeft staan op "Bezet".
// - Je kunt niet meer dagen kiezen dan het pakket heeft.
export default function LesdagenKalender({
    bezet,
    gekozen,
    maxAantal,
    onWijzig,
}: {
    bezet: string[];
    gekozen: string[];
    maxAantal: number;
    onWijzig: (gekozen: string[]) => void;
}) {
    const nu = new Date();
    const vandaag = datumTekst(nu.getFullYear(), nu.getMonth(), nu.getDate());

    // De maand die op het scherm staat. We beginnen bij de huidige maand.
    const [zichtbaar, setZichtbaar] = useState({
        jaar: nu.getFullYear(),
        maand: nu.getMonth(),
    });

    const maandNaam = new Intl.DateTimeFormat('nl-NL', {
        month: 'long',
        year: 'numeric',
    }).format(new Date(zichtbaar.jaar, zichtbaar.maand, 1));

    // Hoeveel lege vakjes vóór de 1e? JavaScript telt zondag als 0, wij beginnen op maandag.
    const eersteWeekdag = new Date(zichtbaar.jaar, zichtbaar.maand, 1).getDay();
    const legeVakjes = (eersteWeekdag + 6) % 7;
    const dagenInMaand = new Date(
        zichtbaar.jaar,
        zichtbaar.maand + 1,
        0,
    ).getDate();

    // De klant mag niet naar een maand in het verleden bladeren.
    const isHuidigeMaand =
        zichtbaar.jaar === nu.getFullYear() && zichtbaar.maand === nu.getMonth();

    function bladeren(stappen: number) {
        const nieuw = new Date(zichtbaar.jaar, zichtbaar.maand + stappen, 1);
        setZichtbaar({ jaar: nieuw.getFullYear(), maand: nieuw.getMonth() });
    }

    // Klik op een dag: aan- of uitzetten (en altijd op volgorde van datum bewaren).
    function wissel(datum: string) {
        if (gekozen.includes(datum)) {
            onWijzig(gekozen.filter((dag) => dag !== datum));
        } else if (gekozen.length < maxAantal) {
            onWijzig([...gekozen, datum].sort());
        }
    }

    const vakjes = [];
    for (let dag = 1; dag <= dagenInMaand; dag++) {
        const datum = datumTekst(zichtbaar.jaar, zichtbaar.maand, dag);
        const isVerleden = datum <= vandaag;
        const isBezet = bezet.includes(datum);
        const isGekozen = gekozen.includes(datum);
        // Vol: je hebt al genoeg dagen gekozen, dus andere dagen kun je niet meer aanklikken.
        const isVol = !isGekozen && gekozen.length >= maxAantal;
        const uitgeschakeld = isVerleden || isBezet || isVol;

        vakjes.push(
            <button
                key={datum}
                type="button"
                disabled={uitgeschakeld}
                aria-pressed={isGekozen}
                onClick={() => wissel(datum)}
                className={cn(
                    'flex h-16 flex-col items-start justify-start gap-0.5 border-t border-l p-2 text-left text-sm transition-colors',
                    isGekozen
                        ? 'bg-primary text-primary-foreground'
                        : 'hover:bg-accent',
                    (isVerleden || isBezet) && 'bg-muted text-muted-foreground',
                    uitgeschakeld && 'cursor-not-allowed',
                    isVol && !isBezet && !isVerleden && 'opacity-60',
                )}
            >
                <span>{dag}</span>
                {isGekozen && <span className="text-xs">Gekozen</span>}
                {isBezet && !isGekozen && <span className="text-xs">Bezet</span>}
            </button>,
        );
    }

    return (
        <div className="space-y-3">
            <div className="flex items-center justify-between">
                <h3 className="font-medium capitalize">{maandNaam}</h3>
                <div className="flex gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        disabled={isHuidigeMaand}
                        onClick={() => bladeren(-1)}
                        aria-label="Vorige maand"
                    >
                        <ChevronLeft />
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        onClick={() => bladeren(1)}
                        aria-label="Volgende maand"
                    >
                        <ChevronRight />
                    </Button>
                </div>
            </div>

            <div className="overflow-hidden rounded-lg border-r border-b">
                <div className="grid grid-cols-7 bg-muted text-center text-xs text-muted-foreground">
                    {WEEKDAGEN.map((weekdag) => (
                        <div key={weekdag} className="p-2">
                            {weekdag}
                        </div>
                    ))}
                </div>
                <div className="grid grid-cols-7">
                    {Array.from({ length: legeVakjes }, (_, nummer) => (
                        <div
                            key={`leeg-${nummer}`}
                            className="h-16 border-t border-l bg-muted/40"
                        />
                    ))}
                    {vakjes}
                </div>
            </div>
        </div>
    );
}
