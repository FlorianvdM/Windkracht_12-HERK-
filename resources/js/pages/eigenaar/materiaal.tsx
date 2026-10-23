import { Head, router, useForm, usePage } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

// Eén soort materiaal, zoals EquipmentController het meestuurt.
type Item = {
    id: number;
    name: string;
    type: string; // kite, board, wetsuit of overig
    size: string | null;
    quantity: number; // totaal aantal stuks
    defect: number; // hoeveel daarvan kapot zijn
};

// Aantallen aanpassen: we sturen het nieuwe aantal en het aantal kapot naar Laravel.
function pasAan(item: Item, quantity: number, defect: number) {
    router.patch(
        `/eigenaar/materiaal/${item.id}`,
        { quantity, defect },
        { preserveScroll: true },
    );
}

// Verwijderen, maar eerst vragen of het zeker is.
function verwijder(item: Item) {
    if (confirm(`${item.name} verwijderen?`)) {
        router.delete(`/eigenaar/materiaal/${item.id}`, { preserveScroll: true });
    }
}

// Materiaalbeheer: overzicht, toevoegen, aantallen aanpassen en verwijderen.
export default function Materiaal({ types, items }: { types: string[]; items: Item[] }) {
    // Foutmeldingen van het aanpassen (bijvoorbeeld "meer kapot dan totaal") komen via de pagina binnen.
    const { errors } = usePage().props;

    const form = useForm({
        name: '',
        type: types[0] ?? 'kite',
        size: '',
        quantity: 1,
    });

    function voegToe() {
        form.post('/eigenaar/materiaal', {
            preserveScroll: true,
            // Na het opslaan het formulier leegmaken, maar de gekozen soort laten staan.
            onSuccess: () => form.reset('name', 'size', 'quantity'),
        });
    }

    // Totaal aantal bruikbare stuks: alles wat niet kapot is.
    const bruikbaar = items.reduce((som, item) => som + item.quantity - item.defect, 0);

    return (
        <>
            <Head title="Materiaal" />

            <div className="mx-auto w-full max-w-4xl space-y-6 p-4">
                <header>
                    <h1 className="text-2xl font-semibold">Materiaal</h1>
                    <p className="text-sm text-muted-foreground">
                        Kites, boards en wetsuits van de school. Nu {bruikbaar}{' '}
                        stuks bruikbaar.
                    </p>
                </header>

                <InputError message={(errors as Record<string, string>).defect} />

                {/* Tabel met al het materiaal */}
                <div className="overflow-x-auto rounded-lg border">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50 text-left">
                            <tr>
                                <th className="p-3 font-medium">Naam</th>
                                <th className="p-3 font-medium">Soort</th>
                                <th className="p-3 font-medium">Aantal</th>
                                <th className="p-3 font-medium">Kapot</th>
                                <th className="p-3 font-medium">Bruikbaar</th>
                                <th className="p-3" />
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {items.map((item) => (
                                <tr key={item.id}>
                                    <td className="p-3">
                                        {item.name}
                                        {item.size && (
                                            <span className="text-muted-foreground">
                                                {' '}
                                                ({item.size})
                                            </span>
                                        )}
                                    </td>
                                    <td className="p-3 capitalize">{item.type}</td>
                                    {/* Met − en + pas je het aantal met één aan. */}
                                    <td className="p-3">
                                        <div className="flex items-center gap-1">
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                disabled={item.quantity <= item.defect}
                                                onClick={() => pasAan(item, item.quantity - 1, item.defect)}
                                            >
                                                −
                                            </Button>
                                            <span className="w-8 text-center">{item.quantity}</span>
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                onClick={() => pasAan(item, item.quantity + 1, item.defect)}
                                            >
                                                +
                                            </Button>
                                        </div>
                                    </td>
                                    <td className="p-3">
                                        <div className="flex items-center gap-1">
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                disabled={item.defect === 0}
                                                onClick={() => pasAan(item, item.quantity, item.defect - 1)}
                                            >
                                                −
                                            </Button>
                                            <span className="w-8 text-center">{item.defect}</span>
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                disabled={item.defect >= item.quantity}
                                                onClick={() => pasAan(item, item.quantity, item.defect + 1)}
                                            >
                                                +
                                            </Button>
                                        </div>
                                    </td>
                                    <td className="p-3 font-medium">
                                        {item.quantity - item.defect}
                                    </td>
                                    <td className="p-3 text-right">
                                        <Button
                                            size="sm"
                                            variant="destructive"
                                            onClick={() => verwijder(item)}
                                        >
                                            Verwijderen
                                        </Button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Formulier om nieuw materiaal toe te voegen */}
                <section className="space-y-4 rounded-lg border p-4">
                    <h2 className="text-lg font-medium">Materiaal toevoegen</h2>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="name">Naam</Label>
                            <Input
                                id="name"
                                placeholder="Bijvoorbeeld Kite 10 m²"
                                value={form.data.name}
                                onChange={(e) => form.setData('name', e.target.value)}
                            />
                            <InputError message={form.errors.name} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="type">Soort</Label>
                            {/* Een gewone select is genoeg; de keuzes komen uit Equipment::TYPES. */}
                            <select
                                id="type"
                                className="h-9 rounded-md border bg-background px-3 text-sm capitalize"
                                value={form.data.type}
                                onChange={(e) => form.setData('type', e.target.value)}
                            >
                                {types.map((type) => (
                                    <option key={type} value={type}>
                                        {type}
                                    </option>
                                ))}
                            </select>
                            <InputError message={form.errors.type} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="size">Maat (mag leeg)</Label>
                            <Input
                                id="size"
                                placeholder="Bijvoorbeeld M of 10 m²"
                                value={form.data.size}
                                onChange={(e) => form.setData('size', e.target.value)}
                            />
                            <InputError message={form.errors.size} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="quantity">Aantal</Label>
                            <Input
                                id="quantity"
                                type="number"
                                min={1}
                                value={form.data.quantity}
                                onChange={(e) => form.setData('quantity', Number(e.target.value))}
                            />
                            <InputError message={form.errors.quantity} />
                        </div>
                    </div>

                    <Button disabled={form.processing} onClick={voegToe}>
                        Toevoegen
                    </Button>
                </section>
            </div>
        </>
    );
}

Materiaal.layout = {
    breadcrumbs: [
        { title: 'Eigenaar', href: '/eigenaar' },
        { title: 'Materiaal', href: '/eigenaar/materiaal' },
    ],
};