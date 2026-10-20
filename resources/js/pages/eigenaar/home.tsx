import RolHome from '@/components/rol-home';

// Homepage voor de eigenaar. Laravel toont deze pagina via de route 'eigenaar.home'.
export default function EigenaarHome() {
    return (
        <RolHome
            titel="Eigenaar"
            uitleg="Hier beheer je de reserveringen van de kitesurfschool."
            onderdelen={[
                {
                    titel: 'Reserveringen',
                    uitleg: 'Maak een reservering definitief als de betaling binnen is.',
                },
                {
                    titel: 'Openstaande betalingen',
                    uitleg: 'Zie welke klanten nog niet hebben betaald.',
                },
            ]}
        />
    );
}

EigenaarHome.layout = {
    breadcrumbs: [{ title: 'Eigenaar', href: '/eigenaar' }],
};
