import RolHome from '@/components/rol-home';

// Homepage voor de eigenaar. Laravel toont deze pagina via de route 'eigenaar.home'.
// Een onderdeel met een 'href' wordt een klikbare kaart en krijgt geen label "Binnenkort" meer.
export default function EigenaarHome() {
    return (
        <RolHome
            titel="Eigenaar"
            uitleg="Hier beheer je de reserveringen van de kitesurfschool."
            onderdelen={[
                {
                    titel: 'Reserveringen',
                    uitleg: 'Maak een reservering definitief als de betaling binnen is.',
                    // Het overzicht met alle reserveringen
                    href: '/eigenaar/reserveringen',
                },
                {
                    titel: 'Openstaande betalingen',
                    uitleg: 'Zie welke klanten nog niet hebben betaald.',
                    // Hetzelfde overzicht, maar met het filter voor voorlopige reserveringen
                    href: '/eigenaar/reserveringen?filter=onbetaald',
                },
            ]}
        />
    );
}

EigenaarHome.layout = {
    breadcrumbs: [{ title: 'Eigenaar', href: '/eigenaar' }],
};