import RolHome from '@/components/rol-home';

// Homepage voor klanten. Laravel toont deze pagina via de route 'klant.home' (routes/web.php).
export default function KlantHome() {
    return (
        <RolHome
            titel="Klant"
            uitleg="Hier regel je jouw kitesurflessen."
            onderdelen={[
                {
                    titel: 'Reserveren',
                    uitleg: 'Kies een pakket, een locatie en je lesdagen.',
                    href: '/klant/reserveren',
                },
                {
                    titel: 'Mijn reserveringen',
                    uitleg: 'Bekijk je reserveringen en of ze voorlopig of definitief zijn.',
                    href: '/klant/reserveringen',
                },
            ]}
        />
    );
}

KlantHome.layout = {
    breadcrumbs: [{ title: 'Klant', href: '/klant' }],
};
