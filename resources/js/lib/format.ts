// Bedrag in eurocenten omzetten naar tekst: 17500 wordt '€ 175,00'.
export function formatEuro(cents: number): string {
    return new Intl.NumberFormat('nl-NL', {
        style: 'currency',
        currency: 'EUR',
    }).format(cents / 100);
}

// Uren netjes met een komma: 10.5 wordt '10,5'.
export function formatUren(uren: number): string {
    return uren.toLocaleString('nl-NL');
}

// Datum van de database omzetten naar leesbare tekst: '2026-10-22' wordt 'donderdag 22 oktober 2026'.
export function formatDatum(datum: string): string {
    const [jaar, maand, dag] = datum.split('-').map(Number);

    // new Date(jaar, maand - 1, dag) gebruikt de lokale tijdzone, zodat de dag nooit een dag opschuift.
    return new Intl.DateTimeFormat('nl-NL', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(jaar, maand - 1, dag));
}
